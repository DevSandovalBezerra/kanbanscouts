<?php

declare(strict_types=1);

namespace App\Services\ProjectMember;

use App\Repositories\ProjectMemberRepository;

final class RemoveMemberService
{
    public function __construct(private readonly ProjectMemberRepository $memberRepo) {}

    /**
     * Removes a member from a project.
     * Allowed by owner, or by the member removing themselves.
     */
    public function execute(int $membershipId, int $actorId): array
    {
        $member = $this->memberRepo->findById($membershipId);
        if (!$member) {
            return ['ok' => false, 'error' => 'Membro não encontrado.'];
        }

        // Owner cannot remove themselves if they are the only owner
        if ($member->userId === $actorId && $member->roleInProject === 'owner') {
            return ['ok' => false, 'error' => 'O owner não pode sair do projeto. Transfira a ownership primeiro.'];
        }

        $ok = $this->memberRepo->remove($membershipId);

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => 'Falha ao remover membro.'];
    }
}
