<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\TaskDTO;
use App\Repositories\PdoTaskRepository;
use App\Services\Task\AddDependencyService;
use App\Services\Task\RemoveDependencyService;

final class AddDependencyServiceTest extends IntegrationTestCase
{
    private AddDependencyService $addService;
    private RemoveDependencyService $removeService;
    private int $taskA;
    private int $taskB;
    private int $taskC;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addService    = new AddDependencyService($this->pdo);
        $this->removeService = new RemoveDependencyService($this->pdo);

        // Minimal data hierarchy
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Co', 'active', NOW(), NOW())");
        $companyId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at)
                          VALUES ($companyId, 'U', 'u@t.com', 'x', 'active', NOW(), NOW())");
        $userId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at)
                          VALUES ($companyId, 'P', '', $userId, NOW(), NOW())");
        $projectId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO boards (project_id, name, created_by, created_at, updated_at)
                          VALUES ($projectId, 'B', $userId, NOW(), NOW())");
        $boardId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO columns (board_id, name, position, created_at, updated_at)
                          VALUES ($boardId, 'C', 1, NOW(), NOW())");
        $colId = (int) $this->pdo->lastInsertId();

        $taskRepo    = new PdoTaskRepository($this->pdo);
        $makeTask    = fn (string $title) => $taskRepo->create(new TaskDTO(
            columnId: $colId, title: $title, description: '',
            priority: 'low', status: 'active', position: 1, createdBy: $userId,
        ));

        $this->taskA = $makeTask('A');
        $this->taskB = $makeTask('B');
        $this->taskC = $makeTask('C');
    }

    public function testAddsDependencySuccessfully(): void
    {
        $result = $this->addService->execute($this->taskA, $this->taskB);

        self::assertTrue($result);
        $this->assertDependencyExists($this->taskA, $this->taskB);
    }

    public function testReturnsFalseForDirectCycle(): void
    {
        $this->addService->execute($this->taskA, $this->taskB); // A depends on B

        $result = $this->addService->execute($this->taskB, $this->taskA); // B depends on A → cycle

        self::assertFalse($result);
        $this->assertDependencyNotExists($this->taskB, $this->taskA);
    }

    public function testThrowsForSelfDependency(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->addService->execute($this->taskA, $this->taskA);
    }

    public function testDuplicateDependencyIsIgnored(): void
    {
        $this->addService->execute($this->taskA, $this->taskB);
        $result = $this->addService->execute($this->taskA, $this->taskB); // INSERT IGNORE

        // Should succeed without creating a duplicate
        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM task_dependencies WHERE task_id = {$this->taskA} AND depends_on_id = {$this->taskB}")
            ->fetchColumn();

        self::assertSame(1, $count);
    }

    public function testRemovesDependency(): void
    {
        $this->addService->execute($this->taskA, $this->taskB);

        $result = $this->removeService->execute($this->taskA, $this->taskB);

        self::assertTrue($result);
        $this->assertDependencyNotExists($this->taskA, $this->taskB);
    }

    public function testRemovingNonExistentDependencyReturnsTrue(): void
    {
        // DELETE on no rows still returns true (PDOStatement::execute)
        $result = $this->removeService->execute($this->taskA, $this->taskC);

        self::assertTrue($result);
    }

    public function testMultipleDependenciesCanCoexist(): void
    {
        $this->addService->execute($this->taskA, $this->taskB);
        $this->addService->execute($this->taskA, $this->taskC);

        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM task_dependencies WHERE task_id = {$this->taskA}")
            ->fetchColumn();

        self::assertSame(2, $count);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function assertDependencyExists(int $taskId, int $dependsOnId): void
    {
        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM task_dependencies WHERE task_id = $taskId AND depends_on_id = $dependsOnId")
            ->fetchColumn();

        self::assertSame(1, $count, "Expected dependency {$taskId}→{$dependsOnId} to exist.");
    }

    private function assertDependencyNotExists(int $taskId, int $dependsOnId): void
    {
        $count = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM task_dependencies WHERE task_id = $taskId AND depends_on_id = $dependsOnId")
            ->fetchColumn();

        self::assertSame(0, $count, "Expected dependency {$taskId}→{$dependsOnId} to NOT exist.");
    }
}
