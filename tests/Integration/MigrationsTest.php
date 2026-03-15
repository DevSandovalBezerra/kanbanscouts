<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Helpers\DatabaseMigrator;
use PDO;
use PHPUnit\Framework\TestCase;

final class MigrationsTest extends TestCase
{
    public function testSqliteMigrationsCreateMvpTables(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('PDO SQLite driver is not available in this PHP runtime.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $migrator = new DatabaseMigrator(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'sqlite');
        $migrator->migrate($pdo);

        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);

        self::assertContains('companies', $tables);
        self::assertContains('users', $tables);
        self::assertContains('projects', $tables);
        self::assertContains('boards', $tables);
        self::assertContains('columns', $tables);
        self::assertContains('tasks', $tables);
        self::assertContains('task_comments', $tables);
        self::assertContains('task_history', $tables);
    }
}
