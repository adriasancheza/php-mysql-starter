<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Env;
use PDO;
use PDOException;

/**
 * Thin PDO factory/wrapper: exceptions on error, prepared statements only,
 * utf8mb4 connection charset.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        self::$connection = self::createConnection();

        return self::$connection;
    }

    public static function createConnection(): PDO
    {
        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_DATABASE', '');
        $username = Env::get('DB_USERNAME', 'root');
        $password = Env::get('DB_PASSWORD', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * For tests: inject/reset the shared connection.
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$connection = $pdo;
    }

    /**
     * Reports whether the configured database is reachable. Used by
     * integration tests to skip gracefully when no MySQL is available.
     */
    public static function isAvailable(): bool
    {
        try {
            self::createConnection();

            return true;
        } catch (PDOException) {
            return false;
        }
    }
}
