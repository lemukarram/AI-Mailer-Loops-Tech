<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$user_role = $_SESSION['user_role'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $title = $_POST['title'] ?? 'Default Campaign';
        $base_prompt = $_POST['base_prompt'] ?? '';
        $subject_template = $_POST['subject_template'] ?? '';
        $body_template = $_POST['body_template'] ?? '';
        $footer_template = $_POST['footer_template'] ?? '';
        $attachment_path = $_POST['existing_attachment'] ?? '';

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'docx'])) {
                $filename = "attach_" . $user_id . "_" . time() . "." . $ext;
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);
                $attachment_path = 'uploads/' . $filename;
            } else { $error = "Only PDF and DOCX attachments are allowed."; }
        }

        if (!$error) {
            try {
                $stmt = $db->prepare("INSERT INTO campaigns (user_id, title, base_prompt, subject_template, body_template, footer_template, attachment_path) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)
                                     ON DUPLICATE KEY UPDATE 
                                     title=VALUES(title), base_prompt=VALUES(base_prompt), 
                                     subject_template=VALUES(subject_template), body_template=VALUES(body_template), 
                                     footer_template=VALUES(footer_template), attachment_path=VALUES(attachment_path)");
                $stmt->execute([$user_id, $title, $base_prompt, $subject_template, $body_template, $footer_template, $attachment_path]);
                $message = "Campaign configuration synced successfully.";
            } catch (PDOException $e) { $error = "Sync failed: " . $e->getMessage(); }
        }
    }
}

$stmt = $db->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #334155; }
        
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); }
        
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; transition: 0.2s; }
        .nav-link i { margin-right: 12px; width: 24px; text-align: center; font-size: 1.1rem; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .nav-link.active { background: var(--primary) !important; color: #fff !important; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        #content { margin-left: var(--sidebar-width); padding: 3rem; min-height: 100vh; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2.5rem; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: 0.8rem 2.5rem; font-weight: 700; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        
        .section-tag { background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.4rem 1rem; border-radius: 8px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <a href="index.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu mt-2">
            <a href="index.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="campaigns.php" class="nav-link active"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-gear"></i> Settings</a>
            <?php if ($user_role === 'admin'): ?><a href="admin/users.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a><?php endif; ?>
            <a href="logout.php" class="nav-link text-danger mt-5"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h3 class="fw-bold m-0 text-dark">Campaign Engine</h3>
                    <p class="text-muted small mb-0">Define how AI and Manual templates should behave.</p>
                </div>
                <i class="fas fa-wand-sparkles fs-2 text-primary opacity-25"></i>
            </div>

            <?php if($message): ?>
                <div class="alert alert-success border-0 rounded-4 shadow-sm px-4 py-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-circle-check fs-5 me-3"></i><div><?php echo $message; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($campaign['attachment_path'] ?? ''); ?>">

                <div class="mb-5">
                    <label class="form-label">Campaign Identity Name</label>
                    <input type="text" name="title" class="form-control form-control-lg" value="<?php echo htmlspecialchars($campaign['title'] ?? 'My Outreach'); ?>" placeholder="e.g. Outreach 2024">
                </div>

                <div class="row g-5">
                    <div class="col-lg-6">
                        <div class="p-4 bg-light rounded-4 border-0 h-100 position-relative">
                            <span class="section-tag">AI Powered</span>
                            <h6 class="fw-bold mb-3">AI Context Prompt</h6>
                            <textarea name="base_prompt" class="form-control bg-white" rows="10" placeholder="e.g. Write a soft, humanized outreach email..."><?php echo htmlspecialchars($campaign['base_prompt'] ?? ''); ?></textarea>
                            <p class="mt-3 small text-muted"><i class="fas fa-info-circle me-1"></i> GPT-5 / Gemini 2.5 Flash will use this context to generate unique bodies.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-4 bg-light rounded-4 border-0 h-100">
                            <span class="section-tag">Direct Control</span>
                            <h6 class="fw-bold mb-3">Manual Template System</h6>
                            <div class="mb-3">
                                <label class="form-label small">Subject Template</label>
                                <input type="text" name="subject_template" class="form-control bg-white" value="<?php echo htmlspecialchars($campaign['subject_template'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Message Body Template</label>
                                <textarea name="body_template" class="form-control bg-white" rows="5"><?php echo htmlspecialchars($campaign['body_template'] ?? ''); ?></textarea>
                            </div>
                            <div class="p-3 bg-white rounded-3 small">
                                <span class="fw-bold text-secondary">Injectable Variables:</span><br>
                                <code>[contact_name]</code>, <code>[email]</code>, <code>[company]</code>, <code>[designation]</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 p-4 border rounded-4 bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="fas fa-paperclip me-2 text-primary"></i>Master Attachment</h6>
                        <p class="text-muted small mb-0">PDF or DOCX only. Will be attached to all emails in this campaign.</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if(!empty($campaign['attachment_path'])): ?>
                            <div class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 rounded-3">
                                <i class="fas fa-file-pdf me-1"></i> <?php echo basename($campaign['attachment_path']); ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="attachment" class="form-control" style="width: auto;" accept=".pdf,.docx">
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-primary shadow-lg">
                        <i class="fas fa-cloud-arrow-up me-2"></i> Sync Engine Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
