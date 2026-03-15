<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;

final class RateLimitMiddleware
{
    private $nowProvider;

    public function __construct(
        private readonly SessionStore $session,
        private readonly int $maxAttempts,
        private readonly int $windowSeconds,
        ?callable $nowProvider = null
    ) {
        $this->nowProvider = $nowProvider ?? static fn (): int => time();
    }

    public function __invoke(HttpRequest $request, callable $next): HttpResponse
    {
        if ($request->method() !== 'POST' || $request->path() !== '/api/auth/login') {
            return $next($request);
        }

        $now = (int) ($this->nowProvider)();
        $state = $this->session->get('rate_limit_login');
        if (!is_array($state)) {
            $state = ['count' => 0, 'reset_at' => $now + $this->windowSeconds];
        }

        $resetAt = isset($state['reset_at']) && is_int($state['reset_at']) ? $state['reset_at'] : ($now + $this->windowSeconds);
        $count = isset($state['count']) && is_int($state['count']) ? $state['count'] : 0;

        if ($now >= $resetAt) {
            $count = 0;
            $resetAt = $now + $this->windowSeconds;
        }

        $count++;
        $state = ['count' => $count, 'reset_at' => $resetAt];
        $this->session->set('rate_limit_login', $state);

        if ($this->maxAttempts > 0 && $count > $this->maxAttempts) {
            $retryAfter = max(0, $resetAt - $now);
            return HttpResponse::json([
                'error' => [
                    'code' => 'rate_limited',
                    'message' => 'Muitas tentativas. Tente novamente mais tarde.',
                    'details' => ['retry_after' => $retryAfter]
                ]
            ], 429, ['retry-after' => (string) $retryAfter]);
        }

        return $next($request);
    }
}

