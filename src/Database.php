<?php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        require_once __DIR__ . '/../config/config.php';
        
        try {
            if (DB_HOST === 'none') return; // For testing
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // Only fail if we actually try to use the connection
            // For now we'll just store the error or null
            $this->conn = null;
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public static function setInstance($instance) {
        self::$instance = $instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
