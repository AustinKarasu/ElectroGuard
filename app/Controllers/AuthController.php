<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\AuditLogger;
use PDO;

class AuthController {
    public static function login(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $identifier = trim($input['identifier'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Identifier (email or username) and password are required.']);
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR username = :id");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            AuditLogger::log(null, $identifier, 'guest', 'LOGIN_FAILED', 'USER', $identifier, 'FAILED', 'Invalid credentials');
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
            return;
        }

        AuthMiddleware::setCurrentUser($user);
        AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'LOGIN', 'USER', (string)$user['id'], 'SUCCESS', 'Authenticated successfully');

        echo json_encode([
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'phone_masked' => $user['phone_masked']
            ]
        ]);
    }

    public static function demoLogin(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $role = $input['role'] ?? 'worker';
        $workerIndex = (int)($input['worker_index'] ?? 1);

        $pdo = Database::getConnection();
        if ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
        } elseif ($role === 'supervisor') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'supervisor'");
        } else {
            $username = 'worker' . max(1, min(3, $workerIndex));
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
            $stmt->bindValue(':u', $username);
        }

        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Demo account not found in database.']);
            return;
        }

        AuthMiddleware::setCurrentUser($user);
        AuditLogger::log($user['id'], $user['full_name'], $user['role'], 'DEMO_LOGIN', 'USER', (string)$user['id'], 'SUCCESS', "Logged in via demonstration selector as {$user['role']}");

        // Fetch worker profile if worker
        $workerProfile = null;
        if ($user['role'] === 'worker') {
            $wStmt = $pdo->prepare("SELECT * FROM worker_profiles WHERE user_id = :uid");
            $wStmt->execute([':uid' => $user['id']]);
            $workerProfile = $wStmt->fetch();
        }

        echo json_encode([
            'success' => true,
            'message' => "Demonstration session started for {$user['full_name']}.",
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
                'phone_masked' => $user['phone_masked'],
                'worker_profile' => $workerProfile
            ]
        ]);
    }

    public static function me(): void {
        $currentUser = AuthMiddleware::getCurrentUser();
        if (!$currentUser) {
            http_response_code(200);
            echo json_encode(['authenticated' => false, 'user' => null]);
            return;
        }

        $pdo = Database::getConnection();
        $workerProfile = null;
        if ($currentUser['role'] === 'worker') {
            $stmt = $pdo->prepare("SELECT * FROM worker_profiles WHERE user_id = :uid");
            $stmt->execute([':uid' => $currentUser['id']]);
            $workerProfile = $stmt->fetch();
        }

        echo json_encode([
            'authenticated' => true,
            'user' => array_merge($currentUser, ['worker_profile' => $workerProfile])
        ]);
    }

    public static function logout(): void {
        $currentUser = AuthMiddleware::getCurrentUser();
        if ($currentUser) {
            AuditLogger::log($currentUser['id'], $currentUser['full_name'], $currentUser['role'], 'LOGOUT', 'USER', (string)$currentUser['id'], 'SUCCESS', 'User logged out');
        }
        AuthMiddleware::logout();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    }
}
