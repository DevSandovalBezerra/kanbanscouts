<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectMemberDTO;

interface ProjectMemberRepository
{
    public function findByProjectId(int $projectId): array;
    public function findMembership(int $projectId, int $userId): ?ProjectMemberDTO;
    public function findById(int $id): ?ProjectMemberDTO;
    public function getRoleInProject(int $projectId, int $userId): ?string;
    public function add(int $projectId, int $userId, string $role, int $invitedBy): int;
    public function updateRole(int $membershipId, string $newRole): bool;
    public function remove(int $membershipId): bool;
}
