<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\DTO\LabelDTO;
use App\DTO\TaskDTO;
use App\Repositories\PdoLabelRepository;
use App\Repositories\PdoTaskRepository;

final class PdoLabelRepositoryTest extends IntegrationTestCase
{
    private PdoLabelRepository $repo;
    private int $companyId;
    private int $taskId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PdoLabelRepository($this->pdo);

        // Seed minimal hierarchy: company → user → project → board → column → task
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Co', 'active', NOW(), NOW())");
        $this->companyId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO users (company_id, name, email, password, status, created_at, updated_at)
                          VALUES ({$this->companyId}, 'U', 'u@test.com', 'x', 'active', NOW(), NOW())");
        $userId = (int) $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at)
                          VALUES ({$this->companyId}, 'P', '', $userId, NOW(), NOW())");
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

    public function testCreateAndFindById(): void
    {
        $dto = new LabelDTO(companyId: $this->companyId, name: 'Bug', color: '#FF0000');
        $id  = $this->repo->create($dto);

        self::assertGreaterThan(0, $id);

        $found = $this->repo->findById($id);
        self::assertNotNull($found);
        self::assertSame('Bug', $found->name);
        self::assertSame('#FF0000', $found->color);
        self::assertSame($this->companyId, $found->companyId);
    }

    public function testFindByIdReturnsNullForUnknown(): void
    {
        self::assertNull($this->repo->findById(99999));
    }

    public function testFindByCompanyIdReturnsAllLabels(): void
    {
        $this->repo->create(new LabelDTO(companyId: $this->companyId, name: 'A', color: '#111111'));
        $this->repo->create(new LabelDTO(companyId: $this->companyId, name: 'B', color: '#222222'));

        $labels = $this->repo->findByCompanyId($this->companyId);

        self::assertCount(2, $labels);
        self::assertSame('A', $labels[0]->name); // ordered by name ASC
        self::assertSame('B', $labels[1]->name);
    }

    public function testAttachAndDetachAndFindByTaskId(): void
    {
        $labelId = $this->repo->create(new LabelDTO(companyId: $this->companyId, name: 'Feature', color: '#00FF00'));

        $this->repo->attach($this->taskId, $labelId);

        $attached = $this->repo->findByTaskId($this->taskId);
        self::assertCount(1, $attached);
        self::assertSame('Feature', $attached[0]->name);

        $this->repo->detach($this->taskId, $labelId);

        self::assertCount(0, $this->repo->findByTaskId($this->taskId));
    }

    public function testAttachIsIdempotent(): void
    {
        $labelId = $this->repo->create(new LabelDTO(companyId: $this->companyId, name: 'Dup', color: '#FFFFFF'));

        $this->repo->attach($this->taskId, $labelId);
        $this->repo->attach($this->taskId, $labelId); // INSERT IGNORE — should not throw

        self::assertCount(1, $this->repo->findByTaskId($this->taskId));
    }

    public function testDelete(): void
    {
        $id = $this->repo->create(new LabelDTO(companyId: $this->companyId, name: 'Temp', color: '#CCCCCC'));

        $result = $this->repo->delete($id);

        self::assertTrue($result);
        self::assertNull($this->repo->findById($id));
    }
}
