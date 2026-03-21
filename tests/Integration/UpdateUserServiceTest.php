<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\User\UpdateUserService;

final class UpdateUserServiceTest extends AdminIntegrationTestCase
{
    private UpdateUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UpdateUserService($this->pdo);
    }

    public function testUpdatesName(): void
    {
        $ok = $this->service->execute($this->regularUserId, ['name' => 'Novo Nome'], $this->adminUserId);

        self::assertTrue($ok);

        $row = $this->pdo->query("SELECT name FROM users WHERE id = {$this->regularUserId}")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('Novo Nome', $row['name']);
    }

    public function testUpdatesEmail(): void
    {
        $ok = $this->service->execute($this->regularUserId, ['email' => 'Novo@Test.COM'], $this->adminUserId);

        self::assertTrue($ok);

        $row = $this->pdo->query("SELECT email FROM users WHERE id = {$this->regularUserId}")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('novo@test.com', $row['email']); // stored lowercase
    }

    public function testPromotesToAdmin(): void
    {
        $ok = $this->service->execute($this->regularUserId, ['is_admin' => true], $this->adminUserId);

        self::assertTrue($ok);

        $row = $this->pdo->query("SELECT is_admin FROM users WHERE id = {$this->regularUserId}")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('1', (string) $row['is_admin']);
    }

    public function testEmptyPayloadReturnsTrueWithoutChanges(): void
    {
        $ok = $this->service->execute($this->regularUserId, [], $this->adminUserId);
        self::assertTrue($ok);
    }

    public function testReturnsFalseForNonExistentUser(): void
    {
        $ok = $this->service->execute(99999, ['name' => 'X'], $this->adminUserId);
        self::assertFalse($ok);
    }

    // ──────────────────────────────────────────────────────────────────────
    // S07 — Audit log
    // ──────────────────────────────────────────────────────────────────────

    public function testUpdateGeneratesAuditLogEntry(): void
    {
        $this->service->execute($this->regularUserId, ['name' => 'Auditado'], $this->adminUserId);

        $log = $this->pdo->query(
            "SELECT * FROM admin_audit_log WHERE action = 'user_updated' AND target_user_id = {$this->regularUserId}"
        )->fetch(\PDO::FETCH_ASSOC);

        self::assertNotFalse($log);

        $meta = json_decode($log['meta'], true);
        self::assertArrayHasKey('before', $meta);
        self::assertArrayHasKey('after', $meta);
    }
}
