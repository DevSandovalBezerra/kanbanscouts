<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use RuntimeException;

final class DatabaseMigrator
{
    public function __construct(
        private readonly string $migrationsDirectory
    ) {
    }

    public function migrate(PDO $pdo): void
    {
        $files = glob($this->migrationsDirectory . DIRECTORY_SEPARATOR . '*.sql');
        if ($files === false) {
            throw new RuntimeException('Failed to read migrations directory');
        }

        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new RuntimeException('Failed to read migration: ' . $file);
            }

            $sql = trim($sql);
            if ($sql === '') {
                continue;
            }

            $pdo->exec($sql);
        }
    }
}
