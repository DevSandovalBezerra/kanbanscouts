<?php

declare(strict_types=1);

use App\Helpers\Router;

$autoloadPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoloadPath)) {
    require $autoloadPath;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relativePath;
        if (is_file($path)) {
            require $path;
        }
    });
}

$router = new Router();

$webRoutes = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
$webRoutes($router);

$apiRoutes = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
$apiRoutes($router);

return $router;
