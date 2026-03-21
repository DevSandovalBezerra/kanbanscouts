<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\PdoProjectMemberRepository;

final class PdoProjectMemberRepositoryTest extends AdminIntegrationTestCase
{
    private PdoProjectMemberRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PdoProjectMemberRepository($this->pdo);
    }

    // ──────────────────────────────────────────────────────────────────────
    // add + findMembership
    // ──────────────────────────────────────────────────────────────────────

    public function testAddAndFindMembership(): void
    {
        $id = $this->repo->add($this->projectId, $this->regularUserId, 'editor', $this->adminUserId);

        self::assertGreaterThan(0, $id);

        $membership = $this->repo->findMembership($this->projectId, $this->regularUserId);
        self::assertNotNull($membership);
        self::assertSame($this->projectId, $membership->projectId);
        self::assertSame($this->regularUserId, $membership->userId);
        self::assertSame('editor', $membership->roleInProject);
        self::assertSame($this->adminUserId, $membership->invitedBy);
    }

    public function testFindMembershipReturnsNullForNonMember(): void
    {
        $result = $this->repo->findMembership($this->projectId, 99999);
        self::assertNull($result);
    }

    // ──────────────────────────────────────────────────────────────────────
    // findById
    // ──────────────────────────────────────────────────────────────────────

    public function testFindById(): void
    {
        $id = $this->repo->add($this->projectId, $this->regularUserId, 'viewer', $this->adminUserId);

        $found = $this->repo->findById($id);
        self::assertNotNull($found);
        self::assertSame($id, $found->id);
        self::assertSame('viewer', $found->roleInProject);
    }

    public function testFindByIdReturnsNullForMissingId(): void
    {
        self::assertNull($this->repo->findById(99999));
    }

    // ──────────────────────────────────────────────────────────────────────
    // getRoleInProject
    // ──────────────────────────────────────────────────────────────────────

    public function testGetRoleInProjectReturnsRole(): void
    {
        $this->repo->add($this->projectId, $this->regularUserId, 'manager', $this->adminUserId);

        $role = $this->repo->getRoleInProject($this->projectId, $this->regularUserId);
        self::assertSame('manager', $role);
    }

    public function testGetRoleInProjectReturnsNullForNonMember(): void
    {
        $role = $this->repo->getRoleInProject($this->projectId, 99999);
        self::assertNull($role);
    }

    // ──────────────────────────────────────────────────────────────────────
    // findByProjectId
    // ──────────────────────────────────────────────────────────────────────

    public function testFindByProjectIdReturnsAllMembers(): void
    {
        $this->repo->add($this->projectId, $this->regularUserId, 'editor', $this->adminUserId);
        $this->repo->add($this->projectId, $this->adminUserId, 'owner', $this->adminUserId);

        $members = $this->repo->findByProjectId($this->projectId);
        self::assertCount(2, $members);
    }

    public function testFindByProjectIdIncludesUserInfo(): void
    {
        $this->repo->add($this->projectId, $this->regularUserId, 'editor', $this->adminUserId);

        $members = $this->repo->findByProjectId($this->projectId);
        $member  = $members[0];

        self::assertNotNull($member->userName);
        self::assertNotNull($member->userEmail);
    }

    // ──────────────────────────────────────────────────────────────────────
    // updateRole
    // ──────────────────────────────────────────────────────────────────────

    public function testUpdateRole(): void
    {
        $id = $this->repo->add($this->projectId, $this->regularUserId, 'editor', $this->adminUserId);

        $ok = $this->repo->updateRole($id, 'viewer');
        self::assertTrue($ok);

        $membership = $this->repo->findById($id);
        self::assertSame('viewer', $membership->roleInProject);
    }

    // ──────────────────────────────────────────────────────────────────────
    // remove
    // ──────────────────────────────────────────────────────────────────────

    public function testRemoveMembership(): void
    {
        $id = $this->repo->add($this->projectId, $this->regularUserId, 'editor', $this->adminUserId);

        $ok = $this->repo->remove($id);
        self::assertTrue($ok);

        self::assertNull($this->repo->findById($id));
        self::assertNull($this->repo->findMembership($this->projectId, $this->regularUserId));
    }
}
