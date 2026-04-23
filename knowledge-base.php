<?php session_start(); $is_logged_in = isset($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete Knowledge Base for AI Mailer. Learn how to configure Gmail App Passwords, generate Gemini API Keys, format CSVs, and write perfect outreach prompts.">
    <title>Knowledge Base & Guide | AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; --light: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--light); color: var(--dark); }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 15px 0; }
        .navbar-brand { font-weight: 800; color: var(--dark); font-size: 1.5rem; }
        .navbar-brand i { color: var(--primary); margin-right: 8px; }
        .nav-link { font-weight: 600; color: #475569; margin: 0 10px; transition: 0.3s; }
        .nav-link:hover { color: var(--primary); }
        
        .kb-header { background: var(--primary); color: #fff; padding: 80px 0; text-align: center; }
        .kb-header h1 { font-weight: 800; font-size: 2.5rem; margin-bottom: 20px; }
        
        .sidebar-nav { background: #fff; border-radius: 20px; padding: 20px; position: sticky; top: 100px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .sidebar-link { display: block; padding: 12px 16px; color: #475569; text-decoration: none; font-weight: 600; border-radius: 10px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        
        .content-card { background: #fff; border-radius: 24px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .content-card h2 { font-weight: 800; margin-bottom: 24px; color: var(--dark); border-bottom: 2px solid var(--light); padding-bottom: 15px; }
        .content-card h4 { font-weight: 700; margin-top: 30px; margin-bottom: 15px; color: var(--primary); }
        .content-card p, .content-card li { color: #64748b; line-height: 1.8; font-size: 1.05rem; }
        
        code { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; color: #e11d48; font-weight: 600; }
        pre { background: var(--dark); color: #f8fafc; padding: 20px; border-radius: 16px; overflow-x: auto; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#kb-sidebar" tabindex="0">

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-inbox"></i> AI Mailer</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="knowledge-base.php">Knowledge Base</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="kb-header">
        <div class="container">
            <h1>Knowledge Base & Master Guide</h1>
            <p class="mb-0 text-white-50">Everything you need to master AI Mailer.</p>
        </div>
    </header>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="sidebar-nav" id="kb-sidebar">
                    <a class="sidebar-link" href="#purpose">Purpose & Overview</a>
                    <a class="sidebar-link" href="#gmail-app">Gmail App Passwords</a>
                    <a class="sidebar-link" href="#api-keys">Getting API Keys</a>
                    <a class="sidebar-link" href="#csv-format">Formatting CSV/Excel</a>
                    <a class="sidebar-link" href="#prompts">Writing Prompts</a>
                    <a class="sidebar-link" href="#queue">Managing the Queue</a>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="content-card" id="purpose">
                    <h2>Purpose of the Project</h2>
                    <p><strong>AI Mailer</strong> is an automated, secure SaaS application designed to solve the biggest problem in cold outreach: sounding like a robot. Whether you are a B2B business trying to generate leads or a professional hunting for a job, this platform ensures your emails are 100% humanized, perfectly personalized with attachments, and delivered safely within SMTP limits.</p>
                </div>

                <div class="content-card" id="gmail-app">
                    <h2>How to Setup Gmail App Passwords</h2>
                    <p>If you do not want to use OAuth 2.0, you can use Gmail's SMTP servers by generating an App Password. Note: Your regular Gmail password will NOT work.</p>
                    <ol>
                        <li>Go to your <a href="https://myaccount.google.com/security" target="_blank" class="fw-bold">Google Account Security page</a>.</li>
                        <li>Ensure <strong>2-Step Verification</strong> is turned ON.</li>
                        <li>Search for "App Passwords" in the security search bar.</li>
                        <li>Select "Other (Custom name)" and type "AI Mailer", then click Generate.</li>
                        <li>Copy the 16-character password provided.</li>
                        <li>In AI Mailer > Settings, enter:
                            <ul>
                                <li>Host: <code>smtp.gmail.com</code></li>
                                <li>Port: <code>465</code> (or 587)</li>
                                <li>Username: Your full Gmail address</li>
                                <li>Password: The 16-character App Password (no spaces)</li>
                            </ul>
                        </li>
                    </ol>
                </div>

                <div class="content-card" id="api-keys">
                    <h2>How to Get API Keys (Gemini & OpenAI)</h2>
                    <h4>Google Gemini API (Free Tier Available)</h4>
                    <ol>
                        <li>Go to <a href="https://aistudio.google.com/" target="_blank" class="fw-bold">Google AI Studio</a>.</li>
                        <li>Click "Get API Key" in the left menu.</li>
                        <li>Click "Create API Key in new project".</li>
                        <li>Copy the key and paste it into AI Mailer Settings.</li>
                    </ol>
                    <h4>OpenAI API (GPT-4o)</h4>
                    <ol>
                        <li>Go to <a href="https://platform.openai.com/api-keys" target="_blank" class="fw-bold">OpenAI Platform</a>.</li>
                        <li>Create an account and add a billing method (required for API access).</li>
                        <li>Click "Create new secret key".</li>
                        <li>Copy the key. It begins with <code>sk-</code>.</li>
                    </ol>
                </div>

                <div class="content-card" id="csv-format">
                    <h2>How the CSV (Excel) Should Look</h2>
                    <p>When importing your Lead Prospect List or Target Companies, your file MUST be in <code>.csv</code> format.</p>
                    <p>The system expects specific headers. The minimum required headers are <strong>contact name</strong> and <strong>email</strong>. Optional headers enhance personalization.</p>
                    <pre>contact name,email,company,designation,company type
John Doe,john@example.com,TechCorp,CEO,B2B Software
Jane Smith,jane@startup.io,StartupIO,Recruiter,Agency</pre>
                    <div class="alert alert-info border-0 mt-3 small">
                        <strong>Pro Tip:</strong> Every time you upload a new CSV, the system clears your old list to ensure your campaign is fresh. Download your list from Excel by choosing "File > Save As > CSV (Comma delimited)".
                    </div>
                </div>

                <div class="content-card" id="prompts">
                    <h2>How to Write the Perfect Prompt</h2>
                    <p>AI Mailer already appends a strict system rule to make the AI sound human. Your "Base Prompt" just needs to provide the context and goal.</p>
                    <h4>For Job Hunters</h4>
                    <p><code>Write a warm, concise outreach to a hiring manager. Emphasize my 5 years of experience in React and my enthusiasm for the innovative work they are doing at [company]. Keep it under 100 words.</code></p>
                    <h4>For B2B Sales</h4>
                    <p><code>Write a casual, non-salesy email offering my SEO services. Note that I noticed some growth opportunities for [company] and want to ask if they are open to a quick 5-min chat next week.</code></p>
                    <p><em>Always use the <code>[company]</code> placeholder. The system will automatically inject the recipient's company name from your CSV!</em></p>
                </div>

                <div class="content-card" id="queue">
                    <h2>Starting/Stopping the Queue & Attachments</h2>
                    <h4>The Queue Engine</h4>
                    <p>Because sending 1000 emails at once will get you banned by Gmail, AI Mailer uses a Cron Queue. In Settings, you define your "Per Hour Email Limit" (Max 12). Once you click <strong>Start Queue</strong> on the Dashboard, the system will slowly send emails in the background 24/7 until the list is finished.</p>
                    <h4>Attachments</h4>
                    <p>In the <strong>Campaigns</strong> tab, you can upload a Master PDF or DOCX (like a Resume or a Business Pitch Deck). This file will automatically be attached to EVERY email sent in that campaign.</p>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(window).on('scroll', function() {
            var scrollDistance = $(window).scrollTop();
            $('.content-card').each(function(i) {
                if ($(this).position().top <= scrollDistance + 150) {
                    $('.sidebar-nav a.active').removeClass('active');
                    $('.sidebar-nav a').eq(i).addClass('active');
                }
            });
        }).scroll();
    </script>
</body>
</html>