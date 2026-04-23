<?php session_start(); $is_logged_in = isset($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact the AI Mailer team for support, feature requests, or enterprise inquiries regarding our AI email automation software.">
    <title>Contact Us | AI Mailer</title>
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
        
        .page-header { background: var(--dark); color: #fff; padding: 100px 0 150px; text-align: center; }
        .page-header h1 { font-weight: 800; font-size: 3rem; margin-bottom: 20px; }
        .page-header p { color: #94a3b8; font-size: 1.2rem; max-width: 600px; margin: 0 auto; }
        
        .contact-card { background: #fff; border-radius: 24px; padding: 50px; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.1); margin-top: -80px; }
        .form-control { border-radius: 12px; padding: 14px 20px; border: 1px solid #e2e8f0; background: #fcfdfe; transition: 0.3s; margin-bottom: 20px; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: 16px 32px; font-weight: 700; width: 100%; transition: 0.3s; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        
        .contact-info { padding: 40px; background: rgba(99, 102, 241, 0.05); border-radius: 24px; height: 100%; }
        .info-item { display: flex; align-items: flex-start; margin-bottom: 30px; }
        .info-icon { width: 50px; height: 50px; border-radius: 12px; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 20px; flex-shrink: 0; }
        .info-item h5 { font-weight: 700; margin-bottom: 5px; }
        .info-item p { color: #64748b; margin-bottom: 0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-inbox"></i> AI Mailer</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="knowledge-base.php">Knowledge Base</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>Have questions about scaling your outreach? We're here to help.</p>
        </div>
    </header>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-card">
                    <div class="row g-5">
                        <div class="col-md-7">
                            <h3 class="fw-bold mb-4">Send a Message</h3>
                            <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Message sent successfully!');">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                                <input type="email" class="form-control" placeholder="Email Address" required>
                                <textarea class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                                <button type="submit" class="btn btn-primary">Send Message <i class="fas fa-paper-plane ms-2"></i></button>
                            </form>
                        </div>
                        <div class="col-md-5">
                            <div class="contact-info">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                                    <div>
                                        <h5>Email Us</h5>
                                        <p>support@loopstech.com<br>sales@loopstech.com</p>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div>
                                        <h5>Location</h5>
                                        <p>San Francisco, CA<br>United States</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>