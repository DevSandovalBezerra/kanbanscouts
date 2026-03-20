<?php

declare(strict_types=1);

namespace Tests\Functional\Api;

use App\Controllers\TaskController;
use App\Helpers\HttpRequest;
use App\Services\ArraySessionStore;
use App\Services\Task\CreateTaskService;
use App\Services\Task\MoveTaskService;
use PHPUnit\Framework\TestCase;

final class TaskControllerTest extends TestCase
{
    private function makeController(
        \App\Repositories\TaskRepository $taskRepo,
        ?\App\Repositories\HistoryRepository $historyRepo = null,
        int $userId = 123,
    ): TaskController {
        $historyRepo ??= $this->createMock(\App\Repositories\HistoryRepository::class);
        $session = new ArraySessionStore();
        $session->set('user_id', $userId);

        return new TaskController(
            new CreateTaskService($taskRepo),
            new MoveTaskService($taskRepo, $historyRepo),
            $taskRepo,
            $session,
        );
    }

    public function testCreateTaskReturns201(): void
    {
        $repository = $this->createMock(\App\Repositories\TaskRepository::class);
        $session = new ArraySessionStore();
        $session->set('user_id', 123);

        $controller = $this->makeController($repository, userId: 123);

        $repository->expects(self::once())
            ->method('create')
            ->willReturn(789);

        $request = new HttpRequest('POST', '/api/tasks', ['content-type' => 'application/json'], json_encode([
            'title' => 'My Task',
            'column_id' => 10
        ]));

        $response = $controller->create($request);

        self::assertSame(201, $response->statusCode());
        self::assertSame('{"id":789}', $response->body());
    }

    public function testMoveTaskReturns200(): void
    {
        $repository = $this->createMock(\App\Repositories\TaskRepository::class);
        $repository->method('findById')->willReturn(null);
        $repository->expects(self::once())
            ->method('move')
            ->with(1, 2, 3)
            ->willReturn(true);

        $controller = $this->makeController($repository, userId: 0);

        $request = new HttpRequest('POST', '/api/tasks/move', ['content-type' => 'application/json'], json_encode([
            'id' => 1,
            'to_column_id' => 2,
            'to_position' => 3
        ]));

        $response = $controller->move($request);

        self::assertSame(200, $response->statusCode());
        self::assertSame('{"ok":true}', $response->body());
    }

}
