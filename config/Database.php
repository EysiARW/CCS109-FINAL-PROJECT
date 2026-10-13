<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

/**
 * Database Connection Manager (Singleton Pattern)
 * Provides centralized PDO connection to MySQL / MariaDB (ssis_db)
 */
class Database {
    private static ?PDO $instance = null;
    private static bool $attempted = false;

    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3307';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_NAME = 'ssis_db';

    private function __construct() {}
    private function __clone() {}

    /**
     * Get active PDO instance or null if unavailable
     */
    public static function getConnection(bool $forceReconnect = false): ?PDO {
        if ($forceReconnect) {
            self::$attempted = false;
            self::$instance  = null;
        }

        if (self::$attempted) {
            return self::$instance;
        }
        self::$attempted = true;

        $host = getenv('DB_HOST') ?: self::DB_HOST;
        $envPort = getenv('DB_PORT');
        $portsToTry = $envPort ? [$envPort] : ['3306', '3307'];
        $user = getenv('DB_USER') ?: self::DB_USER;
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::DB_PASS;
        $name = getenv('DB_NAME') ?: self::DB_NAME;

        foreach ($portsToTry as $port) {
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT            => 2,
                ]);
                return self::$instance;
            } catch (PDOException $e) {
                // If database does not exist on this port, try creating it
                try {
                    $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $serverPdo = new PDO($serverDsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 2,
                    ]);
                    self::runSchemaSql($serverPdo);

                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    return self::$instance;
                } catch (Exception $inner) {
                    continue;
                }
            }
        }
        self::$instance = null;
        return null;
    }

    /**
     * Check if MySQL connection is currently alive
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }

    /**
     * Executes ssis_schema.sql to set up or re-seed tables
     */
    public static function initializeSchema(): bool {
        self::$attempted = false;
        self::$instance  = null;

        $host = getenv('DB_HOST') ?: self::DB_HOST;
        $envPort = getenv('DB_PORT');
        $portsToTry = $envPort ? [$envPort] : ['3306', '3307'];
        $user = getenv('DB_USER') ?: self::DB_USER;
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::DB_PASS;

        foreach ($portsToTry as $port) {
            try {
                $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                $pdo = new PDO($serverDsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 2,
                ]);
                $ok = self::runSchemaSql($pdo);
                if ($ok) {
                    self::getConnection(true);
                    return true;
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return false;
    }

    private static function runSchemaSql(PDO $pdo): bool {
        $schemaPath = dirname(__DIR__) . '/ssis_schema.sql';
        if (!file_exists($schemaPath)) {
            return false;
        }
        $sql = file_get_contents($schemaPath);
        try {
            $pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    try {
                        $pdo->exec($stmt);
                    } catch (Exception $ignored) {}
                }
            }
            return true;
        }
    }
}
