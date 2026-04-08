<?php
// cron/send_queue.php
// Performance-optimized version with decryption and timeouts.
set_time_limit(300); // Allow 5 minutes total
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Mailer.php';
require_once __DIR__ . '/../src/Campaign.php';
require_once __DIR__ . '/../src/Crypto.php';

$db = Database::getInstance()->getConnection();

// 1. Get all users with started queues
$stmt = $db->query("SELECT * FROM user_settings WHERE queue_status = 'started'");
$users_to_process = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users_to_process as $user_settings) {
    $user_start_time = time();
    $user_id = $user_settings['user_id'];
    
    // 2. Check limits (personal hourly limit)
    $stmt = $db->prepare("SELECT COUNT(*) FROM mailing_list WHERE user_id = ? AND status = 'sent' AND last_sent_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$user_id]);
    $sent_in_last_hour = $stmt->fetchColumn();

    if ($sent_in_last_hour >= $user_settings['personal_hourly_limit']) {
        continue;
    }

    // 3. Get next contacts in queue
    $limit = min(50, $user_settings['personal_hourly_limit'] - $sent_in_last_hour); // Small batches
    $stmt = $db->prepare("SELECT * FROM mailing_list WHERE user_id = ? AND status = 'unsent' LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($queue)) continue;

    // 4. Get active campaign
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) continue;

    // Decrypt SMTP password
    $smtp_pass = Crypto::decrypt($user_settings['smtp_pass']);
    $mailer = new Mailer($user_settings['smtp_host'], $user_settings['smtp_port'], $user_settings['smtp_user'], $smtp_pass);

    foreach ($queue as $contact) {
        // Anti-timeout check
        if (time() - $user_start_time > 60) break; // Don't spend more than 60s per user session

        try {
            if (!empty($campaign['base_prompt'])) {
                // AI Mode - This is the bottleneck. Ideally we'd pre-generate.
                // For performance, pre-generation should happen elsewhere, 
                // but here we ensure we don't hang the script.
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

            $sent = $mailer->send($contact['email'], $subject, $body, $footer);
            
            if ($sent) {
                $db->prepare("UPDATE mailing_list SET status = 'sent', last_sent_at = NOW() WHERE id = ?")->execute([$contact['id']]);
            } else {
                $db->prepare("UPDATE mailing_list SET status = 'failed' WHERE id = ?")->execute([$contact['id']]);
            }
        } catch (Exception $e) {
            error_log("Queue error for user $user_id, contact {$contact['id']}: " . $e->getMessage());
        }
    }
}
echo "Queue processing completed.";
