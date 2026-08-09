<?php
// worker/verification.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}

// මෙහිදී දත්ත සමුදායෙන් වර්කර්ගේ දැනට තියෙන verification status එක ලබා ගත හැක.
// (Demo එකක් ලෙස අපි මෙය 'unverified' ලෙස සලකමු)
$verification_status = 'unverified'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 850px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 45px; border-radius: 35px; }

    /* Alert Banners */
    .alert-banner { padding: 20px 25px; border-radius: 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 35px; font-weight: 600; font-size: 1.05rem; }
    .alert-warning { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1.5px solid rgba(245, 158, 11, 0.2); }
    .alert-warning i { font-size: 2rem; color: #f59e0b; }
    
    .alert-success { background: rgba(16, 185, 129, 0.1); color: #059669; border: 1.5px solid rgba(16, 185, 129, 0.2); }
    .alert-success i { font-size: 2rem; color: #10b981; }

    /* Form Styles */
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 10px; font-size: 1rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 16px 20px; border-radius: 15px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }

    /* File Upload Zone */
    .upload-zone { border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 20px; padding: 40px 20px; text-align: center; cursor: pointer; transition: 0.3s; position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; }
    .upload-zone:hover, .upload-zone.dragover { border-color: #10b981; background: rgba(16,185,129,0.05); }
    .upload-zone i { font-size: 3rem; color: #94a3b8; transition: 0.3s; }
    .upload-zone:hover i { color: #10b981; }
    .upload-zone span { font-weight: 600; color: #64748b; font-size: 1rem; }
    .upload-zone input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    
    .file-name { margin-top: 10px; font-size: 0.9rem; color: #10b981; font-weight: 700; display: none; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }

    .btn-submit { background: #1a1a1a; color: white; padding: 18px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.15rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 15px; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.25); background: #000000; }

    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr; }
        .glass-box { padding: 30px 20px; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Identity Verification</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <?php if($verification_status === 'verified'): ?>
                <div class="alert-banner alert-success">
                    <i class="ph-fill ph-seal-check"></i>
                    <div>
                        <h4 style="margin-bottom: 5px; color: #047857;">Account Verified!</h4>
                        <p style="font-size: 0.95rem;">Your identity has been successfully verified. You will now receive more job requests.</p>
                    </div>
                </div>
            <?php elseif($verification_status === 'pending'): ?>
                <div class="alert-banner alert-warning">
                    <i class="ph-fill ph-hourglass-high"></i>
                    <div>
                        <h4 style="margin-bottom: 5px; color: #b45309;">Verification in Progress</h4>
                        <p style="font-size: 0.95rem;">We are currently reviewing your documents. This usually takes up to 24 hours.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert-banner alert-warning">
                    <i class="ph-fill ph-warning-circle"></i>
                    <div>
                        <h4 style="margin-bottom: 5px; color: #b45309;">Action Required: Verify Your Identity</h4>
                        <p style="font-size: 0.95rem;">Please upload your National Identity Card (NIC) to get the "Verified" badge and earn customer trust.</p>
                    </div>
                </div>

                <form action="../api/verify-process.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>National Identity Card (NIC) Number</label>
                        <input type="text" name="nic_number" class="form-control" placeholder="E.g. 199512345678 or 951234567V" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label>NIC Front Side</label>
                            <div class="upload-zone" id="drop-front">
                                <i class="ph-fill ph-identification-card"></i>
                                <span>Click or drag image here</span>
                                <input type="file" name="nic_front" accept="image/png, image/jpeg" required onchange="showFileName(this, 'name-front')">
                                <div class="file-name" id="name-front"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>NIC Back Side</label>
                            <div class="upload-zone" id="drop-back">
                                <i class="ph-fill ph-identification-card"></i>
                                <span>Click or drag image here</span>
                                <input type="file" name="nic_back" accept="image/png, image/jpeg" required onchange="showFileName(this, 'name-back')">
                                <div class="file-name" id="name-back"></div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="ph-bold ph-shield-check"></i> Submit for Verification
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
    // පින්තූරයක් තේරුවාට පසුව එහි නම පෙන්වීම සඳහා කුඩා JavaScript කේතයක්
    function showFileName(input, textId) {
        const fileNameElement = document.getElementById(textId);
        const icon = input.parentElement.querySelector('i');
        const textSpan = input.parentElement.querySelector('span');

        if (input.files && input.files.length > 0) {
            fileNameElement.textContent = "Selected: " + input.files[0].name;
            fileNameElement.style.display = 'block';
            icon.style.color = '#10b981';
            textSpan.style.display = 'none';
        } else {
            fileNameElement.style.display = 'none';
            icon.style.color = '#94a3b8';
            textSpan.style.display = 'block';
        }
    }
</script>

</body>
</html>