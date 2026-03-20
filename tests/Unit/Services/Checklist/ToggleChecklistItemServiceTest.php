<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Checklist;

use App\Repositories\ChecklistRepository;
use App\Services\Checklist\ToggleChecklistItemService;
use PHPUnit\Framework\TestCase;

final class ToggleChecklistItemServiceTest extends TestCase
{
    private ChecklistRepository $repository;
    private ToggleChecklistItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ChecklistRepository::class);
        $this->service    = new ToggleChecklistItemService($this->repository);
    }

    public function testTogglesToDone(): void
    {
        $this->repository->expects(self::once())
            ->method('toggleItem')
            ->with(7, true)
            ->willReturn(true);

        $result = $this->service->execute(7, true);

        self::assertTrue($result);
    }

    public function testTogglesToUndone(): void
    {
        $this->repository->expects(self::once())
            ->method('toggleItem')
            ->with(7, false)
            ->willReturn(true);

        $result = $this->service->execute(7, false);

        self::assertTrue($result);
    }
}
