<?php
// client/post-job.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Service - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 800px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 45px 40px; border-radius: 35px; }
    
    .form-group { margin-bottom: 22px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 18px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    
    textarea.form-control { resize: vertical; min-height: 120px; }
    select.form-control { appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%231a1a1a" viewBox="0 0 256 256"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path></svg>'); background-repeat: no-repeat; background-position: right 20px center; padding-right: 45px; }

    .d-flex-gap { display: flex; gap: 20px; }
    .d-flex-gap .form-group { flex: 1; }

    .btn-submit { background: #10b981; color: white; padding: 18px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.15rem; box-shadow: 0 10px 25px rgba(16,185,129,0.2); margin-top: 15px; display: flex; justify-content: center; align-items: center; gap: 10px; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(16,185,129,0.3); }

    @media (max-width: 768px) {
        .d-flex-gap { flex-direction: column; gap: 0; }
        .glass-box { padding: 30px 20px; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Request a Service</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <p style="color: #666; margin-bottom: 30px; font-size: 1.05rem;">Fill in the details below to broadcast your request to verified workers in your area.</p>

            <form action="../api/job-process.php" method="POST">
                <input type="hidden" name="action" value="post_job">

                <div class="form-group">
                    <label>Job Title / Summary</label>
                    <input type="text" name="title" class="form-control" placeholder="E.g. Need a plumber to fix a leaking pipe" required>
                </div>

                <div class="form-group">
                    <label>Service Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="" disabled selected>Select a category...</option>
                        <option value="1">Electrical</option>
                        <option value="2">Plumbing</option>
                        <option value="3">Cleaning & Maintenance</option>
                        <option value="4">Carpentry</option>
                        <option value="5">Painting</option>
                        <option value="6">Other Handyman Services</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Full Service Address</label>
                    <input type="text" name="address" class="form-control" placeholder="House No, Street, City" required>
                </div>

                <div class="d-flex-gap">
                    <div class="form-group">
                        <label>Preferred Date</label>
                        <input type="date" name="job_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Preferred Time</label>
                        <input type="time" name="job_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="description" class="form-control" placeholder="Describe the issue or task in detail so workers can understand the requirements..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Broadcast Job Request
                </button>
            </form>

        </div>
    </div>
</section>

</body>
</html>