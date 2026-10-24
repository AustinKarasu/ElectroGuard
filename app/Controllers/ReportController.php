<?php
namespace ElectroGuard\Controllers;

use ElectroGuard\Config\Database;
use ElectroGuard\Middleware\AuthMiddleware;
use ElectroGuard\Services\ClearanceRuleEngine;
use PDO;

class ReportController {
    public static function summary(): void {
        AuthMiddleware::requireRole(['admin', 'supervisor']);
        $pdo = Database::getConnection();

        // 1. Jobs summary
        $jobs = $pdo->query("SELECT id, status, restoration_status FROM maintenance_jobs")->fetchAll();
        $totalJobs = count($jobs);
        $activeJobs = 0;
        $pendingSupervisor = 0;
        $clearanceComplete = 0;
        $blockedRestorations = 0;
        $awaitingReviewRestorations = 0;
        $eligibleRestorations = 0;

        foreach ($jobs as $j) {
            $eval = ClearanceRuleEngine::evaluateJob((int)$j['id']);
            if ($eval['job_status'] === 'MAINTENANCE_ACTIVE') $activeJobs++;
            if ($eval['job_status'] === 'PENDING_SUPERVISOR_REVIEW') $pendingSupervisor++;
            if ($eval['job_status'] === 'CLEARANCE_COMPLETE') $clearanceComplete++;

            if ($eval['restoration_status'] === 'BLOCKED') $blockedRestorations++;
            if ($eval['restoration_status'] === 'AWAITING_REVIEW') $awaitingReviewRestorations++;
            if ($eval['restoration_status'] === 'ELIGIBLE_FOR_REVIEW' || $eval['restoration_status'] === 'SIMULATED_COMPLETION') $eligibleRestorations++;
        }

        // 2. Clearances count
        $totalClearancesSubmitted = (int)$pdo->query("SELECT COUNT(*) FROM clearance_submissions WHERE status = 'SUBMITTED'")->fetchColumn();
        $totalClearancesReopened = (int)$pdo->query("SELECT COUNT(*) FROM clearance_submissions WHERE status = 'REOPENED'")->fetchColumn();

        // 3. Controllers count
        $onlineControllers = (int)$pdo->query("SELECT COUNT(*) FROM controllers WHERE connection_status = 'ONLINE'")->fetchColumn();
        $offlineControllers = (int)$pdo->query("SELECT COUNT(*) FROM controllers WHERE connection_status != 'ONLINE'")->fetchColumn();

        // 4. Audit events count
        $totalAuditEvents = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

        echo json_encode([
            'success' => true,
            'data' => [
                'total_jobs' => $totalJobs,
                'active_jobs' => $activeJobs,
                'pending_supervisor' => $pendingSupervisor,
                'clearance_complete' => $clearanceComplete,
                'blocked_restorations' => $blockedRestorations,
                'awaiting_review_restorations' => $awaitingReviewRestorations,
                'eligible_restorations' => $eligibleRestorations,
                'total_clearances_submitted' => $totalClearancesSubmitted,
                'total_clearances_reopened' => $totalClearancesReopened,
                'online_controllers' => $onlineControllers,
                'offline_controllers' => $offlineControllers,
                'total_audit_events' => $totalAuditEvents
            ]
        ]);
    }

    public static function exportCsv(): void {
        AuthMiddleware::requireRole(['admin', 'supervisor']);
        $pdo = Database::getConnection();

        $stmt = $pdo->query("
            SELECT j.job_code, a.name AS area_name, j.title, j.status, j.restoration_status,
                   j.blocking_reason, u.full_name AS supervisor_name, j.created_at
            FROM maintenance_jobs j
            JOIN maintenance_areas a ON j.area_id = a.id
            JOIN users u ON j.supervisor_id = u.id
            ORDER BY j.id ASC
        ");
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="electroguard-jobs-report-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Job Code', 'Area Name', 'Work Description', 'Job Status', 'Restoration Status', 'Current Blocking Reason', 'Supervisor', 'Created At']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['job_code'],
                $row['area_name'],
                $row['title'],
                $row['status'],
                $row['restoration_status'],
                $row['blocking_reason'],
                $row['supervisor_name'],
                $row['created_at']
            ]);
        }

        fclose($out);
        exit;
    }
}
