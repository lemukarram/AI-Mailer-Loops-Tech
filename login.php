<?php
require_once __DIR__ . '/src/Auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $loginResult = Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($loginResult === true) {
            header("Location: dashboard.php");
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
    <title>Login | AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --primary-dark: #4f46e5; --dark: #0f172a; --light: #f8fafc; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center; color: var(--dark);
        }
        .login-card { 
            width: 100%; max-width: 440px; padding: 3rem; 
            background: #fff; border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255,255,255,0.8);
        }
        .logo-box {
            background: var(--primary); width: 56px; height: 56px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px; margin: 0 auto 1.5rem; font-size: 1.75rem; color: #fff;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        .form-label { font-weight: 700; font-size: 0.85rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.025em; }
        .form-control {
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 16px; padding: 14px 20px; color: var(--dark); transition: 0.3s;
        }
        .form-control:focus {
            background: #fff; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); outline: none;
        }
        .btn-primary {
            background: var(--primary); border: none; border-radius: 16px;
            padding: 16px; font-weight: 800; margin-top: 1rem; transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }
        .btn-primary:hover {
            background: var(--primary-dark); transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(99, 102, 241, 0.4);
        }
        .footer-text { color: #64748b; font-size: 0.9rem; }
        a { color: var(--primary); text-decoration: none; font-weight: 700; }
        a:hover { color: var(--primary-dark); text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card animate__animated animate__zoomIn">
    <a href="index.php" class="logo-box">
        <i class="fas fa-inbox"></i>
    </a>
    <h3 class="text-center mb-1 fw-800">Welcome Back</h3>
    <p class="text-center footer-text mb-4">Securely access your outreach dashboard</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 p-3 small mb-4 d-flex align-items-center bg-danger bg-opacity-10 text-danger fw-600">
            <i class="fas fa-circle-exclamation me-2 fs-5"></i><div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="name@company.com" required autofocus>
        </div>
        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <label class="form-label">Password</label>
            </div>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign In to Dashboard <i class="fas fa-arrow-right ms-2"></i></button>
    </form>
    
    <div class="mt-4 text-center footer-text">
        Don't have an account? <a href="register.php">Create Free Account</a>
    </div>
</div>

</body>
</html>
