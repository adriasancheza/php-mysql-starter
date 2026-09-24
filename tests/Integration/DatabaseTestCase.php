<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base class for tests that need a real MySQL connection. Skips the whole
 * test (rather than failing) when no database is configured/reachable,
 * so the suite still passes in environments without MySQL.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        if (!Database::isAvailable()) {
            self::markTestSkipped('No MySQL database available for integration tests (set DB_* env vars).');
        }

        $this->pdo = Database::connection();
        $this->migrate();
        $this->truncateAll();
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->truncateAll();
        }
    }

    private function migrate(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                migration VARCHAR(191) NOT NULL,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_migrations_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $applied = $this->pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

        $migrationsPath = dirname(__DIR__, 2) . '/migrations';
        $files = glob($migrationsPath . '/*.sql') ?: [];
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql !== false) {
                $this->pdo->exec($sql);
                $stmt = $this->pdo->prepare('INSERT INTO migrations (migration) VALUES (:m)');
                $stmt->execute(['m' => $name]);
            }
        }
    }

    private function truncateAll(): void
    {
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['notes', 'login_attempts', 'users'] as $table) {
            $this->pdo->exec("TRUNCATE TABLE {$table}");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
