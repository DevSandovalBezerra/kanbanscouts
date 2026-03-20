<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Checklist;

use App\Repositories\ChecklistRepository;
use App\Services\Checklist\AddChecklistItemService;
use PHPUnit\Framework\TestCase;

final class AddChecklistItemServiceTest extends TestCase
{
    private ChecklistRepository $repository;
    private AddChecklistItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ChecklistRepository::class);
        $this->service    = new AddChecklistItemService($this->repository);
    }

    public function testAddsItemWithTrimmedBody(): void
    {
        $this->repository->expects(self::once())
            ->method('addItem')
            ->with(1, 'Escrever testes', 0)
            ->willReturn(5);

        $id = $this->service->execute(1, '  Escrever testes  ');

        self::assertSame(5, $id);
    }

    public function testPositionIsPassedThrough(): void
    {
        $this->repository->expects(self::once())
            ->method('addItem')
            ->with(2, 'Deploy', 3)
            ->willReturn(6);

        $this->service->execute(2, 'Deploy', 3);
    }
}
