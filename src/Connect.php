<?php

namespace Ren\App;

use \PDO;

class Connect
{
    private const OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ];
    private static $pdo;

    public static function connectPDO(): PDO
    {
        $driver = getenv("DB_DRIVER");
        $host = getenv("DB_HOST");
        $port = getenv("DB_PORT");
        $dbname = getenv("DB_NAME");
        $username = getenv("DB_USER");
        $password = getenv("DB_PASSWORD");
        $dsn = "$driver:host=$host;port=$port;dbname=$dbname";

        if (!isset(self::$pdo)) {
            self::$pdo = new PDO($dsn, $username, $password, self::OPTIONS);
        }
        
        return self::$pdo;
    }
}
