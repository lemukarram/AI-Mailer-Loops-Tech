<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Mailer.php';
require_once __DIR__ . '/../src/Crypto.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();

// Get settings from POST to test before saving, or from DB
$host = $_POST['smtp_host'] ?? '';
$port = (int)($_POST['smtp_port'] ?? 587);
$user = $_POST['smtp_user'] ?? '';
$pass = $_POST['smtp_pass'] ?? '';

// If password is empty in POST, get from DB
if (empty($pass)) {
    $stmt = $db->prepare("SELECT smtp_pass FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pass = Crypto::decrypt($stmt->fetchColumn());
}

if (empty($host) || empty($user) || empty($pass)) {
    echo json_encode(['success' => false, 'error' => 'SMTP Host, User, and Password are required for testing.']);
    exit();
}

try {
    $mailer = new Mailer($host, $port, $user, $pass);
    $sent = $mailer->send($user, "SMTP Test - aiMailSaas", "Success! Your SMTP settings are working perfectly for your job applications.", "Sent via aiMailSaas Core Engine");

    if ($sent) {
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully to ' . $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'SMTP Test failed. Check your credentials and server firewall.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
