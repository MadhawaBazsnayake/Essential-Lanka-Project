<?php
// worker/active-jobs.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: වර්කර් කෙනෙක්ද කියලා බලනවා
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Active Jobs - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 1000px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 40px; border-radius: 35px; }
    
    /* Worker Job Card Styling */
    .job-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .job-card:hover { border-color: #10b981; box-shadow: 0 12px 35px rgba(16,185,129,0.1); transform: translateY(-3px); }
    
    .job-info { display: flex; align-items: flex-start; gap: 20px; }
    .customer-avatar { width: 65px; height: 65px; border-radius: 20px; background-color: #e5dfd5; display: flex; justify-content: center; align-items: center; font-size: 2rem; color: #1a1a1a; flex-shrink: 0; }
    
    .badge-active { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }

    .job-meta { display: flex; gap: 15px; margin-top: 8px; flex-wrap: wrap; }
    .job-meta span { display: inline-flex; align-items: center; gap: 5px; font-size: 0.9rem; color: #666; background: #f8fafc; padding: 5px 12px; border-radius: 8px; }

    .action-group { display: flex; flex-direction: column; gap: 12px; align-items: flex-end; }
    
    .btn-contact { border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 10px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 8px; justify-content: center; }
    .btn-contact:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59,130,246,0.05); }
    
    .btn-complete { background: #10b981; color: white; padding: 10px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(16,185,129,0.2); display: flex; align-items: center; gap: 8px; justify-content: center; }
    .btn-complete:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(16,185,129,0.3); }

    @media (max-width: 768px) {
        .job-card { flex-direction: column; align-items: flex-start; padding: 20px; }
        .action-group { width: 100%; flex-direction: column; align-items: stretch; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">My Active Jobs</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Active Job Item 1 -->
            <div class="job-card">
                <div class="job-info">
                    <div class="customer-avatar">
                        <i class="ph-fill ph-user"></i>
                    </div>
                    <div>
                        <span class="badge-active"><i class="ph-bold ph-wrench"></i> In Progress</span>
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Electrical Wiring Repair</h3>
                        
                        <div class="job-meta">
                            <span><i class="ph-fill ph-user-circle"></i> Nimal Perera</span>
                            <span><i class="ph-fill ph-map-pin"></i> No. 42, Colombo 07</span>
                            <span><i class="ph-fill ph-calendar"></i> Today, 2:00 PM</span>
                        </div>
                    </div>
                </div>
                
                <div class="action-group">
                    <a href="tel:+94712345678" class="btn-contact"><i class="ph-bold ph-phone"></i> Contact Customer</a>
                    <button class="btn-complete" onclick="markCompleted(1)"><i class="ph-bold ph-check-circle"></i> Mark as Completed</button>
                </div>
            </div>

            <!-- Active Job Item 2 -->
            <div class="job-card">
                <div class="job-info">
                    <div class="customer-avatar">
                        <i class="ph-fill ph-user"></i>
                    </div>
                    <div>
                        <span class="badge-active"><i class="ph-bold ph-wrench"></i> In Progress</span>
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Ceiling Fan Installation</h3>
                        
                        <div class="job-meta">
                            <span><i class="ph-fill ph-user-circle"></i> Sarah Silva</span>
                            <span><i class="ph-fill ph-map-pin"></i> Dehiwala</span>
                            <span><i class="ph-fill ph-calendar"></i> Tomorrow, 10:00 AM</span>
                        </div>
                    </div>
                </div>
                
                <div class="action-group">
                    <a href="tel:+94776543210" class="btn-contact"><i class="ph-bold ph-phone"></i> Contact Customer</a>
                    <button class="btn-complete" onclick="markCompleted(2)"><i class="ph-bold ph-check-circle"></i> Mark as Completed</button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function markCompleted(jobId) {
        if(confirm("Are you sure you have successfully completed this job?")) {
            // Here you would typically send an AJAX request to update the database
            alert("Job marked as completed! Waiting for customer payment and review.");
            // Optional: window.location.reload();
        }
    }
</script>

</body>
</html>