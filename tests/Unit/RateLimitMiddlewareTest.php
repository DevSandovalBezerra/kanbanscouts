<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Middleware\RateLimitMiddleware;
use App\Services\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class RateLimitMiddlewareTest extends TestCase
{
    public function testAllowsUpToMaxAttemptsWithinWindow(): void
    {
        $session = new ArraySessionStore();
        $mw = new RateLimitMiddleware($session, 2, 60, static fn (): int => 1000);

        $response1 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));
        $response2 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response1->statusCode());
        self::assertSame(200, $response2->statusCode());
    }

    public function testBlocksWhenExceedingMaxAttemptsWithinWindow(): void
    {
        $session = new ArraySessionStore();
        $mw = new RateLimitMiddleware($session, 2, 60, static fn (): int => 1000);

        $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));
        $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));
        $response3 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(429, $response3->statusCode());
        self::assertNotNull($response3->header('retry-after'));
    }

    public function testResetsAfterWindow(): void
    {
        $session = new ArraySessionStore();
        $now = 1000;
        $mw = new RateLimitMiddleware($session, 1, 10, static function () use (&$now): int {
            return $now;
        });

        $response1 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));
        $response2 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));
        $now = 1011;
        $response3 = $mw(new HttpRequest('POST', '/api/auth/login'), static fn (HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200));

        self::assertSame(200, $response1->statusCode());
        self::assertSame(429, $response2->statusCode());
        self::assertSame(200, $response3->statusCode());
    }
}
