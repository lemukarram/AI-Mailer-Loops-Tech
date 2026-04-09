<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$user_role = $_SESSION['user_role'];
$db = Database::getInstance()->getConnection();

// Fetch logs for the current user
$stmt = $db->prepare("SELECT * FROM logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
$stmt->execute([$user_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #334155; }
        #sidebar { width: var(--sidebar-width); background: var(--dark); color: #fff; min-height: 100vh; position: fixed; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .sidebar-brand { padding: 2.5rem 1.5rem; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-brand i { background: var(--primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; }
        .nav-link { display: flex; align-items: center; padding: 0.85rem 1.25rem; color: #94a3b8; text-decoration: none; border-radius: 12px; margin: 0 1rem 0.5rem; transition: 0.2s; }
        .nav-link i { margin-right: 12px; width: 24px; text-align: center; font-size: 1.1rem; }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .nav-link.active { background: var(--primary) !important; color: #fff !important; }
        #content { margin-left: var(--sidebar-width); padding: 3rem; min-height: 100vh; }
        .glass-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); padding: 2.5rem; }
        .log-error { color: #ef4444; }
        .log-warning { color: #f59e0b; }
        .log-info { color: #6366f1; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav id="sidebar">
        <a href="index.php" class="sidebar-brand"><i class="fas fa-inbox"></i><span>AI Mailer</span></a>
        <div class="nav-menu mt-2">
            <a href="index.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
            <a href="campaigns.php" class="nav-link"><i class="fas fa-bullhorn"></i> Campaigns</a>
            <a href="logs.php" class="nav-link active"><i class="fas fa-list-ul"></i> Logs</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-gear"></i> Settings</a>
            <?php if ($user_role === 'admin'): ?><a href="admin/users.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Panel</a><?php endif; ?>
            <a href="logout.php" class="nav-link text-danger mt-5"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div id="content" class="flex-grow-1">
        <div class="glass-card">
            <h3 class="fw-bold mb-4">Activity Logs</h3>
            <div class="table-responsive">
                <table id="logsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-muted"><?php echo $log['created_at']; ?></td>
                                <td>
                                    <span class="badge bg-opacity-10 px-3 py-2 rounded-pill log-<?php echo $log['type']; ?> bg-<?php echo $log['type'] === 'error' ? 'danger' : ($log['type'] === 'warning' ? 'warning' : 'primary'); ?>">
                                        <?php echo strtoupper($log['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">No logs found. Start your queue to see activity.</td>
                            </tr>
                        <?php endif; ?>
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
        $('#logsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
</body>
</html>
