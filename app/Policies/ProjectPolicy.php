<?php

declare(strict_types=1);

namespace App\Policies;

use App\Repositories\ProjectMemberRepository;
use App\Services\SessionStore;

/**
 * Checks the authenticated user's role in a project.
 *
 * Role hierarchy (highest → lowest):
 *   owner > manager > editor > viewer
 *
 * Usage:
 *   $policy = new ProjectPolicy($projectMemberRepo, $session);
 *   if (!$policy->canWrite($projectId)) {
 *       return HttpResponse::json(['error' => 'forbidden'], 403);
 *   }
 */
final class ProjectPolicy
{
    private const HIERARCHY = ['owner' => 4, 'manager' => 3, 'editor' => 2, 'viewer' => 1];

    public function __construct(
        private readonly ProjectMemberRepository $memberRepo,
        private readonly SessionStore $session
    ) {}

    /** Any member (viewer or above) can view. */
    public function canView(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'viewer');
    }

    /** editor or above can create/edit/move tasks. */
    public function canWrite(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'editor');
    }

    /** manager or above can create boards/columns. */
    public function canManageBoard(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'manager');
    }

    /** manager or above can invite new members (POST). */
    public function canInvite(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'manager');
    }

    /** Only owner can alter roles of existing members (PATCH). */
    public function canAlterRoles(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'owner');
    }

    /** Only owner can edit/delete the project or remove members. */
    public function canManageProject(int $projectId): bool
    {
        return $this->hasMinRole($projectId, 'owner');
    }

    private function hasMinRole(int $projectId, string $minRole): bool
    {
        $userId = (int) ($this->session->get('user_id') ?? 0);
        if ($userId === 0) return false;

        $role = $this->memberRepo->getRoleInProject($projectId, $userId);
        if ($role === null) return false;

        $userLevel = self::HIERARCHY[$role]    ?? 0;
        $minLevel  = self::HIERARCHY[$minRole] ?? 0;

        return $userLevel >= $minLevel;
    }
}
