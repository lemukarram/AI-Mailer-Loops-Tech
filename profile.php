<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$user_role = $_SESSION['user_role'];
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT purpose FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$purpose = $stmt->fetchColumn() ?: 'job_hunt';

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $linkedin_url = $_POST['linkedin_url'] ?? '';
        $website_url = $_POST['website_url'] ?? '';
        $company_name = $_POST['company_name'] ?? '';
        $designation = $_POST['designation'] ?? '';
        $other_info = $_POST['other_info'] ?? '';
        $resume_text = $_POST['resume_text'] ?? '';
        $business_profile_text = $_POST['business_profile_text'] ?? '';

        try {
            $stmt = $db->prepare("INSERT INTO user_profiles (user_id, full_name, phone, linkedin_url, website_url, company_name, designation, other_info, resume_text, business_profile_text) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                 ON DUPLICATE KEY UPDATE 
                                 full_name=VALUES(full_name), phone=VALUES(phone), 
                                 linkedin_url=VALUES(linkedin_url), website_url=VALUES(website_url), 
                                 company_name=VALUES(company_name), designation=VALUES(designation), 
                                 other_info=VALUES(other_info), resume_text=VALUES(resume_text), 
                                 business_profile_text=VALUES(business_profile_text)");
            $stmt->execute([$user_id, $full_name, $phone, $linkedin_url, $website_url, $company_name, $designation, $other_info, $resume_text, $business_profile_text]);
            $message = "Profile updated successfully.";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

$stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #334155; }
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 1040; transition: all 0.3s; }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; }
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; transition: 0.2s; }
        .nav-link i { margin-right: 12px; width: 24px; text-align: center; font-size: 1.1rem; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .nav-link.active { background: var(--primary) !important; color: #fff !important; }
        #content { margin-left: var(--sidebar-width); padding: 3rem; min-height: 100vh; transition: all 0.3s; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2.5rem; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: 0.8rem 2rem; font-weight: 700; }

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
        <a href="index.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu mt-2">
            <a href="index.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="campaigns.php" class="nav-link"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="profile.php" class="nav-link active"><i class="fas fa-id-card"></i> Profile</a>
            <a href="logs.php" class="nav-link"><i class="fas fa-list-ul"></i> Logs</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-gear"></i> Settings</a>
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
            <h3 class="fw-bold mb-2"><?php echo $purpose === 'job_hunt' ? 'Professional Persona' : 'Business Identity'; ?></h3>
            <p class="text-muted mb-5">These details will be passed to the AI to help write highly personalized emails from you.</p>

            <?php if($message): ?><div class="alert alert-success border-0 rounded-4 shadow-sm p-3 mb-4"><?php echo $message; ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger border-0 rounded-4 shadow-sm p-3 mb-4"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Name / Business Owner</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" placeholder="John Doe">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?php echo $purpose === 'job_hunt' ? 'Designation / Profession' : 'Business Role / Primary Service'; ?></label>
                        <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($profile['designation'] ?? ''); ?>" placeholder="Senior Software Architect">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" placeholder="+1 234 567 890">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" placeholder="Tech Solutions Inc.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website URL</label>
                        <input type="url" name="website_url" class="form-control" value="<?php echo htmlspecialchars($profile['website_url'] ?? ''); ?>" placeholder="https://yourwebsite.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Other Information (Skills, Context, Bio)</label>
                        <textarea name="other_info" class="form-control" rows="4" placeholder="Briefly describe your expertise or what you are looking for..."><?php echo htmlspecialchars($profile['other_info'] ?? ''); ?></textarea>
                    </div>

                    <?php if ($purpose === 'job_hunt'): ?>
                        <div class="col-12">
                            <label class="form-label text-primary"><i class="fas fa-file-lines me-2"></i>Extracted Resume Context</label>
                            <textarea name="resume_text" class="form-control bg-light" rows="6"><?php echo htmlspecialchars($profile['resume_text'] ?? ''); ?></textarea>
                            <div class="form-text small">This is the text extracted from your resume and passed to the AI agent.</div>
                        </div>
                    <?php else: ?>
                        <div class="col-12">
                            <label class="form-label text-primary"><i class="fas fa-building-user me-2"></i>Extracted Business Profile Context</label>
                            <textarea name="business_profile_text" class="form-control bg-light" rows="6"><?php echo htmlspecialchars($profile['business_profile_text'] ?? ''); ?></textarea>
                            <div class="form-text small">This is the text extracted from your business profile and passed to the AI agent.</div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Profile Details
                        </button>
                    </div>
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
});
</script>
</body>
</html>
