<?php
// client/active-jobs.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check (පසුව Backend හදද්දී මෙය සම්පූර්ණ කළ හැක)
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
    <title>Active Jobs - Essential Lanka</title>
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
    
    /* Job Card Styling */
    .job-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .job-card:hover { border-color: #10b981; box-shadow: 0 12px 35px rgba(16,185,129,0.1); transform: translateY(-3px); }
    
    .job-info { display: flex; align-items: center; gap: 20px; }
    .worker-avatar { width: 65px; height: 65px; border-radius: 50%; background-color: #e5dfd5; background-size: cover; background-position: center; border: 3px solid #ffffff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .badge-active { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }
    .badge-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }

    .action-group { display: flex; gap: 12px; }
    .btn-outline { border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 12px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-outline:hover { border-color: #10b981; color: #10b981; }
    .btn-primary-sm { background: #10b981; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(16,185,129,0.2); display: flex; align-items: center; gap: 8px; }
    .btn-primary-sm:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(16,185,129,0.3); }

    @media (max-width: 768px) {
        .job-card { flex-direction: column; align-items: flex-start; padding: 20px; }
        .action-group { width: 100%; flex-direction: column; }
        .btn-outline, .btn-primary-sm { width: 100%; justify-content: center; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Active Jobs</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- In Progress Job (Worker Accepted) -->
            <div class="job-card">
                <div class="job-info">
                    <div class="worker-avatar" style="background-image: url('https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop');"></div>
                    <div>
                        <span class="badge-active"><i class="ph-fill ph-circle-notch" style="animation: spin 2s linear infinite;"></i> In Progress</span>
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Electrical Wiring Repair</h3>
                        <p style="font-size: 0.95rem; color: #666;"><i class="ph-fill ph-user"></i> Saman Kumara • <i class="ph-fill ph-map-pin"></i> Colombo 07</p>
                    </div>
                </div>
                <div class="action-group">
                    <a href="payment.php" class="btn-primary-sm"><i class="ph-bold ph-check-circle"></i> Complete & Pay</a>
                </div>
            </div>

            <!-- Pending Job (Waiting for workers) -->
            <div class="job-card">
                <div class="job-info">
                    <div class="worker-avatar" style="background-color: #f0ebe1; background-image: none; display: flex; justify-content: center; align-items: center; color: #a3a3a3; font-size: 2rem;">
                        <i class="ph-fill ph-clock"></i>
                    </div>
                    <div>
                        <span class="badge-pending"><i class="ph-fill ph-hourglass-high"></i> Waiting for Bids</span>
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Plumbing Leak Fix</h3>
                        <p style="font-size: 0.95rem; color: #666;">Requested Today at 10:30 AM</p>
                    </div>
                </div>
                <div class="action-group">
                    <button class="btn-outline" style="color: #ef4444; border-color: #ef4444;"><i class="ph-bold ph-x-circle"></i> Cancel</button>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

</body>
</html>