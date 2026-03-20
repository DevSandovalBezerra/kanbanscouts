<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Label;

use App\Repositories\LabelRepository;
use App\Services\Label\DetachLabelService;
use PHPUnit\Framework\TestCase;

final class DetachLabelServiceTest extends TestCase
{
    public function testDelegatesDetachToRepository(): void
    {
        $repo = $this->createMock(LabelRepository::class);
        $repo->expects(self::once())
            ->method('detach')
            ->with(3, 7)
            ->willReturn(true);

        $service = new DetachLabelService($repo);
        $result  = $service->execute(3, 7);

        self::assertTrue($result);
    }
}
