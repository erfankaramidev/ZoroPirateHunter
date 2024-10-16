<?php

namespace App\Database;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;

    private PDOStatement $stmt;

    /**
     * Creates a new connection to the database via PDO
     */
    public function __construct()
    {
        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
        $options = [
            PDO::ATTR_DEFAULT_STR_PARAM => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function query(string $query, array $params = []): Database
    {
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);

        return $this;
    }

    public function find(): array|bool
    {
        return $this->stmt->fetch();
    }

    public function findAll(): array|bool
    {
        return $this->stmt->fetchAll();
    }
}
