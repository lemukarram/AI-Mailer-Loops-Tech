<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Crypto.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$user_role = $_SESSION['user_role'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $openai_key = $_POST['openai_api_key'] ?? '';
        $gemini_key = $_POST['gemini_api_key'] ?? '';
        $preferred_llm = $_POST['preferred_llm'] ?? 'openai';
        $smtp_host = $_POST['smtp_host'] ?? '';
        $smtp_port = (int)($_POST['smtp_port'] ?? 587);
        $smtp_user = $_POST['smtp_user'] ?? '';
        $smtp_pass = $_POST['smtp_pass'] ?? '';
        $personal_limit = (int)($_POST['personal_hourly_limit'] ?? 50);
        $ai_enabled = isset($_POST['ai_enabled']) ? 1 : 0;

        $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $enc_openai = !empty($openai_key) ? Crypto::encrypt($openai_key) : ($existing['openai_api_key'] ?? null);
        $enc_gemini = !empty($gemini_key) ? Crypto::encrypt($gemini_key) : ($existing['gemini_api_key'] ?? null);
        $enc_smtp_pass = !empty($smtp_pass) ? Crypto::encrypt($smtp_pass) : ($existing['smtp_pass'] ?? null);

        try {
            $stmt = $db->prepare("INSERT INTO user_settings (user_id, openai_api_key, gemini_api_key, preferred_llm, ai_enabled, smtp_host, smtp_port, smtp_user, smtp_pass, personal_hourly_limit) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE 
                                 openai_api_key = VALUES(openai_api_key), gemini_api_key = VALUES(gemini_api_key), 
                                 preferred_llm = VALUES(preferred_llm), ai_enabled = VALUES(ai_enabled), 
                                 smtp_host = VALUES(smtp_host), 
                                 smtp_port = VALUES(smtp_port), smtp_user = VALUES(smtp_user), 
                                 smtp_pass = VALUES(smtp_pass), personal_hourly_limit = VALUES(personal_hourly_limit)");
            $stmt->execute([$user_id, $enc_openai, $enc_gemini, $preferred_llm, $ai_enabled, $smtp_host, $smtp_port, $smtp_user, $enc_smtp_pass, $personal_limit]);
            $message = "System preferences updated successfully.";
        } catch (PDOException $e) { $error = "Update failed: " . $e->getMessage(); }
    }
}

$stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 1040; transition: all 0.3s; }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); }
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; transition: 0.2s; }
        .nav-link i { margin-right: 12px; font-size: 1.1rem; width: 24px; text-align: center; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .nav-link.active { background: var(--primary) !important; color: #fff !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        #content { margin-left: var(--sidebar-width); padding: 3rem; min-height: 100vh; transition: all 0.3s; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2.5rem; }
        .form-label { font-weight: 600; color: #475569; }
        .form-control, .form-select { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: 0.8rem 2rem; font-weight: 700; }
        .btn-outline-secondary { border-radius: 12px; padding: 0.8rem 2rem; border: 2px solid #e2e8f0; font-weight: 600; }

        @media (max-width: 992px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); transition: all 0.3s; z-index: 1040; }
            #sidebar.mobile-show { margin-left: 0; }
            #content { margin-left: 0 !important; padding: 1.5rem !important; }
            .sidebar-close { display: block !important; }
            .glass-card { padding: 1.5rem !important; }
        }
        .sidebar-close {
            display: none; position: absolute; right: 1rem; top: 1.5rem;
            font-size: 1.5rem; color: #94a3b8; cursor: pointer; padding: 0.5rem; z-index: 1050;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <div class="sidebar-close"><i class="fas fa-times"></i></div>
        <a href="dashboard.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu mt-2">
            <a href="dashboard.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="campaigns.php" class="nav-link"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-id-card"></i> Profile</a>
            <a href="logs.php" class="nav-link"><i class="fas fa-list-ul"></i> Logs</a>
            <a href="settings.php" class="nav-link active"><i class="fas fa-gear"></i> Settings</a>
            <?php if ($user_role === 'admin'): ?><a href="admin/users.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a><?php endif; ?>
            <a href="logout.php" class="nav-link text-danger mt-5"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="d-lg-none p-3 bg-white border-bottom mb-4 d-flex align-items-center justify-content-between">
            <button id="toggleSidebar" class="btn btn-light"><i class="fas fa-bars"></i></button>
            <h6 class="mb-0 fw-bold">AI Mailer</h6>
        </div>
        <div class="glass-card">
            <h4 class="fw-bold mb-4 text-dark">System Configurations</h4>
            <?php if($message): ?><div class="alert alert-success rounded-4 border-0 shadow-sm"><?php echo $message; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="row g-4 mb-5">
                    <div class="col-12"><h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2"><i class="fas fa-robot me-2"></i>AI API Credentials</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">OpenAI API Key</label>
                        <input type="password" name="openai_api_key" class="form-control" placeholder="<?php echo !empty($settings['openai_api_key']) ? '••••••••••••' : 'Enter OpenAI Key'; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gemini API Key</label>
                        <input type="password" name="gemini_api_key" class="form-control" placeholder="<?php echo !empty($settings['gemini_api_key']) ? '••••••••••••' : 'Enter Gemini Key'; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default Generation Model</label>
                        <select name="preferred_llm" class="form-select">
                            <option value="openai" <?php echo ($settings['preferred_llm'] ?? '') === 'openai' ? 'selected' : ''; ?>>OpenAI (GPT-5)</option>
                            <option value="gemini" <?php echo ($settings['preferred_llm'] ?? '') === 'gemini' ? 'selected' : ''; ?>>Gemini (2.5 Flash)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-12"><h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2"><i class="fas fa-server me-2"></i>SMTP Mail Server</h6></div>
                    <div class="col-md-8">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? 587); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username / Email</label>
                        <input type="text" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="<?php echo !empty($settings['smtp_pass']) ? '••••••••••••' : 'Enter Password'; ?>">
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-12"><h6 class="fw-bold text-muted text-uppercase small border-bottom pb-2"><i class="fas fa-envelope-circle-check me-2"></i>Sending Preferences</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Personal Hourly Limit</label>
                        <input type="number" name="personal_hourly_limit" class="form-control" value="<?php echo htmlspecialchars($settings['personal_hourly_limit'] ?? 50); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Bulk AI Generation</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="ai_enabled" id="ai_enabled" style="width: 3rem; height: 1.5rem;" <?php echo ($settings['ai_enabled'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label ms-2 pt-1 fw-500" for="ai_enabled">Enable AI for automated queue</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="button" id="testSmtpBtn" class="btn btn-outline-secondary btn-action border-2 fw-bold"><i class="fas fa-plug-circle-check me-2"></i>Test Connection</button>
                    <button type="submit" class="btn btn-primary shadow px-5 py-3"><i class="fas fa-save me-2"></i>Save Preferences</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#toggleSidebar, .sidebar-close').on('click', function() {
        $('#sidebar').toggleClass('mobile-show');
    });
    $('#testSmtpBtn').on('click', function() {
        var btn = $(this);
        var formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            smtp_host: $('input[name="smtp_host"]').val(),
            smtp_port: $('input[name="smtp_port"]').val(),
            smtp_user: $('input[name="smtp_user"]').val(),
            smtp_pass: $('input[name="smtp_pass"]').val()
        };
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Testing...');
        $.post('api/test_smtp.php', formData, function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-plug-circle-check me-2"></i>Test Connection');
            alert(res.success ? res.message : 'Error: ' + res.error);
        }, 'json');
    });
});
</script>
</body>
</html>
