<?php

declare(strict_types=1);

$getEnvValue = static function (string $key): ?string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return null;
    }

    $value = trim((string) $value);
    return $value === '' ? null : $value;
};

$dbHost = $getEnvValue('DB_HOST');
$dbName = $getEnvValue('DB_NAME') ?: $getEnvValue('DB_DATABASE');
$dbUser = $getEnvValue('DB_USER') ?: $getEnvValue('DB_USERNAME');
$dbPass = $getEnvValue('DB_PASS') ?: $getEnvValue('DB_PASSWORD');
$dbPort = $getEnvValue('DB_PORT') ?: '3306';
$dbDriver = $getEnvValue('DB_DRIVER') ?: 'mysql';
$dbCharset = $getEnvValue('DB_CHARSET') ?: 'utf8mb4';

// Detecção de ambiente
$isProduction = false;
$env = $getEnvValue('SCOUTHONDURAS_ENV') ?: $getEnvValue('APP_ENV');

if ($env === 'production') {
    $isProduction = true;
} elseif ($env === 'development' || $env === 'local') {
    $isProduction = false;
} else {
    // Detecção baseada no host
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    if (strpos($host, 'scouthonduras') !== false || strpos($host, 'moveromundo') !== false) {
        $isProduction = true;
    } elseif ($host !== '' && strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false && strpos($host, '.local') === false) {
        $isProduction = true;
    }
}

// Fallbacks baseados no ambiente
$defaultDbHost = '127.0.0.1';

if ($isProduction) {
    // Fallbacks para Produção (Nuvem)
    $defaultDbName = 'u827221935_kanban';
    $defaultDbUser = 'u827221935_kanbanadm';
    $defaultDbPass = '';
} else {
    // Fallbacks para Desenvolvimento (Local)
    $defaultDbName = 'brunor90_kanban';
    $defaultDbUser = 'root';
    $defaultDbPass = ''; // Senha local que definimos anteriormente
}

$finalDbName = $dbName ?: $defaultDbName;
$finalDbUser = $dbUser ?: $defaultDbUser;
$finalDbPass = $dbPass !== null && $dbPass !== '' ? $dbPass : $defaultDbPass;

// LOG FORÇADO DE DEBUG PARA ACHAR O PROBLEMA NAS NUVENS
error_log(sprintf(
    '[DEBUG DATABASE CONFIG] ENV Detected: %s | IsProduction: %d | Host: %s | DB: %s | User: %s | PassLength: %d | Origem DB_PASS_ENV: %s',
    $env ?: 'none',
    $isProduction ? 1 : 0,
    $_SERVER['HTTP_HOST'] ?? 'cli',
    $finalDbName,
    $finalDbUser,
    strlen($finalDbPass),
    $dbPass ? 'sim' : 'nao'
));

return [
    'driver' => $dbDriver,
    'host' => $dbHost ?: $defaultDbHost,
    'port' => $dbPort,
    'database' => $finalDbName,
    'username' => $finalDbUser,
    'password' => $finalDbPass,
    'charset' => $dbCharset,
    'sqlite_path' => $getEnvValue('DB_SQLITE_PATH') ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'database.sqlite'
];
