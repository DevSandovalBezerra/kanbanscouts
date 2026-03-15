<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Middleware\CsrfMiddleware;
use App\Services\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class CsrfMiddlewareTest extends TestCase
{
    public function testAllowsGetWithoutProvidedToken(): void
    {
        $session = new ArraySessionStore();
        $mw = new CsrfMiddleware($session, ['/api/auth/login']);

        $response = $mw(new HttpRequest('GET', '/api/auth/me'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response->statusCode());
        self::assertIsString($session->get('csrf_token'));
    }

    public function testBlocksMutatingRequestWhenTokenMissing(): void
    {
        $session = new ArraySessionStore();
        $session->set('csrf_token', 't');
        $mw = new CsrfMiddleware($session, ['/api/auth/login']);

        $response = $mw(new HttpRequest('POST', '/api/auth/logout'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(419, $response->statusCode());
    }

    public function testAllowsMutatingRequestWhenTokenMatches(): void
    {
        $session = new ArraySessionStore();
        $session->set('csrf_token', 't');
        $mw = new CsrfMiddleware($session, ['/api/auth/login']);

        $response = $mw(new HttpRequest('POST', '/api/auth/logout', ['x-csrf-token' => 't']), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response->statusCode());
    }
}

