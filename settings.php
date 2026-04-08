<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Crypto.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

// Handle Settings Update
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

        // Fetch existing settings
        $stmt = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing_settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Encrypt passwords if they are provided, otherwise keep existing
        $enc_openai = !empty($openai_key) ? Crypto::encrypt($openai_key) : ($existing_settings['openai_api_key'] ?? null);
        $enc_gemini = !empty($gemini_key) ? Crypto::encrypt($gemini_key) : ($existing_settings['gemini_api_key'] ?? null);
        $enc_smtp_pass = !empty($smtp_pass) ? Crypto::encrypt($smtp_pass) : ($existing_settings['smtp_pass'] ?? null);

        try {
            $stmt = $db->prepare("INSERT INTO user_settings (user_id, openai_api_key, gemini_api_key, preferred_llm, smtp_host, smtp_port, smtp_user, smtp_pass, personal_hourly_limit) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE 
                                 openai_api_key = VALUES(openai_api_key), 
                                 gemini_api_key = VALUES(gemini_api_key), 
                                 preferred_llm = VALUES(preferred_llm), 
                                 smtp_host = VALUES(smtp_host), 
                                 smtp_port = VALUES(smtp_port), 
                                 smtp_user = VALUES(smtp_user), 
                                 smtp_pass = VALUES(smtp_pass), 
                                 personal_hourly_limit = VALUES(personal_hourly_limit)");
            $stmt->execute([$user_id, $enc_openai, $enc_gemini, $preferred_llm, $smtp_host, $smtp_port, $smtp_user, $enc_smtp_pass, $personal_limit]);
            $message = "Settings updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating settings: " . $e->getMessage();
        }
    } else {
        $error = "Invalid CSRF token.";
    }
}

// Fetch current settings
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
    <title>Settings - aiMailSaas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">User Settings</h5>
                    <a href="index.php" class="btn btn-sm btn-light">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <h6 class="border-bottom pb-2 mb-3">AI Configuration</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">OpenAI API Key</label>
                                <input type="password" name="openai_api_key" class="form-control" placeholder="<?php echo !empty($settings['openai_api_key']) ? '******** (leave empty to keep)' : 'Enter key'; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gemini API Key</label>
                                <input type="password" name="gemini_api_key" class="form-control" placeholder="<?php echo !empty($settings['gemini_api_key']) ? '******** (leave empty to keep)' : 'Enter key'; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred LLM</label>
                            <select name="preferred_llm" class="form-select">
                                <option value="openai" <?php echo ($settings['preferred_llm'] ?? '') === 'openai' ? 'selected' : ''; ?>>OpenAI (GPT-5)</option>
                                <option value="gemini" <?php echo ($settings['preferred_llm'] ?? '') === 'gemini' ? 'selected' : ''; ?>>Gemini (2.5 Flash)</option>
                            </select>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">SMTP Configuration</h6>
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? 587); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="smtp_user" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" name="smtp_pass" class="form-control" placeholder="<?php echo !empty($settings['smtp_pass']) ? '******** (leave empty to keep)' : 'Enter password'; ?>">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4">Limits & Queue</h6>
                        <div class="mb-3">
                            <label class="form-label">Personal Hourly Send Limit</label>
                            <input type="number" name="personal_hourly_limit" class="form-control" value="<?php echo htmlspecialchars($settings['personal_hourly_limit'] ?? 50); ?>">
                            <div class="form-text">Cannot exceed the global limit set by the administrator.</div>
                        </div>

                        <div class="d-grid mt-4 gap-2">
                            <button type="button" id="testSmtpBtn" class="btn btn-outline-info">Test SMTP Connection</button>
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#testSmtpBtn').on('click', function() {
        var btn = $(this);
        var formData = {
            csrf_token: $('input[name="csrf_token"]').val(),
            smtp_host: $('input[name="smtp_host"]').val(),
            smtp_port: $('input[name="smtp_port"]').val(),
            smtp_user: $('input[name="smtp_user"]').val(),
            smtp_pass: $('input[name="smtp_pass"]').val()
        };

        btn.prop('disabled', true).text('Testing connection...');

        $.post('api/test_smtp.php', formData, function(res) {
            btn.prop('disabled', false).text('Test SMTP Connection');
            if(res.success) {
                alert(res.message);
            } else {
                alert('Error: ' + res.error);
            }
        }, 'json');
    });
});
</script>

</body>
</html>
