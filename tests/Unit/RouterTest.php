<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Helpers\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testDispatchReturnsHandlerResponseWhenRouteMatches(): void
    {
        $router = new Router();
        $router->add('GET', '/health', static function (HttpRequest $request): HttpResponse {
            return HttpResponse::json(['ok' => true], 200);
        });

        $response = $router->dispatch(new HttpRequest('GET', '/health'));

        self::assertSame(200, $response->statusCode());
        self::assertSame('application/json; charset=utf-8', $response->header('content-type'));
        self::assertSame('{"ok":true}', $response->body());
    }

    public function testDispatchReturns404WhenNoRouteMatches(): void
    {
        $router = new Router();

        $response = $router->dispatch(new HttpRequest('GET', '/missing'));

        self::assertSame(404, $response->statusCode());
    }

    public function testDispatchAppliesMiddlewareInOrder(): void
    {
        $router = new Router();
        $router->addMiddleware(static function (HttpRequest $request, callable $next): HttpResponse {
            $response = $next($request);
            return HttpResponse::text('A-' . $response->body(), $response->statusCode(), $response->headers());
        });
        $router->addMiddleware(static function (HttpRequest $request, callable $next): HttpResponse {
            $response = $next($request);
            return HttpResponse::text('B-' . $response->body(), $response->statusCode(), $response->headers());
        });
        $router->add('GET', '/m', static function (HttpRequest $request): HttpResponse {
            return HttpResponse::text('H', 200);
        });

        $response = $router->dispatch(new HttpRequest('GET', '/m'));

        self::assertSame(200, $response->statusCode());
        self::assertSame('A-B-H', $response->body());
    }
}
