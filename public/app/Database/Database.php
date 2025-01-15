<?php

namespace App\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?PDO $pdo = null;

    private PDOStatement $stmt;

    /**
     * Gets the PDO instance
     */

    public function __construct()
    {
        if (self::$pdo === null) {
            $this->connect();
        }
    }

    /**
     * Establishes a new connection to the database if one doesn't exist
     * @return void
     */
    private function connect()
    {
        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
        $options = [
            PDO::ATTR_DEFAULT_STR_PARAM => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            self::$pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $options);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Prepares and executes a query with optional parameters
     * @param string $query
     * @param array $params
     * @throws \Exception
     * @return \App\Database\Database
     */
    public function query(string $query, array $params = []): Database
    {
        try {
            $this->stmt = self::$pdo->prepare($query);
            $this->stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }

        return $this;
    }

    /**
     * Fetches a single record from the executed query
     * @throws \Exception
     * @return array|false
     */
    public function find(): array|false
    {
        try {
            return $this->stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Fetch failed: " . $e->getMessage());
        }
    }

    /**
     * Fetches all records from the executed query
     * @throws \Exception
     * @return array|false
     */
    public function findAll(): array|false
    {
        try {
            return $this->stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Fetch failed: " . $e->getMessage());
        }
    }
}
