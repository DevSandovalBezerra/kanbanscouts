<?php

declare(strict_types=1);

use App\Helpers\DatabaseMigrator;
use App\Repositories\PdoConnectionFactory;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

$config = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$pdo = PdoConnectionFactory::fromConfig($config);

$driver = (string) ($config['driver'] ?? 'mysql');
$migrationsDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $driver;

$migrator = new DatabaseMigrator($migrationsDir);
$migrator->migrate($pdo);

fwrite(STDOUT, "Migrations applied for driver: {$driver}\n");
