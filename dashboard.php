<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$user_role = $_SESSION['user_role'];
$db = Database::getInstance()->getConnection();

// Fetch counters
$stmt = $db->prepare("SELECT COUNT(*) FROM mailing_list WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_list = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM mailing_list WHERE user_id = ? AND status = 'sent'");
$stmt->execute([$user_id]);
$sent_count = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM mailing_list WHERE user_id = ? AND status = 'unsent'");
$stmt->execute([$user_id]);
$unsent_count = $stmt->fetchColumn();

// Fetch queue status
$stmt = $db->prepare("SELECT queue_status FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$queue_status = $stmt->fetchColumn() ?: 'stopped';

// Check wizard status
$stmt = $db->prepare("SELECT wizard_completed, purpose FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$wizard_completed = $user_settings['wizard_completed'] ?? 0;
$purpose = $user_settings['purpose'] ?? 'job_hunt';

if ($wizard_completed === 0) {
    header("Location: wizard.php");
    exit();
}

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Mailer</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --dark: #0f172a;
            --light: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 280px;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f1f5f9;
            color: #334155;
            overflow-x: hidden;
        }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: #fff;
            min-height: 100vh;
            position: fixed;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1040;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        #sidebar.collapsed { margin-left: calc(-1 * var(--sidebar-width)); }
        
        .sidebar-brand { 
            padding: 2.5rem 1.5rem; 
            display: flex; 
            align-items: center; 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: #fff;
            text-decoration: none;
        }
        .sidebar-brand i { 
            background: var(--primary); 
            width: 40px; 
            height: 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 10px; 
            margin-right: 12px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .sidebar-close {
            display: none;
            position: absolute;
            right: 1rem;
            top: 2.5rem;
            font-size: 1.25rem;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.5rem;
            transition: 0.2s;
        }
        .sidebar-close:hover { color: #fff; }
        
        .nav-menu { padding: 0 1rem; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            transition: 0.2s all;
            font-weight: 500;
        }
        .nav-link i { margin-right: 12px; font-size: 1.1rem; width: 24px; text-align: center; }
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }
        .nav-link.active {
            background: var(--primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        .nav-link.text-danger:hover { background: rgba(239, 68, 68, 0.1); }

        /* Content Area */
        #content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
        }
        #content.expanded { margin-left: 0; }

        .top-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Stat Cards */
        .card-stat {
            border: none;
            border-radius: 20px;
            padding: 1.5rem;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .card-stat:hover { transform: translateY(-5px); }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .icon-total { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .icon-sent { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .icon-queue { background: rgba(245, 158, 11, 0.1); color: var(--warning); }

        /* Table Style */
        .data-card {
            background: #fff;
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            padding: 1.5rem;
        }
        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            border: none;
            padding: 1.25rem 1rem;
        }
        .table tbody td { padding: 1.25rem 1rem; border-color: #f1f5f9; }

        /* Buttons */
        .btn-action {
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary { background: var(--primary); border: none; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        /* Responsive */
        @media (max-width: 992px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #sidebar.mobile-show { margin-left: 0; }
            #content { margin-left: 0 !important; }
            .sidebar-close { display: block; }
            .top-bar { padding: 1rem; }
            .p-4.p-lg-5 { padding: 1.5rem !important; }
            .card-stat { padding: 1.25rem; }
            .data-card { padding: 1rem; border-radius: 16px; overflow-x: auto; }
        }
        
        @media (max-width: 576px) {
            .top-bar { flex-wrap: wrap; gap: 0.75rem !important; }
            .top-bar .d-flex.gap-3 { width: 100%; justify-content: space-between; gap: 0.5rem !important; }
            .btn-action { padding: 0.5rem 1rem; font-size: 0.85rem; flex: 1; }
            .stat-icon { width: 44px; height: 44px; font-size: 1.2rem; }
            .fs-3 { font-size: 1.5rem !important; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-close"><i class="fas fa-times"></i></div>
        <a href="dashboard.php" class="sidebar-brand">
            <i class="fas fa-inbox"></i>
            <span>AI Mailer</span>
        </a>

        <div class="nav-menu">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-house"></i> Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="campaigns.php" class="nav-link">
                    <i class="fas fa-bullhorn"></i> Campaigns
                </a>
            </div>
            <div class="nav-item">
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-id-card"></i> Profile
                </a>
            </div>
            <div class="nav-item">
                <a href="logs.php" class="nav-link">
                    <i class="fas fa-list-ul"></i> Logs
                </a>
            </div>
            <div class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-gear"></i> Settings
                </a>
            </div>
            <?php if ($user_role === 'admin'): ?>
                <div class="nav-item">
                    <a href="admin/users.php" class="nav-link">
                        <i class="fas fa-user-shield"></i> Admin Panel
                    </a>
                </div>
            <?php endif; ?>
            <div class="nav-item mt-5">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content" class="flex-grow-1">
        <div class="top-bar d-flex justify-content-between align-items-center">
            <button id="toggleSidebar" class="btn btn-light d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mb-0 fw-bold text-dark d-none d-md-block">Overview</h5>
            
            <div class="d-flex gap-3">
                <button id="toggleQueueBtn" class="btn btn-action <?php echo $queue_status === 'started' ? 'btn-danger' : 'btn-success'; ?>">
                    <i class="fas <?php echo $queue_status === 'started' ? 'fa-pause' : 'fa-play'; ?> me-2"></i>
                    <?php echo $queue_status === 'started' ? 'Stop Queue' : 'Start Queue'; ?>
                </button>
                <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload me-2"></i> Import List
                </button>
            </div>
        </div>

        <div class="p-4 p-lg-5">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 px-4 py-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-circle-check fs-4 me-3"></i>
                    <div><?php echo htmlspecialchars($_GET['msg']); ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="row g-4 mb-5">
                <div class="col-sm-6 col-xl-4">
                    <div class="card-stat">
                        <div class="stat-icon icon-total"><i class="fas fa-<?php echo $purpose === 'job_hunt' ? 'briefcase' : 'building'; ?>"></i></div>
                        <div>
                            <div class="text-secondary small fw-600"><?php echo $purpose === 'job_hunt' ? 'Job Contacts' : 'Business Contacts'; ?></div>
                            <div class="fs-3 fw-bold"><?php echo number_format($total_list); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-stat">
                        <div class="stat-icon icon-sent"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <div class="text-secondary small fw-600"><?php echo $purpose === 'job_hunt' ? 'Applied for Jobs' : 'Business Inquiries'; ?></div>
                            <div class="fs-3 fw-bold"><?php echo number_format($sent_count); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card-stat">
                        <div class="stat-icon icon-queue"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="text-secondary small fw-600">In Queue</div>
                            <div class="fs-3 fw-bold"><?php echo number_format($unsent_count); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Data Table -->
            <div class="data-card">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h5 class="fw-bold m-0 text-dark"><?php echo $purpose === 'job_hunt' ? 'Target Companies' : 'Lead Prospect List'; ?></h5>
                    <div class="text-muted small">Updated just now</div>
                </div>
                <table id="mailingTable" class="table w-100">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="upload.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-5 overflow-hidden">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="modal-header bg-light border-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Import Mailing List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-4 mb-4">
                    <p class="mb-0 small text-warning-emphasis fw-500">
                        <i class="fas fa-triangle-exclamation me-2"></i> Warning: New upload will completely replace your current list.
                    </p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Select CSV File</label>
                    <input type="file" name="excel_file" class="form-control form-control-lg rounded-4 fs-6" accept=".csv" required>
                </div>
                <div class="p-3 bg-light rounded-4">
                    <div class="fw-bold small text-secondary mb-2">Expected CSV Header:</div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-white text-dark border px-3 py-2">contact name</span>
                        <span class="badge bg-white text-dark border px-3 py-2">email</span>
                        <span class="badge bg-white text-dark border px-3 py-2">company</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-primary btn-action w-100 py-3 shadow">Import & Sync List</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-5 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i> Content Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light bg-opacity-50">
                <input type="hidden" id="current_contact_id">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">To Recipient</label>
                    <input type="text" id="email_to" class="form-control border-0 shadow-sm rounded-4 px-4 py-3" readonly>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Subject Line</label>
                    <input type="text" id="email_subject" class="form-control border-0 shadow-sm rounded-4 px-4 py-3">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Email Message Body</label>
                    <textarea id="email_body" class="form-control border-0 shadow-sm rounded-4 px-4 py-3" rows="10"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-secondary">Signature / Footer</label>
                    <input type="text" id="email_footer" class="form-control border-0 shadow-sm rounded-4 px-4 py-3">
                </div>
            </div>
            <div class="modal-footer border-0 p-4 bg-white">
                <button type="button" class="btn btn-light btn-action px-4" data-bs-dismiss="modal">Discard</button>
                <button id="sendNowBtn" class="btn btn-primary btn-action px-5 shadow">
                    <i class="fas fa-paper-plane me-2"></i> Fire Email
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#toggleSidebar, .sidebar-close').on('click', function() {
        $('#sidebar').toggleClass('mobile-show');
    });

    var table = $('#mailingTable').DataTable({
        ajax: 'api/mailing_list.php',
        responsive: true,
        order: [[0, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        columns: [
            { 
                data: 'contact_name',
                render: function(data) {
                    return `<div class="fw-bold text-dark">${data}</div>`;
                }
            },
            { data: 'email' },
            { 
                data: 'company',
                render: function(data) {
                    return data ? `<span class="badge bg-light text-dark border">${data}</span>` : '-';
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    let color = data === 'sent' ? 'success' : (data === 'failed' ? 'danger' : 'secondary');
                    return `<div class="d-flex align-items-center"><span class="bg-${color} rounded-circle me-2" style="width:8px; height:8px;"></span> <span class="text-${color} fw-600 small text-uppercase">${data}</span></div>`;
                }
            },
            { 
                data: null,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-light btn-sm rounded-3 px-3 generate-ai-btn" data-id="${row.id}" title="AI Generate">
                                <i class="fas fa-wand-sparkles text-primary me-1"></i> AI
                            </button>
                            <button class="btn btn-light btn-sm rounded-3 px-3 review-manual-btn" data-id="${row.id}" title="Manual Edit">
                                <i class="fas fa-pen-to-square text-secondary me-1"></i> Edit
                            </button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search contacts...",
            paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
        }
    });

    $(document).on('click', '.generate-ai-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var item = $(this);
        item.html('<i class="fas fa-circle-notch fa-spin me-2"></i> Generating...');

        $.post('api/generate_ai.php', { id: id, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            item.html('<i class="fas fa-wand-sparkles text-primary me-2"></i> AI Generate');
            if(res.success) {
                $('#current_contact_id').val(id);
                $('#email_to').val(res.recipient);
                $('#email_subject').val(res.data.subject);
                $('#email_body').val(res.data.body);
                $('#email_footer').val(res.data.footer);
                $('#emailModal').modal('show');
            } else { alert(res.error); }
        }, 'json');
    });

    $(document).on('click', '.review-manual-btn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.post('api/prepare_template.php', { id: id, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            if(res.success) {
                $('#current_contact_id').val(id);
                $('#email_to').val(res.recipient);
                $('#email_subject').val(res.subject);
                $('#email_body').val(res.body);
                $('#email_footer').val(res.footer);
                $('#emailModal').modal('show');
            } else { alert(res.error); }
        }, 'json');
    });

    $('#sendNowBtn').on('click', function() {
        var id = $('#current_contact_id').val();
        var subject = $('#email_subject').val();
        var body = $('#email_body').val();
        var footer = $('#email_footer').val();
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin me-2"></i> Sending...');

        $.post('api/send_single.php', { id: id, subject: subject, body: body, footer: footer, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Fire Email');
            if(res.success) {
                $('#emailModal').modal('hide');
                table.ajax.reload();
            } else { alert(res.error); }
        }, 'json');
    });

    $('#toggleQueueBtn').on('click', function() {
        var btn = $(this);
        $.post('api/toggle_queue.php', { csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            if(res.success) { location.reload(); } else { alert(res.error); }
        }, 'json');
    });
});
</script>

</body>
</html>
