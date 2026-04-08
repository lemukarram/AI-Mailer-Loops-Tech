<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; color: #fff; padding: 20px; }
        .main-content { padding: 30px; }
        .nav-link { color: #adb5bd; }
        .nav-link:hover { color: #fff; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <h4 class="mb-4">aiMailSaas</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                <?php if ($user_role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin/users.php">Admin Panel</a></li>
                <?php endif; ?>
                <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <!-- Main -->
        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_email']); ?></h2>
                <div class="btn-group">
                    <button id="toggleQueueBtn" class="btn <?php echo $queue_status === 'started' ? 'btn-danger' : 'btn-success'; ?>">
                        <?php echo $queue_status === 'started' ? 'Stop Queue' : 'Start Queue'; ?>
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload CSV</button>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            <?php endif; ?>

            <!-- Analytics -->
            <div class="row mb-4 text-center">
                <div class="col-md-4">
                    <div class="card p-3 bg-white">
                        <small class="text-muted text-uppercase fw-bold">Total Contacts</small>
                        <h3 class="mb-0 mt-2"><?php echo $total_list; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 bg-white">
                        <small class="text-muted text-uppercase fw-bold">Emails Sent</small>
                        <h3 class="mb-0 mt-2 text-success"><?php echo $sent_count; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 bg-white">
                        <small class="text-muted text-uppercase fw-bold">In Queue</small>
                        <h3 class="mb-0 mt-2 text-warning"><?php echo $unsent_count; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <table id="mailingTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="upload.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="modal-header">
                <h5 class="modal-title">Import Mailing List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Important: New upload will clear existing list for your account.</p>
                <div class="mb-3">
                    <label class="form-label">CSV File</label>
                    <input type="file" name="excel_file" class="form-control" accept=".csv" required>
                </div>
                <div class="bg-light p-2 small">
                    Columns: <code>contact name</code>, <code>email</code>, <code>company</code>, <code>designation</code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload & Replace</button>
            </div>
        </form>
    </div>
</div>

<!-- AI View Modal -->
<div class="modal fade" id="aiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">AI Generated Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="current_contact_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject</label>
                    <input type="text" id="ai_subject" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Body</label>
                    <textarea id="ai_body" class="form-control" rows="10"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Footer</label>
                    <input type="text" id="ai_footer" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button id="sendNowBtn" class="btn btn-success">Send Instantly</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#mailingTable').DataTable({
        ajax: 'api/mailing_list.php',
        columns: [
            { data: 'contact_name' },
            { data: 'email' },
            { data: 'company' },
            { 
                data: 'status',
                render: function(data) {
                    let badge = data === 'sent' ? 'bg-success' : (data === 'failed' ? 'bg-danger' : 'bg-secondary');
                    return `<span class="badge ${badge}">${data}</span>`;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-info generate-ai-btn" data-id="${row.id}">AI Generate</button>
                    `;
                }
            }
        ]
    });

    // Handle AI Generation
    $(document).on('click', '.generate-ai-btn', function() {
        var id = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).text('Generating...');

        $.post('api/generate_ai.php', { id: id, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            btn.prop('disabled', false).text('AI Generate');
            if(res.success) {
                $('#current_contact_id').val(id);
                $('#ai_subject').val(res.data.subject);
                $('#ai_body').val(res.data.body);
                $('#ai_footer').val(res.data.footer);
                $('#aiModal').modal('show');
            } else {
                alert(res.error);
            }
        }, 'json');
    });

    // Handle Instant Send
    $('#sendNowBtn').on('click', function() {
        var id = $('#current_contact_id').val();
        var subject = $('#ai_subject').val();
        var body = $('#ai_body').val();
        var footer = $('#ai_footer').val();
        
        $(this).prop('disabled', true).text('Sending...');

        $.post('api/send_single.php', { id: id, subject: subject, body: body, footer: footer, csrf_token: '<?php echo $csrf_token; ?>' }, function(res) {
            $('#sendNowBtn').prop('disabled', false).text('Send Instantly');
            if(res.success) {
                alert('Email sent successfully!');
                $('#aiModal').modal('hide');
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
                if(res.status === 'started') {
                    btn.removeClass('btn-success').addClass('btn-danger').text('Stop Queue');
                } else {
                    btn.removeClass('btn-danger').addClass('btn-success').text('Start Queue');
                }
            } else {
                alert(res.error);
            }
        }, 'json');
    });
});
</script>

</body>
</html>
