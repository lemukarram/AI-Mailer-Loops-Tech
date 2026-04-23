<?php
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Database.php';

Auth::requireLogin();
$user_id = Auth::getUserId();
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT wizard_completed FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$wizard_completed = $stmt->fetchColumn();

if ($wizard_completed === 1) {
    header("Location: dashboard.php");
    exit();
}

$csrf_token = Auth::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - AI Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --primary-dark: #4f46e5; --dark: #0f172a; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: #334155; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        
        .wizard-container { width: 100%; max-width: 800px; padding: 20px; }
        .wizard-card { background: #fff; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08); padding: 50px; position: relative; min-height: 600px; display: flex; flex-direction: column; }
        
        .step-indicator { display: flex; justify-content: center; gap: 12px; margin-bottom: 40px; }
        .step-dot { width: 12px; height: 12px; border-radius: 50%; background: #e2e8f0; transition: 0.3s; }
        .step-dot.active { width: 32px; border-radius: 10px; background: var(--primary); }
        
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        
        .choice-card { border: 2px solid #f1f5f9; border-radius: 24px; padding: 30px; cursor: pointer; transition: 0.3s; height: 100%; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .choice-card:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.02); transform: translateY(-5px); }
        .choice-card.selected { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }
        .choice-icon { width: 80px; height: 80px; border-radius: 20px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary); margin-bottom: 20px; transition: 0.3s; }
        .choice-card.selected .choice-icon { background: var(--primary); color: #fff; }
        
        .form-label { font-weight: 700; color: var(--dark); font-size: 0.9rem; margin-bottom: 8px; }
        .form-control { border-radius: 16px; padding: 14px 20px; border: 1px solid #e2e8f0; background: #fcfdfe; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        
        .btn-wizard { border-radius: 18px; padding: 16px 32px; font-weight: 700; transition: 0.3s; border: none; }
        .btn-next { background: var(--primary); color: #fff; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        .btn-next:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 15px 20px -3px rgba(99, 102, 241, 0.4); }
        .btn-back { background: #f1f5f9; color: #64748b; }
        
        .oauth-btn { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px; width: 100%; display: flex; align-items: center; justify-content: center; gap: 12px; font-weight: 600; transition: 0.3s; }
        .oauth-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
        
        .ai-toggle { background: #f8fafc; border-radius: 24px; padding: 24px; border: 1px solid #e2e8f0; }
        
        .finish-confetti { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
    </style>
</head>
<body>

<div class="wizard-container">
    <div class="wizard-card shadow-lg animate__animated animate__fadeIn">
        <div class="step-indicator">
            <div class="step-dot active" data-step="1"></div>
            <div class="step-dot" data-step="2"></div>
            <div class="step-dot" data-step="3"></div>
            <div class="step-dot" data-step="4"></div>
            <div class="step-dot" data-step="5"></div>
        </div>

        <form id="wizardForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <!-- Step 1: Purpose -->
            <div class="wizard-step active animate__animated animate__fadeInRight" data-step="1">
                <h2 class="fw-bold text-center mb-2">Welcome to AI Mailer</h2>
                <p class="text-muted text-center mb-5">Let's tailor the experience to your specific goals.</p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="choice-card" data-value="job_hunt">
                            <div class="choice-icon"><i class="fas fa-briefcase"></i></div>
                            <h5 class="fw-bold">Job Hunting</h5>
                            <p class="small text-muted mb-0">Personal career outreach, connecting with hiring managers and recruiters.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="choice-card" data-value="business_leads">
                            <div class="choice-icon"><i class="fas fa-rocket"></i></div>
                            <h5 class="fw-bold">Business Leads</h5>
                            <p class="small text-muted mb-0">B2B outreach, finding new clients, and offering your professional services.</p>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="purpose" id="input_purpose" value="job_hunt">
            </div>

            <!-- Step 2: Profile -->
            <div class="wizard-step animate__animated animate__fadeInRight" data-step="2">
                <h2 class="fw-bold mb-2">Tell us about you</h2>
                <p class="text-muted mb-4">This helps the AI write more authentic emails for you.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" id="label_designation">Current Designation</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Product Designer" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Company / Business Name</label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Creative Solutions Inc.">
                    </div>
                    <div class="col-12">
                        <label class="form-label" id="label_bio">Tell us about yourself / your company</label>
                        <textarea name="other_info" class="form-control" rows="4" placeholder="Brief summary of your expertise or service..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Files -->
            <div class="wizard-step animate__animated animate__fadeInRight" data-step="3">
                <h2 class="fw-bold mb-2" id="header_files">Upload your Resume</h2>
                <p class="text-muted mb-4">We'll extract information from your documents to sharpen the AI agent.</p>
                <div class="p-5 border-2 border-dashed rounded-4 text-center bg-light bg-opacity-50">
                    <div class="mb-3 text-primary fs-1"><i class="fas fa-cloud-arrow-up"></i></div>
                    <h6 class="fw-bold">Click to upload or drag and drop</h6>
                    <p class="small text-muted">PDF or DOCX only (Max 5MB)</p>
                    <input type="file" name="attachment_file" id="attachment_input" class="form-control mt-3" accept=".pdf,.docx">
                </div>
                <div class="mt-4 p-3 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-4 d-flex align-items-center">
                    <i class="fas fa-info-circle text-info me-3 fs-4"></i>
                    <p class="small mb-0 text-info-emphasis">This document will also be used as your master attachment for campaigns.</p>
                </div>
            </div>

            <!-- Step 4: SMTP / OAuth -->
            <div class="wizard-step animate__animated animate__fadeInRight" data-step="4">
                <h2 class="fw-bold mb-2">Connect your Email</h2>
                <p class="text-muted mb-4">Choose how you want to send your professional outreach.</p>
                
                <button type="button" class="oauth-btn mb-4 shadow-sm">
                    <img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" width="24" height="24">
                    Sign in with Google (OAuth 2.0)
                </button>
                
                <div class="d-flex align-items-center mb-4">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted small fw-bold">OR CONFIGURE MANUALLY</span>
                    <hr class="flex-grow-1">
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input type="number" name="smtp_port" class="form-control" placeholder="465">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="smtp_user" class="form-control" placeholder="user@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="••••••••">
                    </div>
                </div>
            </div>

            <!-- Step 5: AI & Limits -->
            <div class="wizard-step animate__animated animate__fadeInRight" data-step="5">
                <h2 class="fw-bold mb-2">Final Touches</h2>
                <p class="text-muted mb-4">Configure your AI preferences and safety limits.</p>
                
                <div class="ai-toggle mb-4">
                    <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                        <div>
                            <label class="form-check-label fw-bold fs-5">AI Email Generator</label>
                            <p class="small text-muted mb-0">Automatically write hyper-personalized emails for every contact.</p>
                        </div>
                        <input class="form-check-input" type="checkbox" name="ai_enabled" id="aiToggle" checked style="width: 50px; height: 26px;">
                    </div>
                    
                    <div id="aiOptions" class="mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Preferred Model</label>
                                <select name="preferred_llm" class="form-select rounded-4 p-3 border-0 bg-white shadow-sm">
                                    <option value="gemini">Google Gemini 1.5 Pro</option>
                                    <option value="openai">OpenAI GPT-4o</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Your API Key</label>
                                <input type="password" name="api_key" class="form-control border-0 bg-white shadow-sm" placeholder="Paste your key here">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label d-flex justify-content-between">
                        <span>Per Hour Email Limit</span>
                        <span class="text-primary fw-bold" id="limitVal">10</span>
                    </label>
                    <input type="range" name="personal_hourly_limit" class="form-range" min="1" max="12" value="10" id="limitRange">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Careful</span>
                        <span>Safe (Recommended: 10)</span>
                        <span>Fast</span>
                    </div>
                    <p class="mt-3 small text-muted"><i class="fas fa-circle-info me-1"></i> System max is 12 emails/hour to maintain high deliverability.</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-auto pt-5 d-flex justify-content-between">
                <button type="button" class="btn-wizard btn-back" id="btnBack" style="display: none;">Back</button>
                <button type="button" class="btn-wizard btn-next ms-auto" id="btnNext">Continue <i class="fas fa-arrow-right ms-2"></i></button>
                <button type="submit" class="btn-wizard btn-next ms-auto" id="btnFinish" style="display: none;">Complete Setup <i class="fas fa-check-circle ms-2"></i></button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 5;

    // Purpose Choice
    $('.choice-card').on('click', function() {
        $('.choice-card').removeClass('selected animate__pulse');
        $(this).addClass('selected animate__animated animate__pulse');
        const val = $(this).data('value');
        $('#input_purpose').val(val);
        
        // Dynamic labels
        if (val === 'job_hunt') {
            $('#label_designation').text('Current Designation');
            $('#label_bio').text('Tell us about your career and expertise');
            $('#header_files').text('Upload your Resume');
        } else {
            $('#label_designation').text('Primary Service / Role');
            $('#label_bio').text('Tell us about your business and services');
            $('#header_files').text('Upload Business Profile');
        }
    });

    // Step Navigation
    $('#btnNext').on('click', function() {
        if (currentStep < totalSteps) {
            $(`.wizard-step[data-step="${currentStep}"]`).removeClass('active animate__fadeInRight').addClass('animate__fadeOutLeft');
            
            setTimeout(() => {
                $(`.wizard-step[data-step="${currentStep}"]`).hide().removeClass('animate__fadeOutLeft');
                currentStep++;
                $(`.wizard-step[data-step="${currentStep}"]`).show().addClass('active animate__fadeInRight');
                updateIndicator();
            }, 300);
        }
    });

    $('#btnBack').on('click', function() {
        if (currentStep > 1) {
            $(`.wizard-step[data-step="${currentStep}"]`).removeClass('active animate__fadeInRight').addClass('animate__fadeOutRight');
            
            setTimeout(() => {
                $(`.wizard-step[data-step="${currentStep}"]`).hide().removeClass('animate__fadeOutRight');
                currentStep--;
                $(`.wizard-step[data-step="${currentStep}"]`).show().addClass('active animate__fadeInLeft');
                updateIndicator();
            }, 300);
        }
    });

    function updateIndicator() {
        $('.step-dot').removeClass('active');
        $(`.step-dot[data-step="${currentStep}"]`).addClass('active');
        
        $('#btnBack').toggle(currentStep > 1);
        $('#btnNext').toggle(currentStep < totalSteps);
        $('#btnFinish').toggle(currentStep === totalSteps);
    }

    // AI Toggle
    $('#aiToggle').on('change', function() {
        $('#aiOptions').slideToggle(this.checked);
    });

    // Limit Range
    $('#limitRange').on('input', function() {
        $('#limitVal').text(this.value);
    });

    // Handle Form Submission
    $('#wizardForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        const btn = $('#btnFinish');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Finalizing...');

        $.ajax({
            url: 'api/setup_wizard.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    window.location.href = 'dashboard.php?msg=Welcome! Your account is perfectly configured.';
                } else {
                    alert(res.error);
                    btn.prop('disabled', false).html('Complete Setup <i class="fas fa-check-circle ms-2"></i>');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false).html('Complete Setup <i class="fas fa-check-circle ms-2"></i>');
            }
        });
    });
});
</script>

</body>
</html>
