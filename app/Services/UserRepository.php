<?php

declare(strict_types=1);

namespace App\Services;

interface UserRepository
{
    /**
     * @return list<UserAuthRecord>
     */
    public function findActiveByEmail(string $email): array;
}
