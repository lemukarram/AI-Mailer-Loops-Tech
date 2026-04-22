<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

Auth::requireAdmin();
$db = Database::getInstance()->getConnection();
$user_role = $_SESSION['user_role'];

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_limits'])) {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $stmt = $db->prepare("UPDATE admin_limits SET max_emails_per_hour = ?, total_max_emails = ?, max_file_upload_size = ?, max_excel_rows = ?, master_gemini_key = ? WHERE id = 1");
        $stmt->execute([$_POST['max_emails_per_hour'], $_POST['total_max_emails'], $_POST['max_file_upload_size'], $_POST['max_excel_rows'], $_POST['master_gemini_key']]);
        $message = "Global platform limits updated.";
    }
}

$limits = $db->query("SELECT * FROM admin_limits LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 1040; transition: all 0.3s; }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; }
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; transition: 0.2s; }
        .nav-link.active { background: var(--primary) !important; color: #fff !important; }
        #content { margin-left: var(--sidebar-width); padding: 3rem; transition: all 0.3s; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2rem; }
        .form-control { border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; font-weight: 700; }
        .table thead th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1.25rem 1rem; border: none; }

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
        /* Custom Table Styling */
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <div class="sidebar-close"><i class="fas fa-times"></i></div>
        <a href="../index.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu">
            <a href="../index.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="../campaigns.php" class="nav-link"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="../logs.php" class="nav-link"><i class="fas fa-list-ul"></i> My Logs</a>
            <a href="../settings.php" class="nav-link"><i class="fas fa-gear"></i> Settings</a>
            <a href="users.php" class="nav-link active"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <a href="logs.php" class="nav-link"><i class="fas fa-terminal"></i> System Logs</a>
            <a href="../logout.php" class="nav-link text-danger mt-5"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="d-lg-none p-3 bg-white border-bottom mb-4 d-flex align-items-center justify-content-between">
            <button id="toggleSidebar" class="btn btn-light"><i class="fas fa-bars"></i></button>
            <h6 class="mb-0 fw-bold">AI Mailer</h6>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0 text-dark">Platform Governance</h3>
            <span class="badge bg-dark rounded-pill px-3 py-2">Master Admin</span>
        </div>

        <?php if($message): ?><div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><?php echo $message; ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card">
                    <h6 class="fw-bold mb-4"><i class="fas fa-sliders me-2"></i>Global Limits</h6>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="update_limits" value="1">
                        <div class="mb-3">
                            <label class="form-label small">Max Emails / Hour</label>
                            <input type="number" name="max_emails_per_hour" class="form-control" value="<?php echo $limits['max_emails_per_hour']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Total Max Emails</label>
                            <input type="number" name="total_max_emails" class="form-control" value="<?php echo $limits['total_max_emails']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Max Rows / Upload</label>
                            <input type="number" name="max_excel_rows" class="form-control" value="<?php echo $limits['max_excel_rows']; ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-primary">Master Gemini Key</label>
                            <input type="password" name="master_gemini_key" class="form-control border-primary border-opacity-25" value="<?php echo htmlspecialchars($limits['master_gemini_key'] ?? ''); ?>" placeholder="Enter Gemini Pro Key">
                            <div class="form-text small">Used for AI-assisted profile setup and prompt generation.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 mt-2">Update Parameters</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card p-0 overflow-hidden">
                    <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0"><i class="fas fa-users me-2"></i>User Management</h6>
                        <div id="bulkActions" class="d-none animate__animated animate__fadeIn">
                            <div class="d-flex gap-2">
                                <select id="bulkActionSelect" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">Bulk Actions...</option>
                                    <option value="active">Set Active</option>
                                    <option value="inactive">Set Inactive</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                                <button id="applyBulkAction" class="btn btn-primary btn-sm px-3">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 table-responsive">
                        <table id="usersTable" class="table w-100">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                    <th>Account</th>
                                    <th>Role</th>
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    const csrf_token = '<?php echo $csrf_token; ?>';

    $('#toggleSidebar, .sidebar-close').on('click', function() {
        $('#sidebar').toggleClass('mobile-show');
    });

    const table = $('#usersTable').DataTable({
        ajax: '../api/admin_users.php',
        responsive: true,
        order: [[1, 'desc']],
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    if (row.role === 'admin') return '';
                    return `<input type="checkbox" class="form-check-input user-checkbox" value="${row.id}">`;
                }
            },
            { 
                data: 'email',
                render: function(data, type, row) {
                    const date = new Date(row.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    return `<div class="fw-bold">${data}</div><small class="text-muted">Joined ${date}</small>`;
                }
            },
            { 
                data: 'role',
                render: function(data) {
                    return `<span class="badge bg-secondary-subtle text-secondary px-3">${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const color = data === 'active' ? 'success' : 'danger';
                    return `<span class="badge bg-${color} rounded-pill px-3">${data.toUpperCase()}</span>`;
                }
            },
            { 
                data: null,
                className: 'text-end',
                render: function(data, type, row) {
                    if (row.role === 'admin') return '';
                    return `
                        <button class="btn btn-sm btn-light border rounded-3 px-3 toggle-status" data-id="${row.id}" data-status="${row.status}">
                            <i class="fas fa-repeat me-1"></i> Toggle
                        </button>
                    `;
                }
            }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search users...",
            paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
    });

    // Select All
    $('#selectAll').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionsVisibility();
    });

    $(document).on('change', '.user-checkbox', function() {
        updateBulkActionsVisibility();
    });

    function updateBulkActionsVisibility() {
        const checkedCount = $('.user-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').removeClass('d-none');
        } else {
            $('#bulkActions').addClass('d-none');
        }
    }

    // Individual Toggle
    $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post('../api/admin_user_action.php', {
            action: 'set_status',
            ids: [id],
            status: newStatus,
            csrf_token: csrf_token
        }, function(res) {
            if (res.success) {
                table.ajax.reload(null, false);
            } else {
                alert(res.message);
                table.ajax.reload(null, false);
            }
        }, 'json');
    });

    // Bulk Action
    $('#applyBulkAction').on('click', function() {
        const actionType = $('#bulkActionSelect').val();
        if (!actionType) return;

        const selectedIds = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        let postData = {
            ids: selectedIds,
            csrf_token: csrf_token
        };

        if (actionType === 'active' || actionType === 'inactive') {
            postData.action = 'set_status';
            postData.status = actionType;
        } else if (actionType === 'delete') {
            if (!confirm('Are you sure you want to delete these users? This cannot be undone.')) return;
            postData.action = 'delete';
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post('../api/admin_user_action.php', postData, function(res) {
            btn.prop('disabled', false).text('Apply');
            if (res.success) {
                table.ajax.reload();
                $('#selectAll').prop('checked', false);
                $('#bulkActions').addClass('d-none');
            } else {
                alert(res.message);
            }
        }, 'json');
    });
});
</script>
</body>
</html>
