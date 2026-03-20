<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Checklist;

use App\Repositories\ChecklistRepository;
use App\Services\Checklist\CreateChecklistService;
use PHPUnit\Framework\TestCase;

final class CreateChecklistServiceTest extends TestCase
{
    private ChecklistRepository $repository;
    private CreateChecklistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(ChecklistRepository::class);
        $this->service    = new CreateChecklistService($this->repository);
    }

    public function testCreatesChecklistWithGivenTitle(): void
    {
        $this->repository->expects(self::once())
            ->method('createChecklist')
            ->with(1, 'Critérios de Aceite')
            ->willReturn(10);

        $id = $this->service->execute(1, 'Critérios de Aceite');

        self::assertSame(10, $id);
    }

    public function testEmptyTitleFallsBackToDefault(): void
    {
        $this->repository->expects(self::once())
            ->method('createChecklist')
            ->with(1, 'Checklist')
            ->willReturn(11);

        $this->service->execute(1, '');
    }

    public function testWhitespaceOnlyTitleFallsBackToDefault(): void
    {
        $this->repository->expects(self::once())
            ->method('createChecklist')
            ->with(1, 'Checklist')
            ->willReturn(12);

        $this->service->execute(1, '   ');
    }

    public function testDefaultTitleUsedWhenOmitted(): void
    {
        $this->repository->expects(self::once())
            ->method('createChecklist')
            ->with(5, 'Checklist')
            ->willReturn(13);

        $this->service->execute(5);
    }
}
