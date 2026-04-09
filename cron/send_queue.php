<?php
// cron/send_queue.php
// Performance-optimized version with decryption and timeouts.
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // Allow 5 minutes total
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Mailer.php';
require_once __DIR__ . '/../src/Campaign.php';
require_once __DIR__ . '/../src/Crypto.php';

$db = Database::getInstance()->getConnection();

// Log start of cron
Database::log("Cron job started", 'info');

// 1. Get all users with started queues
$stmt = $db->query("SELECT * FROM user_settings WHERE queue_status = 'started'");
$users_to_process = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users_to_process)) {
    Database::log("Cron job: No users with 'started' queue status found.", 'info');
}

foreach ($users_to_process as $user_settings) {
    $user_start_time = time();
    $user_id = $user_settings['user_id'];
    
    // 2. Check limits (personal hourly limit)
    $stmt = $db->prepare("SELECT COUNT(*) FROM mailing_list WHERE user_id = ? AND status = 'sent' AND last_sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$user_id]);
    $sent_in_last_hour = $stmt->fetchColumn();

    if ($sent_in_last_hour >= $user_settings['personal_hourly_limit']) {
        Database::log("User $user_id reached hourly limit ($sent_in_last_hour/{$user_settings['personal_hourly_limit']})", 'warning', $user_id);
        continue;
    }

    // 3. Get next contacts in queue
    $limit = (int)min(50, $user_settings['personal_hourly_limit'] - $sent_in_last_hour); // Small batches
    $stmt = $db->prepare("SELECT * FROM mailing_list WHERE user_id = ? AND status = 'unsent' LIMIT $limit");
    $stmt->execute([$user_id]);
    $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($queue)) {
        // No more emails to send for this user
        continue;
    }

    Database::log("Processing " . count($queue) . " emails for user $user_id", 'info', $user_id);

    // 4. Get active campaign
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        Database::log("No active campaign found for user $user_id", 'warning', $user_id);
        continue;
    }

    try {
        // Decrypt SMTP password
        $smtp_pass = Crypto::decrypt($user_settings['smtp_pass']);
        $mailer = new Mailer($user_settings['smtp_host'], $user_settings['smtp_port'], $user_settings['smtp_user'], $smtp_pass);
    } catch (Exception $e) {
        Database::log("SMTP decryption/initialization failed for user $user_id: " . $e->getMessage(), 'error', $user_id);
        Database::log("System Error: " . $e->getMessage(), 'error'); // Admin log
        continue;
    }

    foreach ($queue as $contact) {
        // Anti-timeout check
        if (time() - $user_start_time > 60) {
            Database::log("User $user_id session timed out (60s limit reached)", 'warning', $user_id);
            break; 
        }

        try {
            if (!empty($campaign['base_prompt'])) {
                // AI Mode
                $aiEmail = Campaign::generateAIEmail($user_settings, $contact, $campaign['base_prompt']);
                $subject = $aiEmail['subject'];
                $body = $aiEmail['body'];
                $footer = $aiEmail['footer'];
            } else {
                // Manual Mode
                $subject = Campaign::replaceVariables($campaign['subject_template'], $contact);
                $body = Campaign::replaceVariables($campaign['body_template'], $contact);
                $footer = Campaign::replaceVariables($campaign['footer_template'], $contact);
            }

            $attachment = !empty($campaign['attachment_path']) ? __DIR__ . '/../' . $campaign['attachment_path'] : null;
            $sent = $mailer->send($contact['email'], $subject, $body, $footer, $attachment);
            
            if ($sent) {
                $db->prepare("UPDATE mailing_list SET status = 'sent', last_sent_at = NOW() WHERE id = ?")->execute([$contact['id']]);
            } else {
                $db->prepare("UPDATE mailing_list SET status = 'failed' WHERE id = ?")->execute([$contact['id']]);
                Database::log("Failed to send email to {$contact['email']}", 'error', $user_id);
            }
        } catch (Exception $e) {
            $msg = "Error for contact {$contact['email']}: " . $e->getMessage();
            error_log("Queue error for user $user_id, contact {$contact['id']}: " . $e->getMessage());
            Database::log($msg, 'error', $user_id);
            Database::log("System Error (User $user_id): " . $e->getMessage(), 'error'); // Admin log
        }
    }
}
Database::log("Cron job finished", 'info');
echo "Queue processing completed.";
