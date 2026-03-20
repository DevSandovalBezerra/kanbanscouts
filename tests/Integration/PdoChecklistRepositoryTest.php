<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\TaskDTO;
use App\Repositories\PdoChecklistRepository;
use App\Repositories\PdoTaskRepository;

final class PdoChecklistRepositoryTest extends IntegrationTestCase
{
    private PdoChecklistRepository $repo;
    private int $taskId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PdoChecklistRepository($this->pdo);

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

        $taskRepo     = new PdoTaskRepository($this->pdo);
        $this->taskId = $taskRepo->create(new TaskDTO(
            columnId: $colId,
            title: 'T',
            description: '',
            priority: 'low',
            status: 'active',
            position: 1,
            createdBy: $userId,
        ));
    }

    public function testCreateChecklistAndFindByTaskId(): void
    {
        $id = $this->repo->createChecklist($this->taskId, 'DoD');

        self::assertGreaterThan(0, $id);

        $checklists = $this->repo->findByTaskId($this->taskId);
        self::assertCount(1, $checklists);
        self::assertSame('DoD', $checklists[0]->title);
        self::assertSame($this->taskId, $checklists[0]->taskId);
    }

    public function testAddItemAndFindWithinChecklist(): void
    {
        $clId  = $this->repo->createChecklist($this->taskId, 'Steps');
        $itemId = $this->repo->addItem($clId, 'Passo 1', 0);

        self::assertGreaterThan(0, $itemId);

        $checklists = $this->repo->findByTaskId($this->taskId);
        $items      = $checklists[0]->items;

        self::assertCount(1, $items);
        self::assertSame('Passo 1', $items[0]->body);
        self::assertFalse($items[0]->isDone);
    }

    public function testToggleItemMarksDone(): void
    {
        $clId   = $this->repo->createChecklist($this->taskId, 'CL');
        $itemId = $this->repo->addItem($clId, 'Tarefa', 0);

        $this->repo->toggleItem($itemId, true);

        $item = $this->repo->findItemById($itemId);
        self::assertNotNull($item);
        self::assertTrue($item->isDone);
    }

    public function testToggleItemMarksUndone(): void
    {
        $clId   = $this->repo->createChecklist($this->taskId, 'CL');
        $itemId = $this->repo->addItem($clId, 'Tarefa', 0);

        $this->repo->toggleItem($itemId, true);
        $this->repo->toggleItem($itemId, false);

        $item = $this->repo->findItemById($itemId);
        self::assertFalse($item->isDone);
    }

    public function testDeleteItem(): void
    {
        $clId   = $this->repo->createChecklist($this->taskId, 'CL');
        $itemId = $this->repo->addItem($clId, 'Remover', 0);

        $this->repo->deleteItem($itemId);

        self::assertNull($this->repo->findItemById($itemId));
    }

    public function testDeleteChecklist(): void
    {
        $clId = $this->repo->createChecklist($this->taskId, 'Remove me');

        $result = $this->repo->deleteChecklist($clId);

        self::assertTrue($result);
        self::assertCount(0, $this->repo->findByTaskId($this->taskId));
    }

    public function testItemsLoadedEagerlyForMultipleChecklists(): void
    {
        $cl1 = $this->repo->createChecklist($this->taskId, 'CL1');
        $cl2 = $this->repo->createChecklist($this->taskId, 'CL2');

        $this->repo->addItem($cl1, 'A', 0);
        $this->repo->addItem($cl1, 'B', 1);
        $this->repo->addItem($cl2, 'C', 0);

        $checklists = $this->repo->findByTaskId($this->taskId);

        self::assertCount(2, $checklists);
        self::assertCount(2, $checklists[0]->items);
        self::assertCount(1, $checklists[1]->items);
    }
}
