<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\User\CreateUserService;

final class CreateUserServiceTest extends AdminIntegrationTestCase
{
    private CreateUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CreateUserService($this->pdo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Happy path
    // ──────────────────────────────────────────────────────────────────────

    public function testCreatesUserAndReturnsNewId(): void
    {
        $newId = $this->service->execute(
            companyId:     $this->companyId,
            name:          'Novo Usuário',
            email:         'novo@test.com',
            plainPassword: 'Senha@123',
            isAdmin:       false,
            actorId:       $this->adminUserId,
        );

        self::assertGreaterThan(0, $newId);

        $row = $this->pdo->query("SELECT * FROM users WHERE id = $newId")->fetch(\PDO::FETCH_ASSOC);
        self::assertNotFalse($row);
        self::assertSame('novo@test.com', $row['email']);
        self::assertSame('active', $row['status']);
        self::assertSame('0', (string) $row['is_admin']);
    }

    public function testPasswordIsHashedNotStoredInPlainText(): void
    {
        $newId = $this->service->execute(
            $this->companyId, 'Hash Test', 'hash@test.com', 'Senha@999', false, $this->adminUserId
        );

        $row = $this->pdo->query("SELECT password FROM users WHERE id = $newId")->fetch(\PDO::FETCH_ASSOC);
        self::assertNotSame('Senha@999', $row['password']);
        self::assertTrue(password_verify('Senha@999', $row['password']));
    }

    public function testCreatesAdminUserWhenIsAdminIsTrue(): void
    {
        $newId = $this->service->execute(
            $this->companyId, 'Second Admin', 'admin2@test.com', 'Admin@456', true, $this->adminUserId
        );

        $row = $this->pdo->query("SELECT is_admin FROM users WHERE id = $newId")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('1', (string) $row['is_admin']);
    }

    public function testEmailIsStoredAsLowercase(): void
    {
        $newId = $this->service->execute(
            $this->companyId, 'Case Test', 'UPPER@TEST.COM', 'Senha@123', false, $this->adminUserId
        );

        $row = $this->pdo->query("SELECT email FROM users WHERE id = $newId")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('upper@test.com', $row['email']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // S07 — Audit log
    // ──────────────────────────────────────────────────────────────────────

    public function testCreationGeneratesAuditLogEntry(): void
    {
        $newId = $this->service->execute(
            $this->companyId, 'Audit Test', 'audit@test.com', 'Senha@123', false, $this->adminUserId
        );

        $log = $this->pdo->query(
            "SELECT * FROM admin_audit_log WHERE action = 'user_created' AND target_user_id = $newId"
        )->fetch(\PDO::FETCH_ASSOC);

        self::assertNotFalse($log);
        self::assertSame((string) $this->adminUserId, (string) $log['actor_id']);
    }
}
