<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Middleware\AdminMiddleware;
use App\Services\ArraySessionStore;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class AdminMiddlewareTest extends TestCase
{
    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /** Builds an AdminMiddleware whose PDO::prepare()->fetch() returns $dbRow. */
    private function makeMiddleware(ArraySessionStore $session, mixed $dbRow): AdminMiddleware
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($dbRow);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        return new AdminMiddleware($session, $pdo);
    }

    private function next(): callable
    {
        return static fn(HttpRequest $r): HttpResponse => HttpResponse::text('ok', 200);
    }

    // ──────────────────────────────────────────────────────────────────────
    // 403 scenarios
    // ──────────────────────────────────────────────────────────────────────

    public function testBlocksWhenNoSessionExists(): void
    {
        $session = new ArraySessionStore();
        $mw      = $this->makeMiddleware($session, null);

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(403, $response->statusCode());
    }

    public function testBlocksWhenUserIdIsZero(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 0);
        $mw = $this->makeMiddleware($session, ['is_admin' => 1]);

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(403, $response->statusCode());
    }

    public function testBlocksWhenUserNotFoundInDatabase(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 99);
        $mw = $this->makeMiddleware($session, false); // DB returns no row

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(403, $response->statusCode());
    }

    public function testBlocksWhenUserIsNotAdmin(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 5);
        $mw = $this->makeMiddleware($session, ['is_admin' => 0]);

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(403, $response->statusCode());
    }

    /**
     * S03 — Even if the session cache says the user is admin, the DB must be
     * checked. A revoked admin must be blocked immediately on the next request.
     */
    public function testBlocksWhenSessionSaysAdminButDbSaysNo(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 5);
        $session->set('user_is_admin', true); // stale cache

        $mw = $this->makeMiddleware($session, ['is_admin' => 0]); // DB disagrees

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(403, $response->statusCode());
    }

    // ──────────────────────────────────────────────────────────────────────
    // 200 scenario
    // ──────────────────────────────────────────────────────────────────────

    public function testAllowsActiveAdminUser(): void
    {
        $session = new ArraySessionStore();
        $session->set('user_id', 1);
        $mw = $this->makeMiddleware($session, ['is_admin' => 1]);

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());

        self::assertSame(200, $response->statusCode());
    }

    // ──────────────────────────────────────────────────────────────────────
    // Response body
    // ──────────────────────────────────────────────────────────────────────

    public function testForbiddenResponseHasCorrectErrorCode(): void
    {
        $session = new ArraySessionStore();
        $mw      = $this->makeMiddleware($session, null);

        $response = $mw(new HttpRequest('GET', '/api/admin/users'), $this->next());
        $body     = json_decode($response->body(), true);

        self::assertSame('forbidden', $body['error']['code']);
    }
}
