<?php
namespace ElectroGuard\Services;

use ElectroGuard\Config\Database;
use PDO;

class AlertService {
    public static function create(string $severity, string $title, string $message, ?int $relatedJobId = null): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO alerts (severity, title, message, related_job_id, is_read, created_at)
                VALUES (:severity, :title, :message, :related_job_id, 0, datetime('now'))
            ");
            $stmt->execute([
                ':severity' => strtoupper($severity),
                ':title' => $title,
                ':message' => $message,
                ':related_job_id' => $relatedJobId,
            ]);
        } catch (\Exception $e) {
            error_log("Alert creation failed: " . $e->getMessage());
        }
    }

    public static function getUnreadCount(): int {
        try {
            $pdo = Database::getConnection();
            return (int)$pdo->query("SELECT COUNT(*) FROM alerts WHERE is_read = 0")->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function listAlerts(int $limit = 50, ?string $severity = null): array {
        try {
            $pdo = Database::getConnection();
            if ($severity) {
                $stmt = $pdo->prepare("SELECT * FROM alerts WHERE severity = :sev ORDER BY created_at DESC LIMIT :lim");
                $stmt->bindValue(':sev', strtoupper($severity), PDO::PARAM_STR);
                $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            }
            $stmt = $pdo->prepare("SELECT * FROM alerts ORDER BY created_at DESC LIMIT :lim");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function markAsRead(int $alertId): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE alerts SET is_read = 1 WHERE id = :id");
            return $stmt->execute([':id' => $alertId]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
