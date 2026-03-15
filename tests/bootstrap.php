<?php

declare(strict_types=1);

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

error_reporting(E_ALL);
ini_set('display_errors', '1');
