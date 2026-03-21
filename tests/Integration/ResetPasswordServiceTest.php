<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\User\ResetPasswordService;

final class ResetPasswordServiceTest extends AdminIntegrationTestCase
{
    private ResetPasswordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ResetPasswordService($this->pdo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Happy path
    // ──────────────────────────────────────────────────────────────────────

    public function testResetsPasswordSuccessfully(): void
    {
        $result = $this->service->execute(
            userId:      $this->regularUserId,
            newPassword: 'NovaSenha@456',
            actorId:     $this->adminUserId,
            companyId:   $this->companyId,
        );

        self::assertTrue($result['ok']);

        $row = $this->pdo->query("SELECT password FROM users WHERE id = {$this->regularUserId}")->fetch(\PDO::FETCH_ASSOC);
        self::assertTrue(password_verify('NovaSenha@456', $row['password']));
    }

    // ──────────────────────────────────────────────────────────────────────
    // S06 — Password policy enforced inside the service itself
    // ──────────────────────────────────────────────────────────────────────

    public function testRejectsPasswordWithoutUppercase(): void
    {
        $result = $this->service->execute($this->regularUserId, 'semmaiu1scula', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('maiúscula', $result['error']);
    }

    public function testRejectsPasswordWithoutDigit(): void
    {
        $result = $this->service->execute($this->regularUserId, 'SemNumero!!', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
    }

    public function testRejectsPasswordTooShort(): void
    {
        $result = $this->service->execute($this->regularUserId, 'Ab1', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // User not found
    // ──────────────────────────────────────────────────────────────────────

    public function testReturnsErrorForUnknownUser(): void
    {
        $result = $this->service->execute(99999, 'NovaSenha@456', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('não encontrado', $result['error']);
    }

    public function testReturnsErrorForUserFromAnotherCompany(): void
    {
        // Create user in a different company
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Other Corp', 'active', NOW(), NOW())");
        $otherCompanyId = (int) $this->pdo->lastInsertId();

        $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, 'Other User', 'other@other.com', 'x', 'active', 0, NOW(), NOW())"
        )->execute([$otherCompanyId]);
        $otherUserId = (int) $this->pdo->lastInsertId();

        $result = $this->service->execute($otherUserId, 'NovaSenha@456', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']); // company_id mismatch → not found
    }

    // ──────────────────────────────────────────────────────────────────────
    // S07 — Audit log
    // ──────────────────────────────────────────────────────────────────────

    public function testSuccessfulResetGeneratesAuditLogEntry(): void
    {
        $this->service->execute($this->regularUserId, 'NovaSenha@456', $this->adminUserId, $this->companyId);

        $log = $this->pdo->query(
            "SELECT * FROM admin_audit_log WHERE action = 'user_password_reset' AND target_user_id = {$this->regularUserId}"
        )->fetch(\PDO::FETCH_ASSOC);

        self::assertNotFalse($log);
    }
}
