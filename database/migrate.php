<?php
require_once __DIR__ . '/../app/Config/Database.php';

use ElectroGuard\Config\Database;

echo "=== ElectroGuard Database Migration & Seed Tool ===" . PHP_EOL;
echo "Team: GodsPlanKangra | Project: ElectroGuard" . PHP_EOL . PHP_EOL;

try {
    echo "Resetting and migrating database..." . PHP_EOL;
    Database::resetDatabase();
    $pdo = Database::getConnection();
    
    // Verify count of users and jobs
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $jobCount = $pdo->query("SELECT COUNT(*) FROM maintenance_jobs")->fetchColumn();
    $areaCount = $pdo->query("SELECT COUNT(*) FROM maintenance_areas")->fetchColumn();

    echo "[SUCCESS] Database migrated and seeded successfully!" . PHP_EOL;
    echo "  - Driver: " . Database::getDriver() . PHP_EOL;
    echo "  - Users: {$userCount}" . PHP_EOL;
    echo "  - Maintenance Areas: {$areaCount}" . PHP_EOL;
    echo "  - Demonstration Jobs: {$jobCount}" . PHP_EOL;
    echo "Ready for operation." . PHP_EOL;
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
