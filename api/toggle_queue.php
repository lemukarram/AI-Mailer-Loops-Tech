<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare("SELECT queue_status FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn() ?: 'stopped';

    $new_status = ($current === 'started') ? 'stopped' : 'started';

    $stmt = $db->prepare("UPDATE user_settings SET queue_status = ? WHERE user_id = ?");
    $stmt->execute([$new_status, $user_id]);

    echo json_encode(['success' => true, 'status' => $new_status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
