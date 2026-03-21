<?php

declare(strict_types=1);

namespace App\Services\ProjectMember;

use App\Repositories\ProjectMemberRepository;

final class UpdateMemberRoleService
{
    public function __construct(private readonly ProjectMemberRepository $memberRepo) {}

    /**
     * Changes the role of a project member.
     * Only the project owner may call this (enforced by ProjectPolicy in the controller).
     */
    public function execute(int $membershipId, string $newRole, int $actorId): array
    {
        $member = $this->memberRepo->findById($membershipId);
        if (!$member) {
            return ['ok' => false, 'error' => 'Membro não encontrado.'];
        }

        // Owner cannot demote themselves (would leave project without owner)
        if ($member->userId === $actorId && $member->roleInProject === 'owner' && $newRole !== 'owner') {
            return ['ok' => false, 'error' => 'O owner não pode rebaixar a si mesmo. Transfira a ownership primeiro.'];
        }

        $ok = $this->memberRepo->updateRole($membershipId, $newRole);

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'Falha ao atualizar papel.'];
    }
}
