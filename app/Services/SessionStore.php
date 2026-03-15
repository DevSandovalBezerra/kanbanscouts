<?php

declare(strict_types=1);

namespace App\Services;

interface SessionStore
{
    public function regenerate(): void;

    public function set(string $key, mixed $value): void;

    public function get(string $key): mixed;

    public function forget(string $key): void;

    public function destroy(): void;
}
