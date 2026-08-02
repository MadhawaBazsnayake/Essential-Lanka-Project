<?php
// client/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පාරිභෝගිකයෙක් ලෙස ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Customer';

// දත්ත සමුදායෙන් පාරිභෝගිකයාගේ විස්තර ලබා ගැනීම
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Dashboard Specific Inline CSS -->
<style>
    .dash-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .dash-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
    
    /* Welcome Banner Card */
    .welcome-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 40px; border-radius: 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 40px rgba(16,185,129,0.2); margin-bottom: 40px; }
    .welcome-card h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
    .welcome-card p { font-size: 1.1rem; opacity: 0.9; }
    
    /* Grid Layout */
    .dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    
    /* Glass Cards */
    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 30px; margin-bottom: 30px; }
    .glass-box h3 { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    
    /* Quick Action Buttons */
    .action-btn { background: #10b981; color: white; padding: 15px 30px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 20px rgba(16,185,129,0.2); }
    .action-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(16,185,129,0.3); background: #059669; }
    
    .logout-btn { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
    .logout-btn:hover { background: #ef4444; color: white; }

    /* Job Item List */
    .job-item { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 20px 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; transition: 0.3s; }
    .job-item:hover { border-color: #10b981; box-shadow: 0 8px 25px rgba(16,185,129,0.08); }
    
    .badge-status { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; }

    @media (max-width: 992px) {
        .dash-grid { grid-template-columns: 1fr; }
        .welcome-card { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
    }
</style>

<section class="dash-section bg-cream">
    <div class="blob-bg"></div>

    <div class="dash-container">
        
        <!-- Welcome Banner -->
        <div class="welcome-card fade-up show">
            <div>
                <h1>Hello, <?php echo htmlspecialchars($user['first_name']); ?>! 👋</h1>
                <p>Manage your service requests and find trusted workers near you.</p>
            </div>
            <div>
                <a href="../logout.php" class="logout-btn"><i class="ph-bold ph-sign-out"></i> Log Out</a>
            </div>
        </div>

        <div class="dash-grid">
            
            <!-- Left Column: Active Bookings & History -->
            <div>
                <div class="glass-box fade-up show">
                    <h3><i class="ph-fill ph-clock-counter-clockwise" style="color: #10b981;"></i> Recent Service Requests</h3>
                    
                    <!-- Sample Job Item (මෙය පසුව Database දත්ත සමඟ ඩైనමික් කළ හැක) -->
                    <div class="job-item">
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 700; color: #1a1a1a; margin-bottom: 4px;">Electrical Wiring Repair</h4>
                            <p style="font-size: 0.9rem; color: #666;"><i class="ph-fill ph-map-pin"></i> Colombo 07 • 2 hours ago</p>
                        </div>
                        <div>
                            <span class="badge-status">Pending Bids</span>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 25px;">
                        <a href="#" class="action-btn"><i class="ph-bold ph-plus-circle"></i> Request New Service</a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Profile & Quick Info -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 100ms;">
                    <h3><i class="ph-fill ph-user-circle" style="color: #10b981;"></i> My Profile</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 4px;">Full Name</p>
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #1a1a1a;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <p style="font-size: 0.9rem; color: #666; margin-bottom: 4px;">Mobile Number</p>
                        <h4 style="font-size: 1.1rem; font-weight: 700; color: #1a1a1a;"><?php echo htmlspecialchars($user['phone']); ?></h4>
                    </div>

                    <a href="#" class="action-btn" style="width: 100%; justify-content: center; background: #1a1a1a;"><i class="ph-bold ph-pencil-simple"></i> Edit Profile</a>
                </div>
            </div>

        </div>

    </div>
</section>

</body>
</html>