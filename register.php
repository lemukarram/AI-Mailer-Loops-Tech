<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, 'user', 'inactive')");
                if ($stmt->execute([$email, $hashed_password])) {
                    $message = "Registration successful! Your account is pending admin activation.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
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
    <title>Create Account | AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --primary-dark: #4f46e5; --dark: #0f172a; --light: #f8fafc; --success: #10b981; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center; color: var(--dark);
        }
        .register-card { 
            width: 100%; max-width: 480px; padding: 3rem; 
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
        .btn-success {
            background: var(--success); border: none; border-radius: 16px;
            padding: 16px; font-weight: 800; margin-top: 1rem; transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
        .btn-success:hover {
            background: #059669; transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(16, 185, 129, 0.4);
        }
        .footer-text { color: #64748b; font-size: 0.9rem; }
        a { color: var(--primary); text-decoration: none; font-weight: 700; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="register-card animate__animated animate__zoomIn">
    <a href="index.php" class="logo-box">
        <i class="fas fa-inbox"></i>
    </a>
    <h3 class="text-center mb-1 fw-800">Get Started</h3>
    <p class="text-center footer-text mb-4">Create your free AI Mailer account</p>
    
    <?php if ($message): ?>
        <div class="alert alert-success border-0 rounded-4 p-4 mb-4 text-center bg-success bg-opacity-10 text-success">
            <i class="fas fa-circle-check fs-2 mb-3 d-block"></i>
            <h6 class="fw-bold mb-1">Registration Successful!</h6>
            <p class="small mb-0"><?php echo htmlspecialchars($message); ?></p>
        </div>
        <a href="login.php" class="btn btn-primary w-100 py-3 rounded-4 fw-bold">Return to Login</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 rounded-4 p-3 small mb-4 d-flex align-items-center bg-danger bg-opacity-10 text-danger fw-600">
                <i class="fas fa-circle-exclamation me-2 fs-5"></i><div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@company.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100">Create My Account <i class="fas fa-user-plus ms-2"></i></button>
        </form>
        
        <div class="mt-4 text-center footer-text">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
