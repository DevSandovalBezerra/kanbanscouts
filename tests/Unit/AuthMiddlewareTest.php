<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Middleware\AuthMiddleware;
use App\Services\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class AuthMiddlewareTest extends TestCase
{
    public function testAllowsLoginWithoutSession(): void
    {
        $session = new ArraySessionStore();
        $mw = new AuthMiddleware($session, 1800, static fn (): int => 1000);

        $response = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response->statusCode());
    }

    public function testBlocksProtectedRouteWhenNotLoggedIn(): void
    {
        $session = new ArraySessionStore();
        $mw = new AuthMiddleware($session, 1800, static fn (): int => 1000);

        $response = $mw(new HttpRequest('GET', '/api/auth/me'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(401, $response->statusCode());
    }

    public function testExpiresSessionAfterIdleTimeout(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 10);
        $session->set('company_id', 3);
        $session->set('last_activity', 0);

        $mw = new AuthMiddleware($session, 10, static fn (): int => 100);

        $response = $mw(new HttpRequest('GET', '/api/auth/me'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(401, $response->statusCode());
        self::assertNull($session->get('user_id'));
    }

    public function testRefreshesLastActivityWhenLoggedIn(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 10);
        $session->set('company_id', 3);
        $session->set('last_activity', 90);

        $mw = new AuthMiddleware($session, 10, static fn (): int => 95);

        $response = $mw(new HttpRequest('GET', '/api/auth/me'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response->statusCode());
        self::assertSame(95, $session->getInt('last_activity'));
    }
}

