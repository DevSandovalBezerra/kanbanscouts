<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Policies\ProjectPolicy;
use App\Repositories\ProjectMemberRepository;
use App\Services\ArraySessionStore;
use PHPUnit\Framework\TestCase;

/**
 * Tests the role-hierarchy logic in ProjectPolicy.
 *
 * Hierarchy (highest → lowest): owner > manager > editor > viewer
 */
final class ProjectPolicyTest extends TestCase
{
    private const PROJECT_ID = 42;

    // ──────────────────────────────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────────────────────────────

    private function makePolicy(int $userId, ?string $role): ProjectPolicy
    {
        $session = new ArraySessionStore();
        if ($userId > 0) {
            $session->set('user_id', $userId);
        }

        $repo = $this->createMock(ProjectMemberRepository::class);
        $repo->method('getRoleInProject')->willReturn($role);

        return new ProjectPolicy($repo, $session);
    }

    // ──────────────────────────────────────────────────────────────────────
    // canView — viewer or above
    // ──────────────────────────────────────────────────────────────────────

    public function testViewerCanView(): void
    {
        self::assertTrue($this->makePolicy(1, 'viewer')->canView(self::PROJECT_ID));
    }

    public function testEditorCanView(): void
    {
        self::assertTrue($this->makePolicy(1, 'editor')->canView(self::PROJECT_ID));
    }

    public function testManagerCanView(): void
    {
        self::assertTrue($this->makePolicy(1, 'manager')->canView(self::PROJECT_ID));
    }

    public function testOwnerCanView(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canView(self::PROJECT_ID));
    }

    public function testNonMemberCannotView(): void
    {
        self::assertFalse($this->makePolicy(1, null)->canView(self::PROJECT_ID));
    }

    public function testUnauthenticatedUserCannotView(): void
    {
        self::assertFalse($this->makePolicy(0, 'viewer')->canView(self::PROJECT_ID));
    }

    // ──────────────────────────────────────────────────────────────────────
    // canWrite — editor or above
    // ──────────────────────────────────────────────────────────────────────

    public function testEditorCanWrite(): void
    {
        self::assertTrue($this->makePolicy(1, 'editor')->canWrite(self::PROJECT_ID));
    }

    public function testManagerCanWrite(): void
    {
        self::assertTrue($this->makePolicy(1, 'manager')->canWrite(self::PROJECT_ID));
    }

    public function testOwnerCanWrite(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canWrite(self::PROJECT_ID));
    }

    /** S08 — viewer receives 403 on write operations. */
    public function testViewerCannotWrite(): void
    {
        self::assertFalse($this->makePolicy(1, 'viewer')->canWrite(self::PROJECT_ID));
    }

    public function testNonMemberCannotWrite(): void
    {
        self::assertFalse($this->makePolicy(1, null)->canWrite(self::PROJECT_ID));
    }

    // ──────────────────────────────────────────────────────────────────────
    // canManageBoard — manager or above
    // ──────────────────────────────────────────────────────────────────────

    public function testManagerCanManageBoard(): void
    {
        self::assertTrue($this->makePolicy(1, 'manager')->canManageBoard(self::PROJECT_ID));
    }

    public function testOwnerCanManageBoard(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canManageBoard(self::PROJECT_ID));
    }

    public function testEditorCannotManageBoard(): void
    {
        self::assertFalse($this->makePolicy(1, 'editor')->canManageBoard(self::PROJECT_ID));
    }

    public function testViewerCannotManageBoard(): void
    {
        self::assertFalse($this->makePolicy(1, 'viewer')->canManageBoard(self::PROJECT_ID));
    }

    // ──────────────────────────────────────────────────────────────────────
    // canInvite — manager or above (POST to project-members)
    // ──────────────────────────────────────────────────────────────────────

    public function testManagerCanInvite(): void
    {
        self::assertTrue($this->makePolicy(1, 'manager')->canInvite(self::PROJECT_ID));
    }

    public function testOwnerCanInvite(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canInvite(self::PROJECT_ID));
    }

    public function testEditorCannotInvite(): void
    {
        self::assertFalse($this->makePolicy(1, 'editor')->canInvite(self::PROJECT_ID));
    }

    // ──────────────────────────────────────────────────────────────────────
    // canAlterRoles — owner only (S12)
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCanAlterRoles(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canAlterRoles(self::PROJECT_ID));
    }

    /** S12 — manager cannot alter member roles (PATCH). */
    public function testManagerCannotAlterRoles(): void
    {
        self::assertFalse($this->makePolicy(1, 'manager')->canAlterRoles(self::PROJECT_ID));
    }

    public function testEditorCannotAlterRoles(): void
    {
        self::assertFalse($this->makePolicy(1, 'editor')->canAlterRoles(self::PROJECT_ID));
    }

    // ──────────────────────────────────────────────────────────────────────
    // canManageProject — owner only
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCanManageProject(): void
    {
        self::assertTrue($this->makePolicy(1, 'owner')->canManageProject(self::PROJECT_ID));
    }

    public function testManagerCannotManageProject(): void
    {
        self::assertFalse($this->makePolicy(1, 'manager')->canManageProject(self::PROJECT_ID));
    }
}
