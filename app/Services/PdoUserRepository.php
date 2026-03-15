<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class PdoUserRepository implements UserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function findActiveByEmail(string $email): array
    {
        $sql = <<<SQL
SELECT
  u.id,
  u.company_id,
  u.email,
  u.password,
  u.status,
  c.status AS company_status
FROM users u
JOIN companies c ON c.id = u.company_id
WHERE LOWER(u.email) = LOWER(:email)
  AND u.status = 'active'
  AND c.status = 'active'
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $records = [];
        foreach ($rows as $row) {
            $records[] = new UserAuthRecord(
                (int) $row['id'],
                (int) $row['company_id'],
                (string) $row['email'],
                (string) $row['password'],
                (string) $row['status']
            );
        }

        return $records;
    }
}

