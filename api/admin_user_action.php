<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Mailer.php';
require_once __DIR__ . '/../src/Crypto.php';

Auth::requireAdmin();
$db = Database::getInstance()->getConnection();

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $response['message'] = 'CSRF token verification failed.';
        echo json_encode($response);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    $ids = array_map('intval', $ids);

    if (empty($ids)) {
        $response['message'] = 'No users selected.';
        echo json_encode($response);
        exit;
    }

    if ($action === 'set_status') {
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['active', 'inactive'])) {
            $response['message'] = 'Invalid status.';
            echo json_encode($response);
            exit;
        }

        // Exclude current admin from bulk status changes for safety
        $current_admin_id = Auth::getUserId();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE id IN ($placeholders) AND role != 'admin'");
        $params = array_merge([$status], $ids);
        $stmt->execute($params);

        $response['success'] = true;
        $response['message'] = "Status updated for selected users (excluding admins).";

        // Optional: Send notification emails (only for individual toggle or small batches)
        if (count($ids) === 1) {
            $user_id = $ids[0];
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_email = $stmt->fetchColumn();

            // Fetch current admin's SMTP settings
            $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$current_admin_id]);
            $admin_smtp = $stmt->fetch();

            if ($admin_smtp && !empty($admin_smtp['smtp_host'])) {
                try {
                    $mailer = new Mailer(
                        $admin_smtp['smtp_host'],
                        $admin_smtp['smtp_port'],
                        $admin_smtp['smtp_user'],
                        Crypto::decrypt($admin_smtp['smtp_pass'])
                    );
                    
                    $status_msg = strtoupper($status);
                    $subject = "Account Alert: Your Access Status Changed";
                    $body = "Hello,\n\nThis is an automated notification to inform you that your account status on AI Mailer has been changed to: **$status_msg**.\n\nRegards,\nAI Mailer Administration";
                    
                    if ($mailer->send($user_email, $subject, $body)) {
                        $response['message'] .= " Notification email sent.";
                    }
                } catch (Exception $e) {}
            }
        }
    } elseif ($action === 'delete') {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role != 'admin'");
        $stmt->execute($ids);
        $response['success'] = true;
        $response['message'] = "Selected users deleted.";
    }
}

header('Content-Type: application/json');
echo json_encode($response);
