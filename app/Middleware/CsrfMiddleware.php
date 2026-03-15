<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;

final class CsrfMiddleware
{
    public function __construct(
        private readonly SessionStore $session,
        private readonly array $exemptPaths = []
    ) {
    }

    public function __invoke(HttpRequest $request, callable $next): HttpResponse
    {
        $path = $request->path();
        if (!str_starts_with($path, '/api/')) {
            return $next($request);
        }

        $token = $this->ensureToken();

        $method = $request->method();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        if (in_array($path, $this->exemptPaths, true)) {
            return $next($request);
        }

        $provided = $request->header('x-csrf-token');
        if (!is_string($provided) || $provided === '' || !hash_equals($token, $provided)) {
            return HttpResponse::json([
                'error' => [
                    'code' => 'csrf_invalid',
                    'message' => 'CSRF inválido.',
                    'details' => []
                ]
            ], 419);
        }

        return $next($request);
    }

    public function token(): string
    {
        return $this->ensureToken();
    }

    private function ensureToken(): string
    {
        $existing = $this->session->get('csrf_token');
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }
}

