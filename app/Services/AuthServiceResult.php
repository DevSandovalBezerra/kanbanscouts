<?php

declare(strict_types=1);

namespace App\Services;

final class AuthServiceResult
{
    public const OK = 'ok';
    public const INVALID_CREDENTIALS = 'invalid_credentials';
    public const AMBIGUOUS_IDENTITY = 'ambiguous_identity';

    public function __construct(
        public readonly string $code
    ) {
    }
}
