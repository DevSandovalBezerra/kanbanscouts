<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectMemberDTO;
use PDO;

final class PdoProjectMemberRepository implements ProjectMemberRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /** Returns all members of a project with user info joined. */
    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT pm.id, pm.project_id, pm.user_id, pm.role_in_project,
                    pm.invited_by, pm.accepted_at,
                    u.name AS user_name, u.email AS user_email, u.status AS user_status
             FROM project_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE pm.project_id = ?
             ORDER BY pm.role_in_project ASC, u.name ASC"
        );
        $stmt->execute([$projectId]);
        return array_map(
            fn(array $row) => ProjectMemberDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /** Returns the membership row for a specific user in a project, or null. */
    public function findMembership(int $projectId, int $userId): ?ProjectMemberDTO
    {
        $stmt = $this->pdo->prepare(
            "SELECT pm.*, u.name AS user_name, u.email AS user_email, u.status AS user_status
             FROM project_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE pm.project_id = ? AND pm.user_id = ?
             LIMIT 1"
        );
        $stmt->execute([$projectId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ProjectMemberDTO::fromArray($row) : null;
    }

    /** Returns membership by primary key id. */
    public function findById(int $id): ?ProjectMemberDTO
    {
        $stmt = $this->pdo->prepare(
            "SELECT pm.*, u.name AS user_name, u.email AS user_email, u.status AS user_status
             FROM project_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE pm.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ProjectMemberDTO::fromArray($row) : null;
    }

    /** Returns the role of a user in a project, or null if not a member. */
    public function getRoleInProject(int $projectId, int $userId): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT role_in_project FROM project_members WHERE project_id = ? AND user_id = ? LIMIT 1"
        );
        $stmt->execute([$projectId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string) $row['role_in_project'] : null;
    }

    public function add(int $projectId, int $userId, string $role, int $invitedBy): int
    {
        $now  = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "INSERT INTO project_members (project_id, user_id, role_in_project, invited_by, accepted_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$projectId, $userId, $role, $invitedBy, $now, $now, $now]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateRole(int $membershipId, string $newRole): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE project_members SET role_in_project = ?, updated_at = ? WHERE id = ?"
        );
        return $stmt->execute([$newRole, date('Y-m-d H:i:s'), $membershipId]);
    }

    public function remove(int $membershipId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM project_members WHERE id = ?");
        return $stmt->execute([$membershipId]);
    }
}
