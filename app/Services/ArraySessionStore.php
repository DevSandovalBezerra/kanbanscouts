<?php

declare(strict_types=1);

namespace App\Services;

final class ArraySessionStore implements SessionStore
{
    private array $data = [];
    private int $regenerateCount = 0;

    public function regenerate(): void
    {
        $this->regenerateCount++;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function getInt(string $key): ?int
    {
        $value = $this->get($key);
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }

    public function destroy(): void
    {
        $this->data = [];
    }
}
