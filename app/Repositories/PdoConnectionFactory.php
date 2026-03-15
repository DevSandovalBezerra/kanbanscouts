<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class PdoConnectionFactory
{
    public static function fromConfig(array $config): PDO
    {
        $driver = $config['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $path = (string) ($config['sqlite_path'] ?? '');
            if ($path === '') {
                throw new RuntimeException('Missing sqlite_path');
            }
            $pdo = new PDO('sqlite:' . $path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }

        if ($driver === 'mysql') {
            $host = (string) ($config['host'] ?? '127.0.0.1');
            $port = (string) ($config['port'] ?? '3306');
            $database = (string) ($config['database'] ?? '');
            $username = (string) ($config['username'] ?? '');
            $password = (string) ($config['password'] ?? '');
            $charset = (string) ($config['charset'] ?? 'utf8mb4');

            if ($database === '') {
                throw new RuntimeException('Missing database');
            }

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        }

        throw new RuntimeException('Unsupported driver: ' . $driver);
    }
}
