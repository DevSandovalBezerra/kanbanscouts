<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Attachment;

use App\DTO\AttachmentDTO;
use App\Repositories\AttachmentRepository;
use App\Services\Attachment\DeleteAttachmentService;
use PHPUnit\Framework\TestCase;

final class DeleteAttachmentServiceTest extends TestCase
{
    private AttachmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(AttachmentRepository::class);
    }

    public function testReturnsFalseWhenAttachmentNotFound(): void
    {
        $this->repository->method('findById')->with(99)->willReturn(null);
        $this->repository->expects(self::never())->method('delete');

        $service = new DeleteAttachmentService($this->repository, '/tmp/uploads');
        $result  = $service->execute(99);

        self::assertFalse($result);
    }

    public function testDeletesRecordWhenFileDoesNotExistOnDisk(): void
    {
        $attachment = new AttachmentDTO(
            id: 1,
            taskId: 10,
            uploadedBy: 1,
            filename: 'missing.pdf',
            filepath: 'tasks/10/non-existent-uuid.pdf',
            mimeType: 'application/pdf',
            sizeBytes: 1024,
        );

        $this->repository->method('findById')->willReturn($attachment);
        $this->repository->expects(self::once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        // Base path is /tmp where that relative file certainly doesn't exist
        $service = new DeleteAttachmentService($this->repository, '/tmp');
        $result  = $service->execute(1);

        self::assertTrue($result);
    }

    public function testDeletesPhysicalFileAndRecord(): void
    {
        // Create a real temp file
        $tmpDir  = sys_get_temp_dir();
        $tmpFile = tempnam($tmpDir, 'attach_test_');
        file_put_contents($tmpFile, 'test content');

        $relativePath = basename($tmpFile);

        $attachment = new AttachmentDTO(
            id: 2,
            taskId: 5,
            uploadedBy: 1,
            filename: 'report.pdf',
            filepath: $relativePath,
            mimeType: 'application/pdf',
            sizeBytes: 12,
        );

        $this->repository->method('findById')->willReturn($attachment);
        $this->repository->expects(self::once())->method('delete')->with(2)->willReturn(true);

        $service = new DeleteAttachmentService($this->repository, $tmpDir);
        $result  = $service->execute(2);

        self::assertTrue($result);
        self::assertFileDoesNotExist($tmpFile);
    }
}
