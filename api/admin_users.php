<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

Auth::requireAdmin();
$db = Database::getInstance()->getConnection();

// Fetch all users with statistics
$query = "
    SELECT 
        u.id, 
        u.email, 
        u.role, 
        u.status, 
        u.created_at,
        us.purpose,
        up.phone,
        up.resume_path,
        (SELECT COUNT(*) FROM mailing_list WHERE user_id = u.id AND status = 'sent') as sent_count,
        (SELECT COUNT(*) FROM mailing_list WHERE user_id = u.id AND status = 'failed') as failed_count,
        (SELECT COUNT(*) FROM mailing_list WHERE user_id = u.id AND status = 'unsent') as queue_count,
        (SELECT COUNT(*) FROM mailing_list WHERE user_id = u.id) as total_contacts
    FROM users u
    LEFT JOIN user_settings us ON u.id = us.user_id
    LEFT JOIN user_profiles up ON u.id = up.user_id
    ORDER BY u.created_at DESC
";
$stmt = $db->query($query);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['data' => $data]);
