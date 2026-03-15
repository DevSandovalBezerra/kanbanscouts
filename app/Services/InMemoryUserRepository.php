<?php

declare(strict_types=1);

namespace App\Services;

final class InMemoryUserRepository implements UserRepository
{
    /**
     * @param list<UserAuthRecord> $records
     */
    public function __construct(
        private readonly array $records = []
    ) {
    }

    public function findActiveByEmail(string $email): array
    {
        $email = strtolower(trim($email));
        $matches = [];

        foreach ($this->records as $record) {
            if (!$record instanceof UserAuthRecord) {
                continue;
            }

            if (strtolower($record->email) !== $email) {
                continue;
            }

            if ($record->status !== 'active') {
                continue;
            }

            $matches[] = $record;
        }

        return $matches;
    }
}
