<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\TaskDTO;
use App\Repositories\HistoryRepository;
use App\Repositories\TaskRepository;
use App\Services\Task\MoveTaskService;
use PHPUnit\Framework\TestCase;

final class MoveTaskServiceTest extends TestCase
{
    private TaskRepository $taskRepo;
    private HistoryRepository $historyRepo;
    private MoveTaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepo    = $this->createMock(TaskRepository::class);
        $this->historyRepo = $this->createMock(HistoryRepository::class);
        $this->service     = new MoveTaskService($this->taskRepo, $this->historyRepo);
    }

    public function testCanMoveTask(): void
    {
        $existingTask = new TaskDTO(
            id: 1, columnId: 2, title: 'T', description: '',
            priority: 'low', status: 'active', position: 1, createdBy: 1,
        );

        $this->taskRepo->method('findById')->with(1)->willReturn($existingTask);
        $this->taskRepo->expects(self::once())
            ->method('move')
            ->with(1, 10, 5)
            ->willReturn(true);

        $this->historyRepo->expects(self::once())->method('log');

        $result = $this->service->execute(1, 10, 5, 1);
        self::assertTrue($result);
    }

    public function testHistoryNotLoggedOnFailure(): void
    {
        $this->taskRepo->method('findById')->willReturn(null);
        $this->taskRepo->method('move')->willReturn(false);
        $this->historyRepo->expects(self::never())->method('log');

        $result = $this->service->execute(1, 10, 5, 1);
        self::assertFalse($result);
    }
}
