<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host   = $_ENV['DB_HOST'];
            $port   = $_ENV['DB_PORT'];
            $dbname = $_ENV['DB_NAME'];
            $user   = $_ENV['DB_USER'];
            $pass   = $_ENV['DB_PASS'];

            try {
                self::$connection = new PDO(
                    "pgsql:host=$host;port=$port;dbname=$dbname",
                    $user,
                    $pass
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['erro' => 'Falha na conexão com o banco.']));
            }
        }

        return self::$connection;
    }
}