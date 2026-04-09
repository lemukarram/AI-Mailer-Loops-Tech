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

$contact_id = $_POST['id'] ?? null;
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';
$footer = $_POST['footer'] ?? '';

if (!$contact_id || empty($subject) || empty($body)) {
    echo json_encode(['success' => false, 'error' => 'Missing contact ID, subject, or body']);
    exit();
}

try {
    // Get contact email
    $stmt = $db->prepare("SELECT email FROM mailing_list WHERE id = ? AND user_id = ?");
    $stmt->execute([$contact_id, $user_id]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contact) {
        throw new Exception("Contact not found.");
    }

    // Get user settings
    $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings || empty($settings['smtp_host'])) {
        throw new Exception("Please configure your SMTP settings first.");
    }

    $smtp_pass = Crypto::decrypt($settings['smtp_pass']);
    $mailer = new Mailer($settings['smtp_host'], $settings['smtp_port'], $settings['smtp_user'], $smtp_pass);

    // Get attachment from campaign
    $stmt = $db->prepare("SELECT attachment_path FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    $attachment = ($campaign && !empty($campaign['attachment_path'])) ? __DIR__ . '/../' . $campaign['attachment_path'] : null;

    $sent = $mailer->send($contact['email'], $subject, $body, $footer, $attachment);

    if ($sent) {
        $stmt = $db->prepare("UPDATE mailing_list SET status = 'sent', last_sent_at = NOW() WHERE id = ?");
        $stmt->execute([$contact_id]);
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("SMTP sending failed.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
