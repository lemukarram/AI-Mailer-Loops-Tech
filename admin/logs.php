<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Database.php';

Auth::requireAdmin();
$db = Database::getInstance()->getConnection();

// Fetch all logs from the last 24 hours
$stmt = $db->query("SELECT l.*, u.email as user_email FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY l.created_at DESC LIMIT 1000");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .log-error { color: #ef4444; }
        .log-warning { color: #f59e0b; }
        .log-info { color: #6366f1; }

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
        <a href="../dashboard.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu">
            <a href="../dashboard.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="../campaigns.php" class="nav-link"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="../logs.php" class="nav-link"><i class="fas fa-list-ul"></i> My Logs</a>
            <a href="../settings.php" class="nav-link"><i class="fas fa-gear"></i> Settings</a>
            <a href="users.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <a href="logs.php" class="nav-link active"><i class="fas fa-terminal"></i> System Logs</a>
            <a href="../logout.php" class="nav-link text-danger mt-5"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="d-lg-none p-3 bg-white border-bottom mb-4 d-flex align-items-center justify-content-between">
            <button id="toggleSidebar" class="btn btn-light"><i class="fas fa-bars"></i></button>
            <h6 class="mb-0 fw-bold">AI Mailer</h6>
        </div>
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0 text-dark">System Wide Logs</h3>
                <span class="badge bg-danger rounded-pill px-3 py-2">Restricted Access</span>
            </div>

            <div class="table-responsive">
                <table id="logsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-muted"><?php echo $log['created_at']; ?></td>
                                <td class="small"><?php echo $log['user_email'] ? htmlspecialchars($log['user_email']) : '<span class="text-secondary italic">System</span>'; ?></td>
                                <td>
                                    <span class="badge bg-opacity-10 px-3 py-2 rounded-pill log-<?php echo $log['type']; ?> bg-<?php echo $log['type'] === 'error' ? 'danger' : ($log['type'] === 'warning' ? 'warning' : 'primary'); ?>">
                                        <?php echo strtoupper($log['type']); ?>
                                    </span>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($log['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#toggleSidebar, .sidebar-close').on('click', function() {
            $('#sidebar').toggleClass('mobile-show');
        });
        $('#logsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 50
        });
    });
</script>
</body>
</html>
