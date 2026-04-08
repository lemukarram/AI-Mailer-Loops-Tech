<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Campaign.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();
$contact_id = $_POST['id'] ?? null;

try {
    // Get contact data
    $stmt = $db->prepare("SELECT * FROM mailing_list WHERE id = ? AND user_id = ?");
    $stmt->execute([$contact_id, $user_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        throw new Exception("Contact not found.");
    }

    // Get latest campaign templates
    $stmt = $db->prepare("SELECT subject_template, body_template, footer_template FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'subject_template' => 'Hello [contact_name]',
        'body_template' => 'I am writing to you from [company].',
        'footer_template' => 'Best regards'
    ];

    // Replace variables
    $subject = Campaign::replaceVariables($campaign['subject_template'], $contact);
    $body = Campaign::replaceVariables($campaign['body_template'], $contact);
    $footer = Campaign::replaceVariables($campaign['footer_template'], $contact);

    echo json_encode([
        'success' => true,
        'recipient' => $contact['email'],
        'subject' => $subject,
        'body' => $body,
        'footer' => $footer
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
