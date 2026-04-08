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
                $message = "Campaign settings saved successfully.";
            } catch (PDOException $e) { $error = "Database error: " . $e->getMessage(); }
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
        
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; }
        
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; }
        .nav-link i { margin-right: 12px; width: 24px; text-align: center; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.08); }
        .nav-link.active { background: var(--primary); }

        #content { margin-left: var(--sidebar-width); padding: 3rem; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2rem; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: 0.8rem 2rem; font-weight: 700; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <a href="index.php" class="sidebar-brand"><i class="fas fa-mailbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><i class="fas fa-grid-2"></i> Dashboard</a>
            <a href="campaigns.php" class="nav-link active"><i class="fas fa-bolt"></i> Campaigns</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-sliders"></i> Settings</a>
            <?php if ($user_role === 'admin'): ?><a href="admin/users.php" class="nav-link"><i class="fas fa-shield-halved"></i> Admin Panel</a><?php endif; ?>
            <a href="logout.php" class="nav-link text-danger mt-5"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="glass-card">
            <h4 class="fw-bold mb-4 text-dark">Campaign Settings</h4>
            <?php if($message): ?><div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><?php echo $message; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($campaign['attachment_path'] ?? ''); ?>">

                <div class="mb-4">
                    <label class="form-label">Campaign Identity Name</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($campaign['title'] ?? 'My Outreach'); ?>" placeholder="e.g. Senior Developer Application">
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="p-4 bg-light rounded-4 border-0 h-100">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-robot me-2"></i>AI Generation Prompt</h6>
                            <textarea name="base_prompt" class="form-control" rows="8" placeholder="Enter your instructions for the AI..."><?php echo htmlspecialchars($campaign['base_prompt'] ?? ''); ?></textarea>
                            <div class="mt-2 small text-muted">GPT-5 / Gemini 2.5 will use this context.</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-4 bg-light rounded-4 border-0 h-100">
                            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-keyboard me-2"></i>Manual Templates</h6>
                            <div class="mb-3">
                                <label class="form-label small">Subject</label>
                                <input type="text" name="subject_template" class="form-control" value="<?php echo htmlspecialchars($campaign['subject_template'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">Body</label>
                                <textarea name="body_template" class="form-control" rows="4"><?php echo htmlspecialchars($campaign['body_template'] ?? ''); ?></textarea>
                            </div>
                            <div class="small text-muted">Use <code>[contact_name]</code>, <code>[company]</code> for variables.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-4 border rounded-4 bg-white">
                    <h6 class="fw-bold mb-3"><i class="fas fa-file-pdf me-2 text-danger"></i>Attachment (Optional)</h6>
                    <div class="d-flex align-items-center gap-3">
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.docx">
                        <?php if(!empty($campaign['attachment_path'])): ?>
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-3 border border-success border-opacity-25">
                                <i class="fas fa-paperclip me-1"></i> <?php echo basename($campaign['attachment_path']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn btn-primary shadow px-5 py-3">Update Campaign Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
