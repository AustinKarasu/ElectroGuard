<?php
namespace ElectroGuard\Middleware;

class AuthMiddleware {
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getCurrentUser(): ?array {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function setCurrentUser(array $user): void {
        self::startSession();
        // Regenerate session id to protect against fixation
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'phone_masked' => $user['phone_masked'] ?? null
        ];
    }

    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireAuth(): ?array {
        $user = self::getCurrentUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication required to access this resource.'
                ]
            ]);
            exit;
        }
        return $user;
    }

    public static function requireRole(array|string $roles): array {
        $user = self::requireAuth();
        $roles = (array)$roles;
        if (!in_array($user['role'], $roles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have permission to perform this action.'
                ]
            ]);
            exit;
        }
        return $user;
    }
}
