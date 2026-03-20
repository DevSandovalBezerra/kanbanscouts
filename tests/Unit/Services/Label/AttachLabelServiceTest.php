<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Label;

use App\DTO\LabelDTO;
use App\Repositories\LabelRepository;
use App\Services\Label\AttachLabelService;
use PHPUnit\Framework\TestCase;

final class AttachLabelServiceTest extends TestCase
{
    private LabelRepository $repository;
    private AttachLabelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LabelRepository::class);
        $this->service    = new AttachLabelService($this->repository);
    }

    public function testReturnsFalseWhenLabelNotFound(): void
    {
        $this->repository->method('findById')->with(99)->willReturn(null);
        $this->repository->expects(self::never())->method('attach');

        $result = $this->service->execute(1, 99);

        self::assertFalse($result);
    }

    public function testAttachesWhenLabelExists(): void
    {
        $label = new LabelDTO(id: 5, companyId: 1, name: 'Bug', color: '#FF0000');

        $this->repository->method('findById')->with(5)->willReturn($label);
        $this->repository->expects(self::once())
            ->method('attach')
            ->with(10, 5)
            ->willReturn(true);

        $result = $this->service->execute(10, 5);

        self::assertTrue($result);
    }
}
