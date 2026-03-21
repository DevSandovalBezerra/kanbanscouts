<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\PdoProjectMemberRepository;
use App\Services\ProjectMember\AddMemberService;

final class AddMemberServiceTest extends AdminIntegrationTestCase
{
    private AddMemberService $service;
    private PdoProjectMemberRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = new PdoProjectMemberRepository($this->pdo);
        $this->service = new AddMemberService($this->repo, $this->pdo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Happy path
    // ──────────────────────────────────────────────────────────────────────

    public function testAddsEditorMemberSuccessfully(): void
    {
        $result = $this->service->execute(
            projectId:         $this->projectId,
            userId:            $this->regularUserId,
            role:              'editor',
            invitedBy:         $this->adminUserId,
            sessionCompanyId:  $this->companyId,
        );

        self::assertTrue($result['ok']);
        self::assertGreaterThan(0, $result['id']);
    }

    public function testAddsViewerMemberSuccessfully(): void
    {
        $result = $this->service->execute(
            $this->projectId, $this->regularUserId, 'viewer', $this->adminUserId, $this->companyId
        );

        self::assertTrue($result['ok']);

        $membership = $this->repo->findMembership($this->projectId, $this->regularUserId);
        self::assertNotNull($membership);
        self::assertSame('viewer', $membership->roleInProject);
    }

    public function testMembershipIsPersistedInDatabase(): void
    {
        $this->service->execute($this->projectId, $this->regularUserId, 'editor', $this->adminUserId, $this->companyId);

        $membership = $this->repo->findMembership($this->projectId, $this->regularUserId);
        self::assertNotNull($membership);
        self::assertSame($this->projectId, $membership->projectId);
        self::assertSame($this->regularUserId, $membership->userId);
        self::assertSame($this->adminUserId, $membership->invitedBy);
    }

    // ──────────────────────────────────────────────────────────────────────
    // S09 — Cross-company isolation
    // ──────────────────────────────────────────────────────────────────────

    public function testRejectsUserFromAnotherCompany(): void
    {
        // Create a user in a different company
        $this->pdo->exec("INSERT INTO companies (name, status, created_at, updated_at) VALUES ('Other Corp', 'active', NOW(), NOW())");
        $otherCompanyId = (int) $this->pdo->lastInsertId();

        $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, 'Outsider', 'out@other.com', 'x', 'active', 0, NOW(), NOW())"
        )->execute([$otherCompanyId]);
        $outsiderId = (int) $this->pdo->lastInsertId();

        $result = $this->service->execute(
            $this->projectId, $outsiderId, 'editor', $this->adminUserId, $this->companyId
        );

        self::assertFalse($result['ok']);
        self::assertStringContainsString('outra empresa', $result['error']);
    }

    public function testRejectsInactiveUser(): void
    {
        $this->pdo->exec("UPDATE users SET status = 'inactive' WHERE id = {$this->regularUserId}");

        $result = $this->service->execute(
            $this->projectId, $this->regularUserId, 'editor', $this->adminUserId, $this->companyId
        );

        self::assertFalse($result['ok']);
        self::assertStringContainsString('inativo', $result['error']);
    }

    public function testRejectsNonExistentUser(): void
    {
        $result = $this->service->execute(
            $this->projectId, 99999, 'editor', $this->adminUserId, $this->companyId
        );

        self::assertFalse($result['ok']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Duplicate membership guard
    // ──────────────────────────────────────────────────────────────────────

    public function testRejectsDuplicateMembership(): void
    {
        // Add once
        $this->service->execute($this->projectId, $this->regularUserId, 'editor', $this->adminUserId, $this->companyId);

        // Add again
        $result = $this->service->execute($this->projectId, $this->regularUserId, 'viewer', $this->adminUserId, $this->companyId);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('já é membro', $result['error']);
    }
}
