<?php

declare(strict_types=1);

namespace App\Services;

final class PhpSessionStore implements SessionStore
{
    private bool $started = false;

    public function regenerate(): void
    {
        $this->ensureStarted();
        session_regenerate_id(true);
    }

    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? null;
    }

    public function forget(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
        session_destroy();
        $this->started = false;
    }

    private function ensureStarted(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->started = true;
    }
}

