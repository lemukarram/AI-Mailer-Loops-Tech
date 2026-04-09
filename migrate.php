<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database.php';

// Check for admin key
$key = $_GET['key'] ?? '';
if ($key !== ADMIN_KEY) {
    die("Unauthorized access.");
}

$db = Database::getInstance()->getConnection();

// SQL Schema
$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS admin_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        max_emails_per_hour INT DEFAULT 100,
        total_max_emails INT DEFAULT 1000,
        max_file_upload_size INT DEFAULT 5242880, -- 5MB in bytes
        max_excel_rows INT DEFAULT 1000,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS user_settings (
        user_id INT PRIMARY KEY,
        openai_api_key VARCHAR(255),
        gemini_api_key VARCHAR(255),
        preferred_llm ENUM('openai', 'gemini') DEFAULT 'openai',
        smtp_host VARCHAR(255),
        smtp_port INT,
        smtp_user VARCHAR(255),
        smtp_pass VARCHAR(255),
        personal_hourly_limit INT DEFAULT 50,
        queue_status ENUM('started', 'stopped') DEFAULT 'stopped',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS mailing_list (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        contact_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        company VARCHAR(255),
        designation VARCHAR(255),
        company_type VARCHAR(255),
        status ENUM('unsent', 'sent', 'failed') DEFAULT 'unsent',
        last_sent_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    \"CREATE TABLE IF NOT EXISTS campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(255),
        base_prompt TEXT,
        subject_template VARCHAR(255),
        body_template TEXT,
        footer_template TEXT,
        attachment_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )\",
    \"CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        type ENUM('info', 'error', 'warning') DEFAULT 'info',
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )\",
    \"CREATE TABLE IF NOT EXISTS user_profiles (
        user_id INT PRIMARY KEY,
        full_name VARCHAR(255),
        phone VARCHAR(50),
        linkedin_url VARCHAR(255),
        website_url VARCHAR(255),
        company_name VARCHAR(255),
        designation VARCHAR(255),
        other_info TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )\"
];

foreach ($queries as $sql) {
    try {
        $db->exec($sql);
        echo "Successfully executed: " . substr($sql, 0, 50) . "...<br>";
    } catch (PDOException $e) {
        echo "Error executing query: " . $e->getMessage() . "<br>";
    }
}

// Create initial admin user if none exists
$admin_email = 'admin@example.com';
$admin_pass = password_hash('admin_pass', PASSWORD_DEFAULT);
$stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin'");
$stmt->execute();
if (!$stmt->fetch()) {
    $stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$admin_email, $admin_pass]);
    echo "Default admin user created: admin@example.com / admin_pass<br>";

    // Initialize admin limits
    $db->exec("INSERT INTO admin_limits (max_emails_per_hour, total_max_emails, max_file_upload_size, max_excel_rows) VALUES (100, 1000, 5242880, 1000)");
    echo "Default admin limits initialized.<br>";
}

echo "Migration completed successfully.";
