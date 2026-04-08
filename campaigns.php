<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

// Handle Campaign Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $title = $_POST['title'] ?? 'Default Campaign';
        $base_prompt = $_POST['base_prompt'] ?? '';
        $subject_template = $_POST['subject_template'] ?? '';
        $body_template = $_POST['body_template'] ?? '';
        $footer_template = $_POST['footer_template'] ?? '';
        
        $attachment_path = $_POST['existing_attachment'] ?? '';

        // Handle File Attachment
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'docx'])) {
                $filename = "attach_" . $user_id . "_" . time() . "." . $ext;
                if (!is_dir('uploads')) mkdir('uploads', 0755, true);
                move_uploaded_file($file['tmp_name'], 'uploads/' . $filename);
                $attachment_path = 'uploads/' . $filename;
            } else {
                $error = "Only PDF and DOCX attachments are allowed.";
            }
        }

        if (!$error) {
            try {
                // We keep one active campaign per user for simplicity, or we could insert new
                $stmt = $db->prepare("INSERT INTO campaigns (user_id, title, base_prompt, subject_template, body_template, footer_template, attachment_path) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)
                                     ON DUPLICATE KEY UPDATE 
                                     title=VALUES(title), base_prompt=VALUES(base_prompt), 
                                     subject_template=VALUES(subject_template), body_template=VALUES(body_template), 
                                     footer_template=VALUES(footer_template), attachment_path=VALUES(attachment_path)");
                $stmt->execute([$user_id, $title, $base_prompt, $subject_template, $body_template, $footer_template, $attachment_path]);
                $message = "Campaign settings saved successfully.";
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch current campaign
$stmt = $db->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campaign Settings - aiMailSaas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">Campaign Configuration</h5>
                    <a href="index.php" class="btn btn-sm btn-light">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="existing_attachment" value="<?php echo htmlspecialchars($campaign['attachment_path'] ?? ''); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Campaign Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($campaign['title'] ?? 'My Outreach'); ?>">
                        </div>

                        <div class="row">
                            <!-- AI Mode -->
                            <div class="col-md-6 border-end">
                                <h6 class="text-primary border-bottom pb-2">AI Generation Prompt (AI Mode)</h6>
                                <p class="small text-muted">This prompt will be combined with contact data to generate unique emails.</p>
                                <textarea name="base_prompt" class="form-control" rows="8" placeholder="e.g. Write a short email to hire me as a developer..."><?php echo htmlspecialchars($campaign['base_prompt'] ?? ''); ?></textarea>
                            </div>

                            <!-- Manual Mode -->
                            <div class="col-md-6">
                                <h6 class="text-secondary border-bottom pb-2">Templates (Manual Mode / Preview)</h6>
                                <p class="small text-muted">Use variables like <code>[contact_name]</code>, <code>[company]</code>, <code>[designation]</code>.</p>
                                <div class="mb-2">
                                    <label class="form-label small">Subject Template</label>
                                    <input type="text" name="subject_template" class="form-control form-control-sm" value="<?php echo htmlspecialchars($campaign['subject_template'] ?? ''); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Body Template</label>
                                    <textarea name="body_template" class="form-control form-control-sm" rows="4"><?php echo htmlspecialchars($campaign['body_template'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Footer Template</label>
                                    <input type="text" name="footer_template" class="form-control form-control-sm" value="<?php echo htmlspecialchars($campaign['footer_template'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="border-bottom pb-2">Global Attachment (PDF/DOCX)</h6>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.docx">
                            <?php if(!empty($campaign['attachment_path'])): ?>
                                <div class="mt-2 small text-success">Current: <?php echo basename($campaign['attachment_path']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Save Campaign & Templates</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
