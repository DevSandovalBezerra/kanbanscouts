<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\PdoConnectionFactory;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected ?PDO $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        $config = require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        
        // We attempt to use a test database if possible, or the current one.
        // For this environment, we use the configured database but we will be careful.
        $this->pdo = PdoConnectionFactory::fromConfig($config);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Optional: Wrap in transaction OR manually clean tables.
        // Since we are in EXECUTION mode, we'll manually clean the relevant tables for the test.
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->pdo->exec('TRUNCATE TABLE task_dependencies');
        $this->pdo->exec('TRUNCATE TABLE task_labels');
        $this->pdo->exec('TRUNCATE TABLE task_checklist_items');
        $this->pdo->exec('TRUNCATE TABLE task_checklists');
        $this->pdo->exec('TRUNCATE TABLE task_attachments');
        $this->pdo->exec('TRUNCATE TABLE task_history');
        $this->pdo->exec('TRUNCATE TABLE task_comments');
        $this->pdo->exec('TRUNCATE TABLE tasks');
        $this->pdo->exec('TRUNCATE TABLE labels');
        $this->pdo->exec('TRUNCATE TABLE columns');
        $this->pdo->exec('TRUNCATE TABLE boards');
        $this->pdo->exec('TRUNCATE TABLE projects');
        $this->pdo->exec('TRUNCATE TABLE users');
        $this->pdo->exec('TRUNCATE TABLE companies');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
