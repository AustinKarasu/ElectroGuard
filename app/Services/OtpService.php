<?php
namespace ElectroGuard\Services;

use ElectroGuard\Config\Database;
use PDO;

class OtpService {
    public static function createChallenge(int $jobId, int $userId, string $purpose = 'CLEARANCE'): array {
        $pdo = Database::getConnection();

        // Invalidate any active, unverified challenges for this job and user
        $stmt = $pdo->prepare("DELETE FROM otp_challenges WHERE job_id = :jid AND user_id = :uid AND is_verified = 0");
        $stmt->execute([':jid' => $jobId, ':uid' => $userId]);

        $code = sprintf("%06d", random_int(100000, 999999));
        $hash = password_hash($code, PASSWORD_BCRYPT);
        // 5 minutes expiry
        $expiresAt = date('Y-m-d H:i:s', time() + 300);

        $stmt = $pdo->prepare("
            INSERT INTO otp_challenges (job_id, user_id, otp_hash, otp_hint, purpose, attempt_count, max_attempts, is_verified, expires_at, created_at)
            VALUES (:jid, :uid, :hash, :hint, :purpose, 0, 3, 0, :exp, datetime('now'))
        ");
        $stmt->execute([
            ':jid' => $jobId,
            ':uid' => $userId,
            ':hash' => $hash,
            ':hint' => $code, // Prototype mode simulated SMS hint
            ':purpose' => $purpose,
            ':exp' => $expiresAt,
        ]);

        $challengeId = (int)$pdo->lastInsertId();

        AuditLogger::log(
            $userId,
            "Worker #{$userId}",
            'worker',
            'OTP_REQUESTED',
            'OTP_CHALLENGE',
            (string)$challengeId,
            'SUCCESS',
            "Generated {$purpose} challenge for Job #{$jobId}"
        );

        return [
            'challenge_id' => $challengeId,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
            'demo_code' => $code, // Provided for instant demo verification
            'message' => "Simulated verification code issued for prototype demonstration."
        ];
    }

    public static function verifyChallenge(int $jobId, int $userId, string $enteredCode): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT * FROM otp_challenges
            WHERE job_id = :jid AND user_id = :uid AND is_verified = 0
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':jid' => $jobId, ':uid' => $userId]);
        $challenge = $stmt->fetch();

        if (!$challenge) {
            AuditLogger::log($userId, "Worker #{$userId}", 'worker', 'OTP_VERIFY_FAILED', 'OTP_CHALLENGE', 'NONE', 'FAILED', 'No active OTP challenge found or already used');
            return ['success' => false, 'error' => 'No active verification challenge found. Please request a new code.'];
        }

        // Check expiry
        if (strtotime($challenge['expires_at']) < time()) {
            AuditLogger::log($userId, "Worker #{$userId}", 'worker', 'OTP_EXPIRED', 'OTP_CHALLENGE', (string)$challenge['id'], 'FAILED', 'OTP challenge expired');
            return ['success' => false, 'error' => 'Verification code has expired. Please request a new code.'];
        }

        // Check attempt count
        if ($challenge['attempt_count'] >= $challenge['max_attempts']) {
            AuditLogger::log($userId, "Worker #{$userId}", 'worker', 'OTP_MAX_ATTEMPTS', 'OTP_CHALLENGE', (string)$challenge['id'], 'FAILED', 'Maximum verification attempts exceeded');
            return ['success' => false, 'error' => 'Maximum verification attempts exceeded. Code invalidated.'];
        }

        // Verify code
        if (!password_verify($enteredCode, $challenge['otp_hash'])) {
            $newAttempts = $challenge['attempt_count'] + 1;
            $stmt = $pdo->prepare("UPDATE otp_challenges SET attempt_count = :cnt WHERE id = :id");
            $stmt->execute([':cnt' => $newAttempts, ':id' => $challenge['id']]);

            $remaining = $challenge['max_attempts'] - $newAttempts;
            AuditLogger::log($userId, "Worker #{$userId}", 'worker', 'OTP_MISMATCH', 'OTP_CHALLENGE', (string)$challenge['id'], 'FAILED', "Invalid code entered. Attempts remaining: {$remaining}");

            return [
                'success' => false,
                'error' => "Invalid code. {$remaining} attempt(s) remaining.",
                'remaining_attempts' => $remaining
            ];
        }

        // Mark verified
        $stmt = $pdo->prepare("UPDATE otp_challenges SET is_verified = 1 WHERE id = :id");
        $stmt->execute([':id' => $challenge['id']]);

        AuditLogger::log($userId, "Worker #{$userId}", 'worker', 'OTP_VERIFIED', 'OTP_CHALLENGE', (string)$challenge['id'], 'SUCCESS', 'OTP verified successfully');

        return [
            'success' => true,
            'message' => 'Identity verified via simulated two-factor channel.'
        ];
    }
}
