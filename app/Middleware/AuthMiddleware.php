<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;

final class AuthMiddleware
{
    private $nowProvider;

    public function __construct(
        private readonly SessionStore $session,
        private readonly int $idleTimeoutSeconds,
        ?callable $nowProvider = null
    ) {
        $this->nowProvider = $nowProvider ?? static fn (): int => time();
    }

    public function __invoke(HttpRequest $request, callable $next): HttpResponse
    {
        $path = $request->path();
        if (!str_starts_with($path, '/api/')) {
            return $next($request);
        }

        if ($path === '/api/auth/login') {
            return $next($request);
        }

        $userId = $this->sessionInt('user_id');
        $companyId = $this->sessionInt('company_id');
        if ($userId === null || $companyId === null) {
            return $this->unauthorized('Não autenticado.');
        }

        $now = (int) ($this->nowProvider)();
        $last = $this->sessionInt('last_activity');
        if ($last !== null && $this->idleTimeoutSeconds > 0) {
            if (($now - $last) > $this->idleTimeoutSeconds) {
                $this->session->destroy();
                return $this->unauthorized('Sessão expirada.');
            }
        }

        $this->session->set('last_activity', $now);

        return $next($request);
    }

    private function sessionInt(string $key): ?int
    {
        $value = $this->session->get($key);
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

    private function unauthorized(string $message): HttpResponse
    {
        return HttpResponse::json([
            'error' => [
                'code' => 'unauthorized',
                'message' => $message,
                'details' => []
            ]
        ], 401);
    }
}

