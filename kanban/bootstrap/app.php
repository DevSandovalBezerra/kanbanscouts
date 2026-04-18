<?php

declare(strict_types=1);

use App\Helpers\Router;

// Carrega o autoloader do Composer
$autoloadPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

// Carrega variáveis do .env usando a biblioteca Dotenv
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} else {
    // Fallback: Caso o Dotenv não esteja disponível (improvável se o vendor estiver ok)
    $loadDotEnv = static function (string $path): bool {
        if (!is_file($path) || !is_readable($path)) {
            return false;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return false;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            if ($key === '') continue;
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            @putenv($key . '=' . $value);
        }
        return true;
    };
    $loadDotEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
}

$logsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0775, true);
}
if (!is_writable($logsDir)) {
    $logsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0775, true);
    }
}

$logFile = rtrim($logsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'error-' . date('Y-m-d') . '.log';
ini_set('log_errors', '1');
ini_set('error_log', $logFile);
error_reporting(E_ALL);

if (class_exists(\App\Helpers\ErrorLogger::class)) {
    \App\Helpers\ErrorLogger::register($logsDir);
}

$getFirstEnv = static function (array $keys): string {
    foreach ($keys as $key) {
        $value = getenv((string) $key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $value = $_ENV[(string) $key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        $value = $_SERVER[(string) $key] ?? null;
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
    }

    return '';
};

$dbUser = $getFirstEnv(['DB_USER', 'DB_USERNAME']);
$dbPass = $getFirstEnv(['DB_PASS', 'DB_PASSWORD']);
if (!is_string($dbUser) || trim($dbUser) === '' || !is_string($dbPass) || trim($dbPass) === '') {
    $payload = json_encode([
        'ts' => date('c'),
        'level' => 'env_missing',
        'message' => 'Variáveis de banco ausentes ou vazias.',
        'context' => [
            'dotenv_checked' => $dotenvPaths,
            'dotenv_loaded' => $dotenvLoaded,
            'db_user_present' => is_string($dbUser) && trim($dbUser) !== '',
            'db_pass_present' => is_string($dbPass) && trim($dbPass) !== '',
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload !== false) {
        error_log($payload);
    }
}

$dbConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$pdo = \App\Repositories\PdoConnectionFactory::fromConfig($dbConfig);

$router = new Router();

$webRoutes = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
$webRoutes($router, $pdo);

$apiRoutes = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
$apiRoutes($router, $pdo);

\App\Helpers\View::setTemplatesDir(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'default');

return $router;
