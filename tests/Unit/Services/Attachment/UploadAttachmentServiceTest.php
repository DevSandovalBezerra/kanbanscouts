<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Attachment;

use App\Repositories\AttachmentRepository;
use App\Services\Attachment\UploadAttachmentService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UploadAttachmentServiceTest extends TestCase
{
    private AttachmentRepository $repository;
    private UploadAttachmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(AttachmentRepository::class);
        $this->service    = new UploadAttachmentService($this->repository, sys_get_temp_dir());
    }

    public function testThrowsOnUploadError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Erro no upload/');

        $this->service->execute(1, 1, [
            'name'     => 'file.txt',
            'tmp_name' => '/tmp/phpXXXX',
            'type'     => 'text/plain',
            'size'     => 100,
            'error'    => UPLOAD_ERR_INI_SIZE,
        ]);
    }

    public function testThrowsWhenFileTooLarge(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/10 MB/');

        $this->service->execute(1, 1, [
            'name'     => 'big.pdf',
            'tmp_name' => '/tmp/phpXXXX',
            'type'     => 'application/pdf',
            'size'     => 11 * 1024 * 1024, // 11 MB
            'error'    => UPLOAD_ERR_OK,
        ]);
    }

    /**
     * @requires extension fileinfo
     */
    public function testThrowsOnDisallowedMimeType(): void
    {
        // Create a real temp file with PHP-script content (text/x-php or similar)
        $tmpFile = tempnam(sys_get_temp_dir(), 'mime_test_');
        file_put_contents($tmpFile, '<?php echo "malicious"; ?>');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/não permitido/');

        try {
            $this->service->execute(1, 1, [
                'name'     => 'malicious.php',
                'tmp_name' => $tmpFile,
                'type'     => 'text/plain', // spoofed type
                'size'     => filesize($tmpFile),
                'error'    => UPLOAD_ERR_OK,
            ]);
        } finally {
            if (is_file($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
