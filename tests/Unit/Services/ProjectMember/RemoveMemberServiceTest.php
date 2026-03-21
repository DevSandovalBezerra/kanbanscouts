<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ProjectMember;

use App\DTO\ProjectMemberDTO;
use App\Repositories\ProjectMemberRepository;
use App\Services\ProjectMember\RemoveMemberService;
use PHPUnit\Framework\TestCase;

final class RemoveMemberServiceTest extends TestCase
{
    private ProjectMemberRepository $repo;
    private RemoveMemberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = $this->createMock(ProjectMemberRepository::class);
        $this->service = new RemoveMemberService($this->repo);
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

        $result = $this->service->execute(99, actorId: 1);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('não encontrado', $result['error']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Owner self-removal guard
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCannotRemoveThemself(): void
    {
        $member = $this->makeMember(1, userId: 5, role: 'owner');
        $this->repo->method('findById')->willReturn($member);

        $result = $this->service->execute(membershipId: 1, actorId: 5);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('owner', strtolower($result['error']));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Successful removal
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerCanRemoveAnotherMember(): void
    {
        $member = $this->makeMember(2, userId: 10, role: 'editor');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('remove')->willReturn(true);

        // actorId = 5 (owner), userId of member = 10 (another person)
        $result = $this->service->execute(2, actorId: 5);

        self::assertTrue($result['ok']);
    }

    public function testMemberCanRemoveThemself(): void
    {
        // An editor removing themselves (self-exit from project)
        $member = $this->makeMember(3, userId: 7, role: 'editor');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('remove')->willReturn(true);

        $result = $this->service->execute(3, actorId: 7);

        self::assertTrue($result['ok']);
    }

    public function testViewerCanRemoveThemself(): void
    {
        $member = $this->makeMember(4, userId: 8, role: 'viewer');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('remove')->willReturn(true);

        $result = $this->service->execute(4, actorId: 8);

        self::assertTrue($result['ok']);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Repository failure
    // ──────────────────────────────────────────────────────────────────────

    public function testReturnsErrorWhenRepositoryFails(): void
    {
        $member = $this->makeMember(5, userId: 10, role: 'editor');
        $this->repo->method('findById')->willReturn($member);
        $this->repo->method('remove')->willReturn(false);

        $result = $this->service->execute(5, actorId: 1);

        self::assertFalse($result['ok']);
    }
}
