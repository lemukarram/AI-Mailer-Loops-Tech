<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/LLM.php';

Auth::requireLogin();
$user_id = Auth::getUserId();

if (!Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit();
}

$db = Database::getInstance()->getConnection();

try {
    // 1. Get Master Gemini Key
    $stmt = $db->query("SELECT master_gemini_key FROM admin_limits LIMIT 1");
    $master_key = $stmt->fetchColumn();

    if (!$master_key) {
        throw new Exception("Platform Master AI is not configured. Please contact Admin.");
    }

    // 2. Get User Settings and Profile
    $stmt = $db->prepare("SELECT purpose, master_ai_used FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($settings['master_ai_used']) {
        throw new Exception("You have already used the Master AI setup once.");
    }

    $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    $purpose = $settings['purpose'] ?? 'job_hunt';
    
    // Check if profile is substantially setup
    $is_profile_setup = (!empty($profile['full_name']) && !empty($profile['designation']));

    if ($is_profile_setup) {
        // CALL AI AGENT
        $llm = new LLM('gemini', $master_key, 'gemini-2.5-flash');
        $result = $llm->generateCampaignPlaceholder($purpose, $profile);
        
        $base_prompt = $result['base_prompt'] ?? '';
        $subject = $result['subject'] ?? '';
        $body = $result['body'] ?? '';
    } else {
        // GENERIC FALLBACKS
        if ($purpose === 'job_hunt') {
            $base_prompt = "Write a warm, professional, and 100% humanized outreach email to a hiring manager. Emphasize my enthusiasm for the role at [company] and keep it concise.100% interesting and humanize tone";
            $subject = "Regarding the open position at [company]";
            $body = "Hi there,\n\nI recently came across the opening at [company] and was immediately drawn to the team's mission. I've been following your progress in the industry and would love to bring my skills to your current projects.\n\nAre you open to a brief chat next week to see if my background might be a good fit?\n\nBest regards,\n[My Name]";
        } else {
            $base_prompt = "Write a soft-toned, catchy B2B outreach email for my services. Focus on how I can provide value to [company] without being pushy or salesy.100% interesting and humanize tone";
            $subject = "Quick question for the [company] team";
            $body = "Hello,\n\nI've been admiring the work [company] is doing and noticed some interesting opportunities where my services could help you scale even faster.\n\nI'm not looking to sell you anything right now—just wanted to introduce myself and see if we might have some common ground for a quick conversation.\n\nCheers,\n[My Name]";
        }
    }

    // Mark as used
    $stmt = $db->prepare("UPDATE user_settings SET master_ai_used = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);

    echo json_encode([
        'success' => true,
        'base_prompt' => $base_prompt,
        'subject' => $subject,
        'body' => $body
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
