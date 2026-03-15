<?php

declare(strict_types=1);

namespace App\Helpers;

final class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $path = $path === '' ? '/' : ($path[0] === '/' ? $path : '/' . $path);

        $this->routes[$method][$path] = $handler;
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function dispatch(HttpRequest $request): HttpResponse
    {
        $method = $request->method();
        $path = $request->path();

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            return HttpResponse::text('Not Found', 404);
        }

        if (is_array($handler)) {
            $handler = $this->resolveArrayHandler($handler);
        }

        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            static function (callable $next, callable $middleware): callable {
                return static function (HttpRequest $request) use ($middleware, $next): HttpResponse {
                    $response = $middleware($request, $next);
                    if (!$response instanceof HttpResponse) {
                        return HttpResponse::text('Invalid middleware response', 500);
                    }
                    return $response;
                };
            },
            static function (HttpRequest $request) use ($handler): HttpResponse {
                $response = $handler($request);
                if (!$response instanceof HttpResponse) {
                    return HttpResponse::text('Invalid handler response', 500);
                }
                return $response;
            }
        );

        $response = $pipeline($request);
        if (!$response instanceof HttpResponse) {
            return HttpResponse::text('Invalid handler response', 500);
        }

        return $response;
    }

    private function resolveArrayHandler(array $handler): callable
    {
        $target = $handler[0] ?? null;
        $method = $handler[1] ?? null;

        if (is_string($target) && is_string($method)) {
            $instance = new $target();
            return [$instance, $method];
        }

        if (is_object($target) && is_string($method)) {
            return [$target, $method];
        }

        return static fn (HttpRequest $request): HttpResponse => HttpResponse::text('Invalid handler', 500);
    }
}
