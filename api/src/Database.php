<?php

/**
 * Database connection class
 */
class Database
{
    private $host = "db";
    private $db_name = "course_catalog";
    private $username = "test_user";
    private $password = "test_password";
    private $conn;

    /**
     * Get database connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo json_encode([
                "status" => "error",
                "message" => "Connection error: " . $exception->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }

    /**
     * run database migrations if not already run
     */
    public function runMigrations(): void
    {
        $sql = file_get_contents(__DIR__ . '/database/migrations/1744097978_migrations.sql');
        $this->conn->exec($sql);
    }
}