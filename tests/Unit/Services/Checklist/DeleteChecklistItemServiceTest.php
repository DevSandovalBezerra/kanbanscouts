<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Checklist;

use App\Repositories\ChecklistRepository;
use App\Services\Checklist\DeleteChecklistItemService;
use PHPUnit\Framework\TestCase;

final class DeleteChecklistItemServiceTest extends TestCase
{
    public function testDelegatesDeletionToRepository(): void
    {
        $repo = $this->createMock(ChecklistRepository::class);
        $repo->expects(self::once())
            ->method('deleteItem')
            ->with(42)
            ->willReturn(true);

        $service = new DeleteChecklistItemService($repo);
        $result  = $service->execute(42);

        self::assertTrue($result);
    }
}
