<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Label;

use App\DTO\LabelDTO;
use App\Repositories\LabelRepository;
use App\Services\Label\CreateLabelService;
use PHPUnit\Framework\TestCase;

final class CreateLabelServiceTest extends TestCase
{
    private LabelRepository $repository;
    private CreateLabelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LabelRepository::class);
        $this->service    = new CreateLabelService($this->repository);
    }

    public function testValidHexColorIsStored(): void
    {
        $this->repository->expects(self::once())
            ->method('create')
            ->with(self::callback(fn (LabelDTO $dto) => $dto->color === '#FF5733'))
            ->willReturn(1);

        $this->service->execute(1, 'Bug', '#FF5733');
    }

    public function testHexColorIsUppercased(): void
    {
        $this->repository->expects(self::once())
            ->method('create')
            ->with(self::callback(fn (LabelDTO $dto) => $dto->color === '#AABBCC'))
            ->willReturn(1);

        $this->service->execute(1, 'Feature', '#aabbcc');
    }

    public function testInvalidHexFallsBackToDefault(): void
    {
        $this->repository->expects(self::once())
            ->method('create')
            ->with(self::callback(fn (LabelDTO $dto) => $dto->color === '#6200EE'))
            ->willReturn(1);

        $this->service->execute(1, 'Label', 'not-a-color');
    }

    public function testNameIsTrimed(): void
    {
        $this->repository->expects(self::once())
            ->method('create')
            ->with(self::callback(fn (LabelDTO $dto) => $dto->name === 'Urgente'))
            ->willReturn(2);

        $this->service->execute(1, '  Urgente  ', '#FF0000');
    }

    public function testReturnsRepositoryId(): void
    {
        $this->repository->method('create')->willReturn(42);

        $id = $this->service->execute(1, 'X', '#123456');

        self::assertSame(42, $id);
    }
}
