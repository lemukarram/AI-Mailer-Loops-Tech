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

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - aiMailSaas</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --sidebar-width: 260px;
            --bg-color: #f3f4f6;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color);
            color: #1f2937;
        }

        /* Layout */
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        
        /* Sidebar */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: #111827;
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
            z-index: 1000;
        }
        
        #sidebar.active { margin-left: calc(-1 * var(--sidebar-width)); }
        
        .sidebar-header { padding: 2rem 1.5rem; background: #111827; }
        .sidebar-header h4 { font-weight: 700; letter-spacing: -0.5px; color: #6366f1; }
        
        #sidebar ul.components { padding: 0 1rem; }
        #sidebar ul li a {
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: 0.2s;
        }
        
        #sidebar ul li a i { margin-right: 12px; width: 20px; text-align: center; }
        #sidebar ul li a:hover, #sidebar ul li.active > a {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        #sidebar ul li.active > a { background: var(--primary-gradient); color: #fff; }

        /* Main Content */
        #content { width: 100%; padding: 0; min-height: 100vh; transition: all 0.3s; }
        .top-navbar { background: #fff; padding: 1rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .page-content { padding: 2rem; }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 1rem;
            padding: 1.5rem;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .stat-card.bg-total { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
        .stat-card.bg-sent { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-card.bg-queue { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-card i {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.2;
        }

        /* Table UI */
        .card-table { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .table thead th { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 1rem; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: #6b7280; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); position: absolute; }
            #sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0; }
            .stat-card { margin-bottom: 1rem; }
        }
        
        .btn-modern { border-radius: 0.5rem; padding: 0.6rem 1.2rem; font-weight: 500; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-paper-plane me-2"></i>aiMailSaas</h4>
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="index.php"><i class="fas fa-th-large"></i> Dashboard</a>
            </li>
            <li>
                <a href="campaigns.php"><i class="fas fa-bullhorn"></i> Campaigns</a>
            </li>
            <li>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            </li>
            <?php if ($user_role === 'admin'): ?>
                <li>
                    <a href="admin/users.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
                </li>
            <?php endif; ?>
            <li class="mt-5">
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg top-navbar">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-light me-3">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-text fw-bold d-none d-md-block">
                    Welcome back, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                </span>
                
                <div class="ms-auto d-flex gap-2">
                    <button id="toggleQueueBtn" class="btn btn-modern <?php echo $queue_status === 'started' ? 'btn-danger' : 'btn-success'; ?>">
                        <i class="fas <?php echo $queue_status === 'started' ? 'fa-stop-circle' : 'fa-play-circle'; ?> me-2"></i>
                        <?php echo $queue_status === 'started' ? 'Stop Queue' : 'Start Queue'; ?>
                    </button>
                    <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-file-upload me-2"></i> Upload CSV
                    </button>
                </div>
            </div>
        </nav>

        <div class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Analytics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card bg-total">
                        <small class="opacity-75 text-uppercase fw-bold">Total Contacts</small>
                        <h2 class="mb-0 mt-1"><?php echo $total_list; ?></h2>
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-sent">
                        <small class="opacity-75 text-uppercase fw-bold">Emails Sent</small>
                        <h2 class="mb-0 mt-1"><?php echo $sent_count; ?></h2>
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-queue">
                        <small class="opacity-75 text-uppercase fw-bold">In Queue</small>
                        <h2 class="mb-0 mt-1"><?php echo $unsent_count; ?></h2>
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card card-table overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Mailing List</h5>
                    </div>
                    <table id="mailingTable" class="table table-hover w-100">
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
</div>

<!-- Modals -->
<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="upload.php" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Import Mailing List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-warning border-0 small">
                    <i class="fas fa-exclamation-triangle me-2"></i> New upload will clear your existing list.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Choose CSV File</label>
                    <input type="file" name="excel_file" class="form-control rounded-3" accept=".csv" required>
                </div>
                <div class="bg-light p-3 rounded-3 small">
                    <p class="mb-1 fw-bold text-muted">Required Format:</p>
                    <code>contact name</code>, <code>email</code>, <code>company</code>, <code>designation</code>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" class="btn btn-primary btn-modern w-100">Upload & Replace List</button>
            </div>
        </form>
    </div>
</div>

<!-- AI/Manual Review Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 rounded-top-4">
                <h5 class="modal-title"><i class="fas fa-envelope-open-text me-2"></i> Review & Send</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="current_contact_id">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Recipient</label>
                    <input type="text" id="email_to" class="form-control border-0 bg-light rounded-3" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Subject Line</label>
                    <input type="text" id="email_subject" class="form-control rounded-3 border-light shadow-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Email Body</label>
                    <textarea id="email_body" class="form-control rounded-3 border-light shadow-sm" rows="10"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold text-muted small">Footer / Signature</label>
                    <input type="text" id="email_footer" class="form-control rounded-3 border-light shadow-sm">
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4">
                <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Discard</button>
                <button id="sendNowBtn" class="btn btn-primary btn-modern px-5 shadow-sm">
                    <i class="fas fa-paper-plane me-2"></i> Send Instantly
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
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
    });

    var table = $('#mailingTable').DataTable({
        ajax: 'api/mailing_list.php',
        responsive: true,
        order: [[0, 'asc']],
        columns: [
            { data: 'contact_name' },
            { data: 'email' },
            { data: 'company' },
            { 
                data: 'status',
                render: function(data) {
                    let badge = data === 'sent' ? 'bg-success' : (data === 'failed' ? 'bg-danger' : 'bg-secondary');
                    return `<span class="badge rounded-pill ${badge} px-3 py-2">${data}</span>`;
                }
            },
            { 
                data: null,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-info text-white generate-ai-btn" title="AI Generate" data-id="${row.id}"><i class="fas fa-magic"></i> AI</button>
                            <button class="btn btn-sm btn-primary review-manual-btn" title="Manual Review" data-id="${row.id}"><i class="fas fa-edit"></i> Review</button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search contacts...",
            lengthMenu: "_MENU_ per page",
        }
    });

    // Handle AI Generation
    $(document).on('click', '.generate-ai-btn', function() {
        var id = $(this).data('id');
        var btn = $(this);
        var oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post('api/generate_ai.php', { id: id, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            btn.prop('disabled', false).html(oldHtml);
            if(res.success) {
                $('#current_contact_id').val(id);
                $('#email_to').val(res.recipient);
                $('#email_subject').val(res.data.subject);
                $('#email_body').val(res.data.body);
                $('#email_footer').val(res.data.footer);
                $('#emailModal').modal('show');
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // Handle Manual Review
    $(document).on('click', '.review-manual-btn', function() {
        var id = $(this).data('id');
        var btn = $(this);
        var oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post('api/prepare_template.php', { id: id, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            btn.prop('disabled', false).html(oldHtml);
            if(res.success) {
                $('#current_contact_id').val(id);
                $('#email_to').val(res.recipient);
                $('#email_subject').val(res.subject);
                $('#email_body').val(res.body);
                $('#email_footer').val(res.footer);
                $('#emailModal').modal('show');
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // Handle Instant Send
    $('#sendNowBtn').on('click', function() {
        var id = $('#current_contact_id').val();
        var subject = $('#email_subject').val();
        var body = $('#email_body').val();
        var footer = $('#email_footer').val();
        
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');

        $.post('api/send_single.php', { id: id, subject: subject, body: body, footer: footer, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Send Instantly');
            if(res.success) {
                $('#emailModal').modal('hide');
                table.ajax.reload();
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // Handle Queue Toggle
    $('#toggleQueueBtn').on('click', function() {
        var btn = $(this);
        $.post('api/toggle_queue.php', { csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            if(res.success) {
                location.reload();
            } else {
                alert(res.error);
            }
        }, 'json');
    });
});
</script>

</body>
</html>
