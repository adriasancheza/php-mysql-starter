#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database;
use App\Support\Env;

require dirname(__DIR__) . '/vendor/autoload.php';

Env::load(dirname(__DIR__) . '/.env');

$migrationsPath = dirname(__DIR__) . '/migrations';

$pdo = Database::connection();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        migration VARCHAR(191) NOT NULL,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_migrations_migration (migration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
/** @var list<string> $applied */

$files = glob($migrationsPath . '/*.sql');
if ($files === false) {
    $files = [];
}
sort($files);

$ranCount = 0;

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $applied, true)) {
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Could not read migration file: {$name}\n");
        exit(1);
    }

    echo "Applying {$name}...\n";

    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);

        $stmt = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
        $stmt->execute(['migration' => $name]);

        $pdo->commit();
        $ranCount++;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "Migration failed ({$name}): {$e->getMessage()}\n");
        exit(1);
    }
}

if ($ranCount === 0) {
    echo "Nothing to migrate. Database is up to date.\n";
} else {
    echo "Applied {$ranCount} migration(s).\n";
}
