<?php

declare(strict_types=1);

namespace Tests\Functional\Api;

use App\Controllers\AuthController;
use App\Helpers\HttpRequest;
use App\Services\ArraySessionStore;
use App\Services\AuthService;
use App\Services\InMemoryUserRepository;
use App\Services\UserAuthRecord;
use PHPUnit\Framework\TestCase;

final class AuthControllerTest extends TestCase
{
    public function testLoginReturns200ForValidCredentials(): void
    {
        $users = new InMemoryUserRepository([
            new UserAuthRecord(10, 3, 'a@a.com', password_hash('secret', PASSWORD_DEFAULT), 'active'),
        ]);
        $session = new ArraySessionStore();
        $auth = new AuthService($users, $session);
        $controller = new AuthController($auth, $session);

        $request = new HttpRequest('POST', '/api/auth/login', ['content-type' => 'application/json'], json_encode([
            'email' => 'a@a.com',
            'password' => 'secret'
        ]));

        $response = $controller->login($request);

        self::assertSame(200, $response->statusCode());
        $decoded = json_decode($response->body(), true);
        self::assertIsArray($decoded);
        self::assertTrue($decoded['ok'] ?? false);
        self::assertIsString($decoded['csrf_token'] ?? null);
        self::assertSame(10, $session->getInt('user_id'));
    }

    public function testLoginReturns422ForInvalidPayload(): void
    {
        $users = new InMemoryUserRepository([]);
        $session = new ArraySessionStore();
        $auth = new AuthService($users, $session);
        $controller = new AuthController($auth, $session);

        $request = new HttpRequest('POST', '/api/auth/login', ['content-type' => 'application/json'], json_encode([
            'email' => 'a@a.com'
        ]));

        $response = $controller->login($request);

        self::assertSame(422, $response->statusCode());
    }

    public function testMeReturns401WhenNotLoggedIn(): void
    {
        $users = new InMemoryUserRepository([]);
        $session = new ArraySessionStore();
        $auth = new AuthService($users, $session);
        $controller = new AuthController($auth, $session);

        $response = $controller->me(new HttpRequest('GET', '/api/auth/me'));

        self::assertSame(401, $response->statusCode());
    }

    public function testMeReturnsUserWhenLoggedIn(): void
    {
        $users = new InMemoryUserRepository([]);
        $session = new ArraySessionStore();
        $session->set('user_id', 10);
        $session->set('company_id', 3);
        $auth = new AuthService($users, $session);
        $controller = new AuthController($auth, $session);

        $response = $controller->me(new HttpRequest('GET', '/api/auth/me'));

        self::assertSame(200, $response->statusCode());
        self::assertSame('{"user":{"id":10,"company_id":3}}', $response->body());
    }
}
