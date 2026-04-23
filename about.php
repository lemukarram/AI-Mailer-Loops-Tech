<?php session_start(); $is_logged_in = isset($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about the mission and vision behind AI Mailer, the leading B2B outreach and job hunt automation platform.">
    <title>About Us | AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --primary-dark: #4f46e5; --dark: #0f172a; --light: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--light); color: var(--dark); }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 15px 0; }
        .navbar-brand { font-weight: 800; color: var(--dark); font-size: 1.5rem; }
        .navbar-brand i { color: var(--primary); margin-right: 8px; }
        .nav-link { font-weight: 600; color: #475569; margin: 0 10px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }
        .btn-nav { font-weight: 700; border-radius: 12px; padding: 10px 24px; transition: 0.3s; }
        .btn-nav-outline { border: 2px solid var(--primary); color: var(--primary); }
        .btn-nav-outline:hover { background: var(--primary); color: #fff; }
        
        .page-header { background: var(--dark); color: #fff; padding: 100px 0; text-align: center; }
        .page-header h1 { font-weight: 800; font-size: 3rem; margin-bottom: 20px; }
        .page-header p { color: #94a3b8; font-size: 1.2rem; max-width: 600px; margin: 0 auto; }
        
        .content-section { padding: 80px 0; background: #fff; }
        .content-section h2 { font-weight: 800; margin-bottom: 30px; color: var(--dark); }
        .content-section p { color: #475569; font-size: 1.1rem; line-height: 1.8; margin-bottom: 20px; }
        .quote-box { background: rgba(99, 102, 241, 0.05); border-left: 4px solid var(--primary); padding: 30px; border-radius: 0 16px 16px 0; margin: 40px 0; }
        .quote-box p { font-size: 1.3rem; font-style: italic; color: var(--primary-dark); font-weight: 600; margin: 0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-inbox"></i> AI Mailer</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="knowledge-base.php">Knowledge Base</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <div class="d-flex gap-3 mt-3 mt-lg-0">
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php" class="btn btn-nav btn-nav-outline py-2 px-4">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-nav btn-nav-outline">Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <div class="container">
            <h1>Our Mission</h1>
            <p>Building the bridge between cutting-edge AI and authentic, humanized outreach.</p>
        </div>
    </header>

    <section class="content-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                    <h2>Why we built AI Mailer</h2>
                    <p>In a world flooded with robotic spam, we saw a critical need for an automated solution that actually sounded human. Whether you are a B2B startup trying to find leads or a professional hunting for your dream job, cold outreach is a numbers game.</p>
                    <p>But numbers without quality lead to zero responses.</p>
                    <p>That's why we created AI Mailer—a zero-dependency, hyper-secure PHP platform that forces top-tier LLMs (OpenAI and Gemini) to write soft, casual, and highly personalized emails.</p>
                    
                    <div class="quote-box">
                        <p>"Stop sounding like a robot. High-conversion outreach requires empathy, context, and scale. We give you all three."</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop" alt="Team Collaboration" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </section>

</body>
</html>