<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Campaign.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();

$contact_id = $_POST['id'] ?? null;
$base_prompt = $_POST['base_prompt'] ?? 'Write a professional outreach email.';

if (!$contact_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit();
}

try {
    // Get contact data
    $stmt = $db->prepare("SELECT * FROM mailing_list WHERE id = ? AND user_id = ?");
    $stmt->execute([$contact_id, $user_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        throw new Exception("Contact not found.");
    }

    // Get user settings
    $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("Please configure your API keys in Settings first.");
    }

    $aiEmail = Campaign::generateAIEmail($settings, $contact, $base_prompt);

    echo json_encode(['success' => true, 'data' => $aiEmail]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
