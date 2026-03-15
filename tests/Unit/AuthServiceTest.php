<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AuthService;
use App\Services\AuthServiceResult;
use App\Services\InMemoryUserRepository;
use App\Services\UserAuthRecord;
use App\Services\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testLoginSucceedsAndWritesSession(): void
    {
        $users = new InMemoryUserRepository([
            new UserAuthRecord(10, 3, 'a@a.com', password_hash('secret', PASSWORD_DEFAULT), 'active'),
        ]);
        $session = new ArraySessionStore();
        $service = new AuthService($users, $session);

        $result = $service->login('a@a.com', 'secret');

        self::assertSame(AuthServiceResult::OK, $result->code);
        self::assertSame(10, $session->getInt('user_id'));
        self::assertSame(3, $session->getInt('company_id'));
    }

    public function testLoginFailsForInvalidCredentials(): void
    {
        $users = new InMemoryUserRepository([
            new UserAuthRecord(10, 3, 'a@a.com', password_hash('secret', PASSWORD_DEFAULT), 'active'),
        ]);
        $session = new ArraySessionStore();
        $service = new AuthService($users, $session);

        $result = $service->login('a@a.com', 'wrong');

        self::assertSame(AuthServiceResult::INVALID_CREDENTIALS, $result->code);
        self::assertNull($session->get('user_id'));
    }

    public function testLoginFailsWhenEmailIsAmbiguousAcrossCompanies(): void
    {
        $users = new InMemoryUserRepository([
            new UserAuthRecord(10, 3, 'a@a.com', password_hash('secret', PASSWORD_DEFAULT), 'active'),
            new UserAuthRecord(11, 4, 'a@a.com', password_hash('secret', PASSWORD_DEFAULT), 'active'),
        ]);
        $session = new ArraySessionStore();
        $service = new AuthService($users, $session);

        $result = $service->login('a@a.com', 'secret');

        self::assertSame(AuthServiceResult::AMBIGUOUS_IDENTITY, $result->code);
        self::assertNull($session->get('user_id'));
    }
}
