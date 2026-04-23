<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Crypto.php';
require_once __DIR__ . '/../src/LLM.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // 1. Basic Info
    $purpose = $_POST['purpose'] ?? 'job_hunt';
    $full_name = $_POST['full_name'] ?? '';
    $designation = $_POST['designation'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $other_info = $_POST['other_info'] ?? '';

    // 2. Handle File Upload (Resume / Business Profile)
    $attachment_path = '';
    if (isset($_FILES['attachment_file']) && $_FILES['attachment_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['attachment_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'docx'])) {
            $filename = "setup_" . $user_id . "_" . time() . "." . $ext;
            if (!is_dir('../uploads')) mkdir('../uploads', 0755, true);
            move_uploaded_file($file['tmp_name'], '../uploads/' . $filename);
            $attachment_path = 'uploads/' . $filename;
        }
    }

    // 3. Update User Profile
    $resume_path = ($purpose === 'job_hunt') ? $attachment_path : '';
    $business_path = ($purpose === 'business_leads') ? $attachment_path : '';

    $stmt = $db->prepare("INSERT INTO user_profiles (user_id, full_name, designation, company_name, other_info, resume_path, business_profile_path) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE 
                         full_name=VALUES(full_name), designation=VALUES(designation), 
                         company_name=VALUES(company_name), other_info=VALUES(other_info),
                         resume_path=VALUES(resume_path), business_profile_path=VALUES(business_profile_path)");
    $stmt->execute([$user_id, $full_name, $designation, $company_name, $other_info, $resume_path, $business_path]);

    // 4. Update User Settings
    $ai_enabled = isset($_POST['ai_enabled']) ? 1 : 0;
    $preferred_llm = $_POST['preferred_llm'] ?? 'gemini';
    $api_key = Crypto::encrypt($_POST['api_key'] ?? '');
    
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = !empty($_POST['smtp_port']) ? (int)$_POST['smtp_port'] : 465;
    $smtp_user = $_POST['smtp_user'] ?? '';
    $smtp_pass = Crypto::encrypt($_POST['smtp_pass'] ?? '');
    
    $personal_limit = !empty($_POST['personal_hourly_limit']) ? (int)$_POST['personal_hourly_limit'] : 10;
    if ($personal_limit > 12) $personal_limit = 12;

    $stmt = $db->prepare("UPDATE user_settings SET 
                         purpose = ?, ai_enabled = ?, preferred_llm = ?, 
                         openai_api_key = ?, gemini_api_key = ?, 
                         smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_pass = ?,
                         personal_hourly_limit = ?, wizard_completed = 1 
                         WHERE user_id = ?");
    
    $oa_key = ($preferred_llm === 'openai') ? $api_key : null;
    $gm_key = ($preferred_llm === 'gemini') ? $api_key : null;

    $stmt->execute([
        $purpose, $ai_enabled, $preferred_llm, 
        $oa_key, $gm_key,
        $smtp_host, $smtp_port, $smtp_user, $smtp_pass,
        $personal_limit, $user_id
    ]);

    // 5. Pre-generate Campaign Placeholder using Master AI (if possible)
    $stmt = $db->query("SELECT master_gemini_key FROM admin_limits LIMIT 1");
    $master_key = $stmt->fetchColumn();

    $base_prompt = ''; $subject = ''; $body = '';

    if ($master_key) {
        try {
            // Set a short timeout for this specific AI call so it doesn't block completion
            // Fetch the profile we just saved
            $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($profile['full_name'])) {
                $llm = new LLM('gemini', $master_key, 'gemini-2.5-flash');
                $ai_data = $llm->generateCampaignPlaceholder($purpose, $profile);
                
                $base_prompt = $ai_data['base_prompt'] ?? '';
                $subject = $ai_data['subject'] ?? '';
                $body = $ai_data['body'] ?? '';

                // Mark master AI as used
                $db->prepare("UPDATE user_settings SET master_ai_used = 1 WHERE user_id = ?")->execute([$user_id]);
            }
        } catch (Exception $e) {
            // Log error internally if needed, but don't stop the flow
        }
    }

    // Fallback generic if AI failed or no master key
    if (empty($subject)) {
        if ($purpose === 'job_hunt') {
            $base_prompt = "Write a warm, professional, and 100% humanized outreach email to a hiring manager.";
            $subject = "Regarding the open position at [company]";
            $body = "Hi there,\n\nI recently came across the opening at [company] and was immediately drawn to the team's mission.\n\nAre you open to a brief chat next week?\n\nBest regards,\n" . $full_name;
        } else {
            $base_prompt = "Write a soft-toned, catchy B2B outreach email for my services.";
            $subject = "Quick question for the [company] team";
            $body = "Hello,\n\nI've been admiring the work [company] is doing and noticed some interesting opportunities where my services could help you scale even faster.\n\nCheers,\n" . $full_name;
        }
    }

    // Create Initial Campaign
    $stmt = $db->prepare("INSERT INTO campaigns (user_id, title, base_prompt, subject_template, body_template, attachment_path) 
                         VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id, 
        ($purpose === 'job_hunt' ? 'Job Outreach' : 'Business Growth'),
        $base_prompt,
        $subject,
        $body,
        $attachment_path
    ]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
