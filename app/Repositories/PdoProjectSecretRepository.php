<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProjectSecretDTO;
use PDO;

final class PdoProjectSecretRepository implements ProjectSecretRepository
{
    private ?bool $supportsMetaColumns = null;

    public function __construct(private readonly PDO $pdo) {}

    private function hasMetaColumns(): bool
    {
        if ($this->supportsMetaColumns !== null) {
            return $this->supportsMetaColumns;
        }

        try {
            $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $stmt = $this->pdo->query("PRAGMA table_info('project_secrets')");
                $cols = array_map(
                    static fn (array $row): string => (string) ($row['name'] ?? ''),
                    $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : []
                );
                $hasTitle = in_array('title', $cols, true);
                $hasDesc  = in_array('description', $cols, true);
                $this->supportsMetaColumns = $hasTitle && $hasDesc;
                return $this->supportsMetaColumns;
            }

            $stmt = $this->pdo->prepare(
                "SELECT COLUMN_NAME
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'project_secrets'
                   AND COLUMN_NAME IN ('title', 'description')"
            );
            $stmt->execute();
            $cols = array_map(
                static fn (array $row): string => (string) ($row['COLUMN_NAME'] ?? ''),
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );

            $hasTitle = in_array('title', $cols, true);
            $hasDesc  = in_array('description', $cols, true);
            $this->supportsMetaColumns = $hasTitle && $hasDesc;
            return $this->supportsMetaColumns;
        } catch (\Throwable) {
            $this->supportsMetaColumns = false;
            return false;
        }
    }

    public function findByProjectId(int $projectId): array
    {
        $stmt = $this->pdo->prepare($this->hasMetaColumns()
            ? 'SELECT id, project_id, secret_key, title, description, secret_value_enc, created_by, created_at, updated_at
               FROM project_secrets
               WHERE project_id = ?
               ORDER BY secret_key ASC'
            : 'SELECT id, project_id, secret_key, secret_value_enc, created_by, created_at, updated_at
               FROM project_secrets
               WHERE project_id = ?
               ORDER BY secret_key ASC'
        );
        $stmt->execute([$projectId]);

        return array_map(
            fn (array $row) => ProjectSecretDTO::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?ProjectSecretDTO
    {
        $stmt = $this->pdo->prepare($this->hasMetaColumns()
            ? 'SELECT id, project_id, secret_key, title, description, secret_value_enc, created_by, created_at, updated_at
               FROM project_secrets
               WHERE id = ?
               LIMIT 1'
            : 'SELECT id, project_id, secret_key, secret_value_enc, created_by, created_at, updated_at
               FROM project_secrets
               WHERE id = ?
               LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ProjectSecretDTO::fromArray($row) : null;
    }

    public function create(ProjectSecretDTO $secret): int
    {
        $now = date('Y-m-d H:i:s');
        if ($this->hasMetaColumns()) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO project_secrets (project_id, secret_key, title, description, secret_value_enc, created_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $secret->projectId,
                $secret->secretKey,
                $secret->title,
                $secret->description,
                $secret->secretValueEnc,
                $secret->createdBy,
                $now,
                $now,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO project_secrets (project_id, secret_key, secret_value_enc, created_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $secret->projectId,
                $secret->secretKey,
                $secret->secretValueEnc,
                $secret->createdBy,
                $now,
                $now,
            ]);
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function update(ProjectSecretDTO $secret): bool
    {
        if ($this->hasMetaColumns()) {
            $stmt = $this->pdo->prepare(
                'UPDATE project_secrets
                 SET secret_key = ?, title = ?, description = ?, secret_value_enc = ?, updated_at = ?
                 WHERE id = ?'
            );

            return $stmt->execute([
                $secret->secretKey,
                $secret->title,
                $secret->description,
                $secret->secretValueEnc,
                date('Y-m-d H:i:s'),
                $secret->id,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE project_secrets
             SET secret_key = ?, secret_value_enc = ?, updated_at = ?
             WHERE id = ?'
        );

        return $stmt->execute([
            $secret->secretKey,
            $secret->secretValueEnc,
            date('Y-m-d H:i:s'),
            $secret->id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM project_secrets WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
