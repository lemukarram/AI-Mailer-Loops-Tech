<?php
require_once __DIR__ . '/src/Auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $loginResult = Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($loginResult === true) {
            header("Location: index.php");
            exit();
        } else {
            $error = $loginResult;
        }
    } else {
        $error = "Invalid CSRF token.";
    }
}

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - aiMailSaas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); background: white; border-radius: 8px; }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4">aiMailSaas Login</h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="mt-3 text-center small">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
    <div class="mt-2 text-center small text-muted">
        Default Admin: <code>admin@example.com</code> / <code>admin_pass</code>
    </div>
</div>

</body>
</html>
