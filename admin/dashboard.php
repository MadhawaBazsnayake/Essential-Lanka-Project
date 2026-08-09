<?php
// admin/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: ඇඩ්මින් කෙනෙක්ද කියලා බලනවා
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // ඇඩ්මින් නොවේ නම් සාමාන්‍ය ලොගින් එකට යවනවා
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['name'] ?? 'Administrator';

require_once '../config/db.php';

// Total Customers
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
$totalCustomers = $stmt->fetchColumn() ?: 0;

// Verified Workers
$stmt = $pdo->query("SELECT COUNT(*) FROM worker_profiles WHERE verification_status = 'verified'");
$verifiedWorkers = $stmt->fetchColumn() ?: 0;

// Pending Approvals
$stmt = $pdo->query("SELECT COUNT(*) FROM worker_profiles WHERE verification_status = 'pending'");
$pendingApprovals = $stmt->fetchColumn() ?: 0;

// Total Jobs Done
$stmt = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'completed'");
$totalJobsDone = $stmt->fetchColumn() ?: 0;

// Fetch pending approvals list
$stmt = $pdo->query("
    SELECT wp.*, u.first_name, u.last_name, c.name_en as cat_name 
    FROM worker_profiles wp 
    JOIN users u ON wp.worker_id = u.id 
    JOIN categories c ON wp.category_id = c.id 
    WHERE wp.verification_status = 'pending' 
    ORDER BY u.created_at DESC LIMIT 5
");
$pendingWorkers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .admin-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .admin-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
    
    /* Admin Banner */
    .admin-banner { background: #1a1a1a; color: white; padding: 40px; border-radius: 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 40px rgba(0,0,0,0.15); margin-bottom: 40px; }
    .admin-banner h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 15px; }
    .admin-banner h1 i { color: #10b981; }
    .admin-banner p { font-size: 1.1rem; color: #a3a3a3; }
    
    .logout-btn { background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
    .logout-btn:hover { background: #ef4444; color: white; }

    /* Overview Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 35px rgba(0,0,0,0.04); padding: 30px; border-radius: 30px; display: flex; align-items: center; gap: 20px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    
    .stat-icon { width: 60px; height: 60px; border-radius: 18px; display: flex; justify-content: center; align-items: center; font-size: 2rem; }
    
    .stat-info p { font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-info h3 { font-size: 1.8rem; font-weight: 800; color: #1a1a1a; }

    /* Main Grid Layout */
    .admin-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    
    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 30px; }
    .glass-box h3 { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }

    /* Action List */
    .action-item { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 20px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; transition: 0.3s; }
    .action-item:hover { border-color: #f59e0b; box-shadow: 0 10px 25px rgba(245,158,11,0.1); }
    
    .btn-review { background: #f59e0b; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-review:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245,158,11,0.3); }

    /* Menu Links */
    .menu-link { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; background: #f8fafc; border-radius: 18px; text-decoration: none; color: #1a1a1a; font-weight: 700; margin-bottom: 15px; transition: 0.3s; border: 1.5px solid transparent; }
    .menu-link-content { display: flex; align-items: center; gap: 15px; }
    .menu-link-content i { font-size: 1.5rem; color: #10b981; }
    .menu-link:hover { background: #ffffff; border-color: #10b981; box-shadow: 0 10px 25px rgba(16,185,129,0.1); transform: translateX(5px); }

    @media (max-width: 992px) {
        .admin-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .admin-banner { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .action-item { flex-direction: column; align-items: flex-start; gap: 15px; }
        .btn-review { width: 100%; justify-content: center; }
    }
</style>

<section class="admin-section bg-cream">
    <div class="blob-bg"></div>

    <div class="admin-container">
        
        <!-- Banner -->
        <div class="admin-banner fade-up show">
            <div>
                <h1><i class="ph-fill ph-shield-check"></i> System Admin</h1>
                <p>Welcome back, <?php echo htmlspecialchars($adminName); ?>. Here is what's happening today.</p>
            </div>
            <div>
                <a href="../logout.php" class="logout-btn"><i class="ph-bold ph-sign-out"></i> Secure Logout</a>
            </div>
        </div>

        <!-- System Stats -->
        <div class="stats-grid fade-up show" style="transition-delay: 100ms;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph-fill ph-users"></i></div>
                <div class="stat-info">
                    <p>Total Customers</p>
                    <h3><?php echo number_format($totalCustomers); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-fill ph-wrench"></i></div>
                <div class="stat-info">
                    <p>Verified Workers</p>
                    <h3><?php echo number_format($verifiedWorkers); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="ph-fill ph-hourglass-high"></i></div>
                <div class="stat-info">
                    <p>Pending Approvals</p>
                    <h3 style="color: #f59e0b;"><?php echo number_format($pendingApprovals); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="ph-fill ph-briefcase"></i></div>
                <div class="stat-info">
                    <p>Total Jobs Done</p>
                    <h3><?php echo number_format($totalJobsDone); ?></h3>
                </div>
            </div>
        </div>

        <div class="admin-grid">
            
            <!-- Left: Needs Attention -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                    <h3><i class="ph-fill ph-warning-circle" style="color: #f59e0b;"></i> Needs Your Attention</h3>
                    <p style="color: #666; margin-bottom: 25px; font-size: 0.95rem;">The following workers have uploaded their NICs and are waiting for your approval.</p>
                    
                    <?php if (empty($pendingWorkers)): ?>
                        <div style="text-align: center; padding: 20px; color: #64748b;">No pending worker verifications.</div>
                    <?php else: ?>
                        <?php foreach ($pendingWorkers as $worker): ?>
                            <div class="action-item">
                                <div>
                                    <h4 style="font-size: 1.15rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;"><?php echo htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']); ?></h4>
                                    <p style="font-size: 0.9rem; color: #666;"><i class="ph-fill ph-user"></i> Category: <?php echo htmlspecialchars($worker['cat_name']); ?> • Status: <?php echo htmlspecialchars($worker['verification_status']); ?></p>
                                </div>
                                <a href="verify-worker.php?worker_id=<?php echo $worker['worker_id']; ?>" class="btn-review"><i class="ph-bold ph-eye"></i> Review ID</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Admin Navigation -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 300ms;">
                    <h3><i class="ph-fill ph-squares-four" style="color: #10b981;"></i> System Menus</h3>
                    
                    <a href="manage-users.php" class="menu-link">
                        <div class="menu-link-content">
                            <i class="ph-fill ph-users-three"></i>
                            <span>Manage Users</span>
                        </div>
                        <i class="ph-bold ph-caret-right" style="color: #a3a3a3;"></i>
                    </a>

                    <a href="verifications.php" class="menu-link">
                        <div class="menu-link-content">
                            <i class="ph-fill ph-identification-badge"></i>
                            <span>Verifications</span>
                        </div>
                        <i class="ph-bold ph-caret-right" style="color: #a3a3a3;"></i>
                    </a>

                    <a href="manage-jobs.php" class="menu-link">
                        <div class="menu-link-content">
                            <i class="ph-fill ph-briefcase"></i>
                            <span>All Job Requests</span>
                        </div>
                        <i class="ph-bold ph-caret-right" style="color: #a3a3a3;"></i>
                    </a>
                    
                    <a href="settings.php" class="menu-link">
                        <div class="menu-link-content">
                            <i class="ph-fill ph-gear"></i>
                            <span>System Settings</span>
                        </div>
                        <i class="ph-bold ph-caret-right" style="color: #a3a3a3;"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

</body>
</html>