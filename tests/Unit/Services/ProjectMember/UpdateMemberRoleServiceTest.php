<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ProjectMember;

use App\DTO\ProjectMemberDTO;
use App\Repositories\ProjectMemberRepository;
use App\Services\ProjectMember\UpdateMemberRoleService;
use PHPUnit\Framework\TestCase;

final class UpdateMemberRoleServiceTest extends TestCase
{
    private ProjectMemberRepository $repo;
    private UpdateMemberRoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = $this->createMock(ProjectMemberRepository::class);
        $this->service = new UpdateMemberRoleService($this->repo);
    }

    private function makeMember(int $membershipId, int $userId, string $role): ProjectMemberDTO
    {
        return new ProjectMemberDTO(
            id:            $membershipId,
            projectId:     10,
            userId:        $userId,
            roleInProject: $role,
            invitedBy:     null,
            acceptedAt:    null,
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // Not found
    // ──────────────────────────────────────────────────────────────────────

    public function testReturnsErrorWhenMembershipNotFound(): void
    {
        $this->repo->method('findById')->willReturn(null);

        $result = $this->service->execute(99, 'editor', actorId: 1);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('não encontrado', $result['error']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Owner self-demotion guard
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCannotDemoteThemself(): void
    {
        $member = $this->makeMember(1, 5, 'owner');
        $this->repo->method('findById')->willReturn($member);

        $result = $this->service->execute(membershipId: 1, newRole: 'editor', actorId: 5);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('owner', strtolower($result['error']));
    }

    public function testOwnerCannotDemoteThemselfToManager(): void
    {
        $member = $this->makeMember(1, 7, 'owner');
        $this->repo->method('findById')->willReturn($member);

        $result = $this->service->execute(1, 'manager', 7);

        self::assertFalse($result['ok']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Successful role change
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCanChangeAnotherMemberRole(): void
    {
        $member = $this->makeMember(membershipId: 2, userId: 10, role: 'editor');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('updateRole')->willReturn(true);

        // actorId = 5 (the owner), userId of member = 10 (different person)
        $result = $this->service->execute(2, 'viewer', actorId: 5);

        self::assertTrue($result['ok']);
    }

    public function testOwnerCanPromoteEditorToManager(): void
    {
        $member = $this->makeMember(3, userId: 20, role: 'editor');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('updateRole')->willReturn(true);

        $result = $this->service->execute(3, 'manager', actorId: 1);

        self::assertTrue($result['ok']);
    }

    public function testOwnerCanKeepTheirOwnOwnerRole(): void
    {
        // Keeping owner → owner is not a demotion, should be allowed
        $member = $this->makeMember(4, userId: 5, role: 'owner');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('updateRole')->willReturn(true);

        $result = $this->service->execute(4, 'owner', actorId: 5);

        self::assertTrue($result['ok']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Repository failure
    // ──────────────────────────────────────────────────────────────────────

    public function testReturnsErrorWhenRepositoryFails(): void
    {
        $member = $this->makeMember(5, userId: 10, role: 'viewer');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('updateRole')->willReturn(false);

        $result = $this->service->execute(5, 'editor', actorId: 1);

        self::assertFalse($result['ok']);
    }
}
