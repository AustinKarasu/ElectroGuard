<?php
namespace ElectroGuard\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::initConnection();
        }
        return self::$instance;
    }

    private static function initConnection(): void {
        $envFile = __DIR__ . '/../../.env';
        $env = [];
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (strpos($line, '=') !== false) {
                    [$key, $val] = explode('=', $line, 2);
                    $env[trim($key)] = trim($val);
                }
            }
        }

        $connection = $env['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'sqlite';
        self::$driver = $connection;

        try {
            if ($connection === 'mysql') {
                $host = $env['DB_HOST'] ?? '127.0.0.1';
                $port = $env['DB_PORT'] ?? '3306';
                $database = $env['DB_DATABASE'] ?? 'electroguard';
                $username = $env['DB_USERNAME'] ?? 'root';
                $password = $env['DB_PASSWORD'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                // SQLite default
                $dbPath = __DIR__ . '/../../database/electroguard.sqlite';
                $isNew = !file_exists($dbPath);
                
                self::$instance = new PDO("sqlite:{$dbPath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$instance->exec("PRAGMA foreign_keys = ON;");

                if ($isNew || filesize($dbPath) === 0) {
                    self::runMigrationsAndSeeds();
                }
            }
        } catch (PDOException $e) {
            // If connection fails, output structured error if in API mode or throw
            error_log("Database Connection Failed: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    public static function runMigrationsAndSeeds(): void {
        $pdo = self::getConnection();
        $schemaFile = __DIR__ . '/../../database/schema.sql';
        $seedFile = __DIR__ . '/../../database/seed.sql';

        if (file_exists($schemaFile)) {
            $schemaSql = file_get_contents($schemaFile);
            $pdo->exec($schemaSql);
        }

        if (file_exists($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
        }
    }

    public static function resetDatabase(): bool {
        $dbPath = __DIR__ . '/../../database/electroguard.sqlite';
        self::$instance = null;
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        self::initConnection();
        return true;
    }
}
