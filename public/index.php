<?php

declare(strict_types=1);

use App\Helpers\HttpRequest;

$router = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

$request = HttpRequest::fromGlobals();
$response = $router->dispatch($request);

http_response_code($response->statusCode());
foreach ($response->headers() as $name => $value) {
    if (is_array($value)) {
        foreach ($value as $v) {
            header($name . ': ' . $v, false);
        }
        continue;
    }
    header($name . ': ' . $value, true);
}

echo $response->body();
