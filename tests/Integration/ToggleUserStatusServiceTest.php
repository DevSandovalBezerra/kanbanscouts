<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\User\ToggleUserStatusService;

final class ToggleUserStatusServiceTest extends AdminIntegrationTestCase
{
    private ToggleUserStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ToggleUserStatusService($this->pdo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Happy path
    // ──────────────────────────────────────────────────────────────────────

    public function testDeactivatesActiveUser(): void
    {
        $result = $this->service->execute($this->regularUserId, $this->adminUserId, $this->companyId);

        self::assertTrue($result['ok']);
        self::assertSame('inactive', $result['new_status']);

        $row = $this->pdo->query("SELECT status FROM users WHERE id = {$this->regularUserId}")->fetch(\PDO::FETCH_ASSOC);
        self::assertSame('inactive', $row['status']);
    }

    public function testReactivatesInactiveUser(): void
    {
        // First deactivate
        $this->pdo->exec("UPDATE users SET status = 'inactive' WHERE id = {$this->regularUserId}");

        $result = $this->service->execute($this->regularUserId, $this->adminUserId, $this->companyId);

        self::assertTrue($result['ok']);
        self::assertSame('active', $result['new_status']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Business-rule violations
    // ──────────────────────────────────────────────────────────────────────

    public function testCannotToggleOwnStatus(): void
    {
        $result = $this->service->execute(
            userId:    $this->adminUserId,
            actorId:   $this->adminUserId, // same person
            companyId: $this->companyId,
        );

        self::assertFalse($result['ok']);
        self::assertStringContainsString('próprio', $result['error']);
    }

    /** S11 — Cannot disable the last active admin. */
    public function testCannotDisableLastAdmin(): void
    {
        // adminUserId is the only admin; regularUserId is not admin
        // Trying to disable the sole admin (by another actor — but wait, regularUserId is not admin
        // so we need a second admin to try disabling the last one)

        // Create a second admin to act as the actor
        $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, 'Actor Admin', 'actor@test.com', 'x', 'active', 1, NOW(), NOW())"
        )->execute([$this->companyId]);
        $actorId = (int) $this->pdo->lastInsertId();

        // adminUserId is the last remaining original admin (actorId is also admin but let's remove them)
        // Actually, now we have 2 admins. Let's make adminUserId the "last" by deactivating actorId first
        // Simpler: just test with only 1 admin in the DB from the start
        // The setUp seeds 1 admin: adminUserId. actorId is another admin.
        // Now there are 2 active admins. Deactivate one → ok.
        // Try to deactivate the last one → error.

        // Deactivate the newly created admin to get back to 1 admin
        $this->pdo->exec("UPDATE users SET status = 'inactive' WHERE id = $actorId");

        // Now adminUserId is the only active admin. Any actor (say, regularUserId elevated) trying
        // to disable adminUserId should fail.
        // But the service checks company admin count, and the actor must be a different user.
        // Use regularUserId as actor (won't matter for the count check).
        $result = $this->service->execute($this->adminUserId, $this->regularUserId, $this->companyId);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('único administrador', $result['error']);
    }

    public function testReturnsErrorForUserNotInCompany(): void
    {
        $result = $this->service->execute(99999, $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('não encontrado', $result['error']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // S07 — Audit log
    // ──────────────────────────────────────────────────────────────────────

    public function testDisablingGeneratesAuditLogEntry(): void
    {
        $this->service->execute($this->regularUserId, $this->adminUserId, $this->companyId);

        $log = $this->pdo->query(
            "SELECT * FROM admin_audit_log WHERE action = 'user_disabled' AND target_user_id = {$this->regularUserId}"
        )->fetch(\PDO::FETCH_ASSOC);

        self::assertNotFalse($log);
    }

    public function testReactivatingGeneratesAuditLogEntry(): void
    {
        $this->pdo->exec("UPDATE users SET status = 'inactive' WHERE id = {$this->regularUserId}");

        $this->service->execute($this->regularUserId, $this->adminUserId, $this->companyId);

        $log = $this->pdo->query(
            "SELECT * FROM admin_audit_log WHERE action = 'user_activated' AND target_user_id = {$this->regularUserId}"
        )->fetch(\PDO::FETCH_ASSOC);

        self::assertNotFalse($log);
    }
}
