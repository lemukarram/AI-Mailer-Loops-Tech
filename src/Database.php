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
        if ($this->conn === null) {
            die("Database Connection Error: Please ensure your .env file is configured correctly and the database is accessible.");
        }
        return $this->conn;
    }

    public static function log($message, $type = 'info', $user_id = null) {
        try {
            $db = self::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO logs (user_id, type, message) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $type, $message]);
        } catch (Exception $e) {
            error_log("Logging error: " . $e->getMessage());
        }
    }
}
