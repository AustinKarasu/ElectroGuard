<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\AlertService;
use PDO;

class AlertController {
    public static function index(): void {
        AuthMiddleware::requireAuth();
        $severity = $_GET['severity'] ?? null;
        $alerts = AlertService::listAlerts(100, $severity);
        $unread = AlertService::getUnreadCount();

        echo json_encode([
            'success' => true,
            'data' => $alerts,
            'unread_count' => $unread
        ]);
    }

    public static function markRead(int $id): void {
        AuthMiddleware::requireAuth();
        $success = AlertService::markAsRead($id);
        echo json_encode(['success' => $success]);
    }

    public static function markAllRead(): void {
        AuthMiddleware::requireAuth();
        $pdo = Database::getConnection();
        $pdo->exec("UPDATE alerts SET is_read = 1 WHERE is_read = 0");
        echo json_encode(['success' => true]);
    }
}
