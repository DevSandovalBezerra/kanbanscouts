<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Helpers\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Repositories\PdoConnectionFactory;
use App\Services\AuthService;
use App\Services\PdoUserRepository;
use App\Services\PhpSessionStore;

return static function (Router $router): void {
    $dbConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
    $sessionConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
    $rateLimitConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'rate_limit.php';

    $pdo = PdoConnectionFactory::fromConfig($dbConfig);
    $users = new PdoUserRepository($pdo);
    $session = new PhpSessionStore();
    $auth = new AuthService($users, $session);
    $controller = new AuthController($auth, $session);

    $router->addMiddleware(new AuthMiddleware($session, (int) ($sessionConfig['idle_timeout_seconds'] ?? 1800)));
    $router->addMiddleware(new RateLimitMiddleware(
        $session,
        (int) ($rateLimitConfig['login']['max_attempts'] ?? 5),
        (int) ($rateLimitConfig['login']['window_seconds'] ?? 60)
    ));
    $router->addMiddleware(new CsrfMiddleware($session, ['/api/auth/login']));

    $handler = static function (callable $action) use ($controller): callable {
        return static function (HttpRequest $request) use ($action, $controller): HttpResponse {
            return $action($controller, $request);
        };
    };

    $router->add('POST', '/api/auth/login', $handler(static fn (AuthController $c, HttpRequest $r): HttpResponse => $c->login($r)));
    $router->add('POST', '/api/auth/logout', $handler(static fn (AuthController $c, HttpRequest $r): HttpResponse => $c->logout($r)));
    $router->add('GET', '/api/auth/me', $handler(static fn (AuthController $c, HttpRequest $r): HttpResponse => $c->me($r)));
};
