<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use PDO;

class AuditController {
    public static function index(): void {
        AuthMiddleware::requireRole(['admin', 'supervisor']);
        $pdo = Database::getConnection();

        $search = trim($_GET['search'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $result = trim($_GET['result'] ?? '');

        $sql = "SELECT * FROM audit_logs WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (actor_name LIKE :s OR action LIKE :s OR entity_id LIKE :s OR reason LIKE :s)";
            $params[':s'] = "%{$search}%";
        }
        if (!empty($role)) {
            $sql .= " AND actor_role = :role";
            $params[':role'] = $role;
        }
        if (!empty($result)) {
            $sql .= " AND result = :result";
            $params[':result'] = $result;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 150";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $logs]);
    }
}
