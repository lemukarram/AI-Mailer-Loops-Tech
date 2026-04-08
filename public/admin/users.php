<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

Auth::requireAdmin();
$db = Database::getInstance()->getConnection();

$message = '';
$error = '';

// Handle Status Toggle
if (isset($_GET['toggle_id'])) {
    $stmt = $db->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['toggle_id']]);
    $message = "User status updated.";
}

// Handle Global Limits Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_limits'])) {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $stmt = $db->prepare("UPDATE admin_limits SET max_emails_per_hour = ?, total_max_emails = ?, max_file_upload_size = ?, max_excel_rows = ? WHERE id = 1");
        $stmt->execute([
            $_POST['max_emails_per_hour'],
            $_POST['total_max_emails'],
            $_POST['max_file_upload_size'],
            $_POST['max_excel_rows']
        ]);
        $message = "Global limits updated.";
    }
}

// Fetch all users
$stmt = $db->query("SELECT id, email, role, status, created_at FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch limits
$stmt = $db->query("SELECT * FROM admin_limits LIMIT 1");
$limits = $stmt->fetch(PDO::FETCH_ASSOC);

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - aiMailSaas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Admin Dashboard</h2>
        <a href="../index.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Global Limits -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white">Global Limits</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="update_limits" value="1">
                        <div class="mb-3">
                            <label class="form-label">Max Emails / Hour (per user)</label>
                            <input type="number" name="max_emails_per_hour" class="form-control" value="<?php echo $limits['max_emails_per_hour']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Max Emails</label>
                            <input type="number" name="total_max_emails" class="form-control" value="<?php echo $limits['total_max_emails']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max File Size (bytes)</label>
                            <input type="number" name="max_file_upload_size" class="form-control" value="<?php echo $limits['max_file_upload_size']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Excel Rows</label>
                            <input type="number" name="max_excel_rows" class="form-control" value="<?php echo $limits['max_excel_rows']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Limits</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Management -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">Manage Users</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $user['role']; ?></span></td>
                                    <td>
                                        <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <a href="?toggle_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                Toggle Status
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
