<?php

declare(strict_types=1);

class Database
{
    public static function connect(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = (int)($config['port'] ?? 3306);
        $db = $config['name'] ?? 'finanzas60s';
        $user = $config['user'] ?? '';
        $pass = $config['password'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $db, $charset);

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
