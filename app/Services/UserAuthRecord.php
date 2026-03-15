<?php

declare(strict_types=1);

namespace App\Services;

final class UserAuthRecord
{
    public function __construct(
        public readonly int $id,
        public readonly int $companyId,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $status
    ) {
    }
}
