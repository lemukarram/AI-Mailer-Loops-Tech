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
    <title>Login - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --dark: #0f172a;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #fff;
        }
        .login-card { 
            width: 100%; 
            max-width: 420px; 
            padding: 2.5rem; 
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .logo-box {
            background: var(--primary);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            color: #fff;
            transition: 0.3s;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
            color: #fff;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 700;
            margin-top: 1rem;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        .text-muted { color: #94a3b8 !important; }
        a { color: var(--primary); text-decoration: none; font-weight: 600; }
        a:hover { color: #818cf8; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-box">
        <i class="fas fa-mailbox"></i>
    </div>
    <h3 class="text-center mb-1 fw-bold">Welcome Back</h3>
    <p class="text-center text-muted small mb-4">Sign in to manage your AI Mailer</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger small rounded-3 mb-4">
            <i class="fas fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="mb-3">
            <label class="form-label small fw-600">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@company.com" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-600">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>
    
    <div class="mt-4 text-center small">
        <span class="text-muted">Don't have an account?</span> <a href="register.php">Create Account</a>
    </div>
    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center small text-muted">
        Admin: <code>admin@example.com</code> / <code>admin_pass</code>
    </div>
</div>

</body>
</html>
