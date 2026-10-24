<?php
namespace ElectroGuard\Services;

use ElectroGuard\Config\Database;
use PDO;

class AuditLogger {
    public static function log(
        ?int $actorId,
        string $actorName,
        string $actorRole,
        string $action,
        string $entityType,
        string $entityId,
        string $result,
        ?string $reason = null,
        ?string $correlationId = null
    ): void {
        try {
            $pdo = Database::getConnection();
            $correlationId = $correlationId ?? ('CORR-' . strtoupper(bin2hex(random_bytes(4))));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (correlation_id, actor_id, actor_name, actor_role, action, entity_type, entity_id, result, reason, ip_address, created_at)
                VALUES (:cid, :actor_id, :actor_name, :actor_role, :action, :entity_type, :entity_id, :result, :reason, :ip, datetime('now'))
            ");
            $stmt->execute([
                ':cid' => $correlationId,
                ':actor_id' => $actorId,
                ':actor_name' => $actorName,
                ':actor_role' => $actorRole,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':result' => $result,
                ':reason' => $reason,
                ':ip' => $ip,
            ]);
        } catch (\Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}
