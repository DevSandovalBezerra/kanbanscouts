<?php

declare(strict_types=1);

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Helpers\Router;

return static function (Router $router): void {
    $router->add('GET', '/health', static function (HttpRequest $request): HttpResponse {
        return HttpResponse::json(['ok' => true], 200);
    });
};
