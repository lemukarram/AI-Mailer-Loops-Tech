<?php
session_start();
// If logged in, maybe show a "Go to Dashboard" button instead of "Login"
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI Mailer is the ultimate AI email outreach software. Automate B2B lead generation and job search emails with 100% humanized, perfectly tailored messages using Gemini and OpenAI.">
    <meta name="keywords" content="AI Email Outreach, Cold Email Software, B2B Lead Generation, Humanized AI Emails, Job Search Email Automator, SaaS Email Marketing, Automated Cold Email">
    <title>AI Mailer | Humanized AI Email Outreach Software for B2B & Job Hunters</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --primary-dark: #4f46e5; --dark: #0f172a; --light: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--light); color: var(--dark); overflow-x: hidden; }
        
        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); transition: all 0.3s; padding: 15px 0; }
        .navbar-brand { font-weight: 800; color: var(--dark); font-size: 1.5rem; }
        .navbar-brand i { color: var(--primary); margin-right: 8px; }
        .nav-link { font-weight: 600; color: #475569; margin: 0 10px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }
        .btn-nav { font-weight: 700; border-radius: 12px; padding: 10px 24px; transition: 0.3s; }
        .btn-nav-outline { border: 2px solid var(--primary); color: var(--primary); }
        .btn-nav-outline:hover { background: var(--primary); color: #fff; }
        
        /* Hero Section */
        .hero { padding: 120px 0 80px; position: relative; background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent 50%); }
        .hero h1 { font-size: 4rem; font-weight: 800; line-height: 1.1; margin-bottom: 24px; letter-spacing: -0.03em; }
        .hero h1 span { background: linear-gradient(135deg, var(--primary), #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 1.25rem; color: #475569; margin-bottom: 40px; max-width: 600px; }
        .btn-hero { background: var(--primary); color: #fff; border-radius: 16px; padding: 16px 40px; font-size: 1.1rem; font-weight: 700; border: none; box-shadow: 0 20px 30px -10px rgba(99, 102, 241, 0.4); transition: 0.3s; }
        .btn-hero:hover { background: var(--primary-dark); transform: translateY(-3px); box-shadow: 0 25px 35px -10px rgba(99, 102, 241, 0.5); color: #fff; }
        
        .hero-img-wrapper { position: relative; perspective: 1000px; }
        .hero-img { border-radius: 24px; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.15); transform: rotateY(-15deg) rotateX(5deg); transition: transform 0.5s; width: 100%; border: 1px solid rgba(255,255,255,0.5); }
        .hero-img-wrapper:hover .hero-img { transform: rotateY(0) rotateX(0); }
        
        /* Features */
        .features { padding: 100px 0; background: #fff; }
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-weight: 800; font-size: 2.5rem; }
        .section-title p { color: #64748b; font-size: 1.1rem; }
        
        .feature-card { background: var(--light); border-radius: 24px; padding: 40px; text-align: left; transition: 0.3s; border: 1px solid transparent; height: 100%; }
        .feature-card:hover { transform: translateY(-10px); background: #fff; border-color: rgba(99, 102, 241, 0.2); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); }
        .feature-icon { width: 60px; height: 60px; border-radius: 16px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 24px; }
        .feature-card h4 { font-weight: 700; margin-bottom: 16px; }
        .feature-card p { color: #64748b; margin-bottom: 0; line-height: 1.7; }
        
        /* How it works */
        .how-it-works { padding: 100px 0; background: var(--dark); color: #fff; }
        .step-box { text-align: center; position: relative; z-index: 1; margin-bottom: 40px; }
        .step-number { width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; margin: 0 auto 24px; color: var(--primary); border: 2px solid rgba(99, 102, 241, 0.3); backdrop-filter: blur(5px); }
        .step-box h4 { font-weight: 700; margin-bottom: 16px; }
        .step-box p { color: #94a3b8; }
        
        /* Footer */
        .footer { padding: 60px 0 30px; background: #fff; border-top: 1px solid #e2e8f0; }
        .footer-logo { font-weight: 800; font-size: 1.5rem; color: var(--dark); text-decoration: none; margin-bottom: 20px; display: inline-block; }
        .footer-link { color: #64748b; text-decoration: none; display: block; margin-bottom: 10px; transition: 0.3s; }
        .footer-link:hover { color: var(--primary); padding-left: 5px; }
        
        /* Floating animations */
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite reverse; }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-20px); } 100% { transform: translateY(0px); } }

        @media (max-width: 991px) {
            .hero h1 { font-size: 3rem; }
            .hero-img { transform: none; margin-top: 40px; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-inbox"></i> AI Mailer</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="knowledge-base.php">Knowledge Base</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <div class="d-flex gap-3 mt-3 mt-lg-0">
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php" class="btn btn-nav btn-hero py-2 px-4">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-nav btn-nav-outline">Log In</a>
                        <a href="register.php" class="btn btn-nav btn-hero py-2 px-4">Start Free</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate__animated animate__fadeInLeft">
                    <div class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill mb-4 border border-primary-subtle">🚀 The #1 AI Cold Email Software</div>
                    <h1>Outreach that feels <span>100% Human.</span></h1>
                    <p>Stop sounding like a robot. AI Mailer uses advanced generative AI to craft perfectly tailored, hyper-personalized emails for B2B lead generation and job hunting at scale.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-hero">Start Sending Now <i class="fas fa-arrow-right ms-2"></i></a>
                        <a href="knowledge-base.php" class="btn btn-nav btn-nav-outline py-3 px-4 d-flex align-items-center bg-white"><i class="fas fa-book-open me-2"></i> Read the Guide</a>
                    </div>
                    <div class="mt-5 d-flex align-items-center gap-3 text-muted small fw-bold">
                        <span><i class="fas fa-check-circle text-success me-1"></i> No Credit Card</span>
                        <span><i class="fas fa-check-circle text-success me-1"></i> B2B & Job Seekers</span>
                        <span><i class="fas fa-check-circle text-success me-1"></i> Gemini & OpenAI</span>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="hero-img-wrapper animate__animated animate__fadeInRight float-1">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop" alt="AI Email Dashboard" class="hero-img">
                        <!-- Decorative floating elements -->
                        <div class="position-absolute top-0 start-0 translate-middle bg-white p-3 rounded-4 shadow-lg float-2" style="z-index: 2;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success-subtle text-success p-2 rounded-circle"><i class="fas fa-paper-plane"></i></div>
                                <div><div class="small text-muted fw-bold">Open Rate</div><div class="fw-bold fs-5">84.2%</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title animate__animated animate__fadeInUp">
                <h2 class="text-dark">Powerful Automation, Zero Bloat</h2>
                <p>Engineered for maximum deliverability and response rates.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-robot"></i></div>
                        <h4>Master AI Identity</h4>
                        <p>Our Master AI agent analyzes your resume or business profile to automatically craft your perfect outreach persona and custom prompts.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-user-check"></i></div>
                        <h4>100% Humanized Tone</h4>
                        <p>Strict system prompts force the LLMs (GPT-4o or Gemini 2.5) to write softly, casually, and authentically, bypassing AI detectors.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <h4>Safe Background Queue</h4>
                        <p>Set a safe hourly limit (e.g., 10 emails/hr) to protect your domain reputation. Our cron engine handles the delivery in the background.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-file-excel"></i></div>
                        <h4>Smart List Management</h4>
                        <p>Upload your prospect lists via CSV. The system dynamically pulls `[contact_name]` and `[company]` into your templates.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-envelope-open-text"></i></div>
                        <h4>Universal Mail Support</h4>
                        <p>Connect any standard SMTP server or securely integrate with Google OAuth 2.0 for native Gmail sending capabilities.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>Enterprise Security</h4>
                        <p>AES-256-CBC encryption secures your API keys and SMTP credentials at rest. CSRF protection on all endpoints guarantees safety.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-title animate__animated animate__fadeInUp">
                <h2 class="text-white">How AI Mailer Works</h2>
                <p class="text-muted">Launch your first highly-targeted campaign in under 5 minutes.</p>
            </div>
            <div class="row position-relative">
                <!-- Connecting Line -->
                <div class="d-none d-lg-block position-absolute top-50 start-0 w-100 border-top border-2 border-secondary" style="z-index: 0; opacity: 0.2; transform: translateY(-50%);"></div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="step-box">
                        <div class="step-number">1</div>
                        <h4>Setup Persona</h4>
                        <p>Upload your resume or business profile in our sleek 5-step wizard. The Master AI configures your identity instantly.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-box">
                        <div class="step-number">2</div>
                        <h4>Import Leads</h4>
                        <p>Upload your CSV file with your targets. Required columns are simply name and email. We handle the rest.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-box">
                        <div class="step-number">3</div>
                        <h4>Review Prompts</h4>
                        <p>Use the AI Setup Wizard to let Gemini or OpenAI draft your base prompt, subject, and signature based on your profile.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-box">
                        <div class="step-number">4</div>
                        <h4>Start Queue</h4>
                        <p>Hit "Start Queue" on the dashboard. The system runs safely in the background respecting your hourly limits.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="index.php" class="footer-logo"><i class="fas fa-inbox text-primary me-2"></i>AI Mailer</a>
                    <p class="text-muted small pe-lg-5">The ultimate automated outreach tool that guarantees humanized, high-converting emails. Perfect for SaaS growth and job hunters.</p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <a href="index.php#features" class="footer-link">Features</a>
                    <a href="register.php" class="footer-link">Sign Up</a>
                    <a href="login.php" class="footer-link">Login</a>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Resources</h6>
                    <a href="knowledge-base.php" class="footer-link">Knowledge Base</a>
                    <a href="about.php" class="footer-link">About Us</a>
                    <a href="contact.php" class="footer-link">Contact</a>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <a href="privacy-policy.php" class="footer-link">Privacy Policy</a>
                    <a href="terms-conditions.php" class="footer-link">Terms & Conditions</a>
                </div>
            </div>
            <div class="border-top mt-5 pt-4 text-center text-muted small">
                &copy; <?php echo date('Y'); ?> AI Mailer SaaS. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>