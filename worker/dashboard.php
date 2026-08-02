<?php
// worker/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// වර්කර් කෙනෙක් ලෙස ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}

// Database Connection
include '../config/db.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Worker';

// දත්ත සමුදායෙන් වර්කර්ගේ විස්තර ලබා ගැනීම
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .dash-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .dash-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
    
    /* Welcome Banner Card */
    .welcome-card { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); color: white; padding: 40px; border-radius: 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 40px rgba(0,0,0,0.15); margin-bottom: 40px; }
    .welcome-card h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
    .welcome-card p { font-size: 1.1rem; color: #9ca3af; }
    
    .logout-btn { background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
    .logout-btn:hover { background: #ef4444; color: white; }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 35px rgba(0,0,0,0.04); padding: 30px; border-radius: 30px; display: flex; align-items: center; gap: 20px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    
    .stat-icon { width: 65px; height: 65px; border-radius: 20px; display: flex; justify-content: center; align-items: center; font-size: 2rem; }
    .icon-green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .icon-blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .icon-yellow { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    
    .stat-info p { font-size: 0.95rem; color: #666; font-weight: 600; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-info h3 { font-size: 1.8rem; font-weight: 800; color: #1a1a1a; }

    /* Main Grid Layout */
    .dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    
    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 30px; }
    .glass-box h3 { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }

    /* Job Alert Item */
    .job-alert-item { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 20px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; transition: 0.3s; }
    .job-alert-item:hover { border-color: #3b82f6; box-shadow: 0 10px 25px rgba(59,130,246,0.1); }
    
    .job-meta { display: flex; gap: 15px; margin-top: 8px; flex-wrap: wrap; }
    .job-meta span { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #666; background: #f8fafc; padding: 4px 10px; border-radius: 8px; }

    .btn-accept { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
    .btn-accept:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(59,130,246,0.3); }

    /* Quick Links */
    .quick-link { display: flex; align-items: center; gap: 15px; padding: 15px 20px; background: #f8fafc; border-radius: 15px; text-decoration: none; color: #1a1a1a; font-weight: 700; margin-bottom: 12px; transition: 0.3s; }
    .quick-link i { font-size: 1.5rem; color: #10b981; }
    .quick-link:hover { background: #10b981; color: white; }
    .quick-link:hover i { color: white; }

    @media (max-width: 992px) {
        .dash-grid, .stats-grid { grid-template-columns: 1fr; }
        .welcome-card { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
    }
    @media (max-width: 768px) {
        .job-alert-item { flex-direction: column; align-items: flex-start; gap: 15px; }
        .btn-accept { width: 100%; justify-content: center; }
    }
</style>

<section class="dash-section bg-cream">
    <div class="blob-bg"></div>

    <div class="dash-container">
        
        <!-- Welcome Banner -->
        <div class="welcome-card fade-up show">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! 🛠️</h1>
                <p>You have new job requests waiting in your area.</p>
            </div>
            <div>
                <a href="../logout.php" class="logout-btn"><i class="ph-bold ph-sign-out"></i> Log Out</a>
            </div>
        </div>

        <!-- Top Stats Grid -->
        <div class="stats-grid fade-up show" style="transition-delay: 100ms;">
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="ph-fill ph-wallet"></i></div>
                <div class="stat-info">
                    <p>This Month</p>
                    <h3>Rs. 45,000</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="ph-fill ph-check-circle"></i></div>
                <div class="stat-info">
                    <p>Jobs Completed</p>
                    <h3>24</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-yellow"><i class="ph-fill ph-star"></i></div>
                <div class="stat-info">
                    <p>Average Rating</p>
                    <h3>4.8 <span style="font-size: 1rem; color: #666; font-weight: 500;">/ 5.0</span></h3>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            
            <!-- Left Column: New Job Alerts -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                    <h3><i class="ph-fill ph-bell-ringing" style="color: #3b82f6;"></i> New Job Requests Near You</h3>
                    
                    <!-- Job Item 1 -->
                    <div class="job-alert-item">
                        <div>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;">House Painting (Exterior)</h4>
                            <div class="job-meta">
                                <span><i class="ph-fill ph-map-pin"></i> Colombo 07 (2km away)</span>
                                <span><i class="ph-fill ph-calendar"></i> Requested Today</span>
                            </div>
                        </div>
                        <button class="btn-accept" onclick="acceptJob()"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                    </div>

                    <!-- Job Item 2 -->
                    <div class="job-alert-item">
                        <div>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;">Plumbing Leak Repair</h4>
                            <div class="job-meta">
                                <span><i class="ph-fill ph-map-pin"></i> Nugegoda (5km away)</span>
                                <span><i class="ph-fill ph-calendar"></i> Urgent (ASAP)</span>
                            </div>
                        </div>
                        <button class="btn-accept" onclick="acceptJob()"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                    </div>

                    <div style="text-align: center; margin-top: 25px;">
                        <a href="find-jobs.php" style="color: #3b82f6; font-weight: 700; text-decoration: none;">View All Available Jobs <i class="ph-bold ph-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Links -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 300ms;">
                    <h3><i class="ph-fill ph-lightning" style="color: #f59e0b;"></i> Quick Actions</h3>
                    
                    <a href="active-jobs.php" class="quick-link">
                        <i class="ph-bold ph-wrench"></i>
                        My Active Jobs
                    </a>
                    
                    <a href="completed-jobs.php" class="quick-link">
                        <i class="ph-bold ph-check-square"></i>
                        Work History
                    </a>
                    
                    <a href="profile.php" class="quick-link">
                        <i class="ph-bold ph-user-circle"></i>
                        Update Profile
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<script>
    function acceptJob() {
        alert("Bid sent successfully! The customer will contact you soon.");
    }
</script>

</body>
</html>