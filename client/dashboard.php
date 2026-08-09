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

// Dummy calculation for Profile Completeness
$profileComplete = 60;
if(!empty($user['email'])) $profileComplete += 15;
if(!empty($user['address'])) $profileComplete += 15;
if(!empty($user['profile_picture'])) $profileComplete += 10;

// Fetch client stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE client_id = ?");
$stmt->execute([$userId]);
$totalRequests = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE client_id = ? AND status = 'in_progress'");
$stmt->execute([$userId]);
$activeJobsCount = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE client_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedJobsCount = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("
    SELECT SUM(COALESCE(b.amount, j.budget, 0)) 
    FROM jobs j 
    LEFT JOIN bids b ON b.job_id = j.id AND b.status = 'accepted'
    WHERE j.client_id = ? AND j.status = 'completed'
");
$stmt->execute([$userId]);
$totalSpent = $stmt->fetchColumn() ?: 0;

// Fetch current & recent requests
$stmt = $pdo->prepare("
    SELECT j.*, c.name_en as category_name, c.icon_class, 
           w.first_name as worker_first, w.last_name as worker_last, w.id as worker_id,
           b.amount as accepted_bid_amount
    FROM jobs j
    LEFT JOIN categories c ON j.category_id = c.id
    LEFT JOIN users w ON j.assigned_worker_id = w.id
    LEFT JOIN bids b ON b.job_id = j.id AND b.worker_id = j.assigned_worker_id AND b.status = 'accepted'
    WHERE j.client_id = ?
    ORDER BY j.created_at DESC LIMIT 5
");
$stmt->execute([$userId]);
$recentRequests = $stmt->fetchAll();

// Fetch recent transactions
$stmt = $pdo->prepare("
    SELECT j.id, j.title, j.created_at, COALESCE(b.amount, j.budget, 0) as amount
    FROM jobs j
    LEFT JOIN bids b ON b.job_id = j.id AND b.status = 'accepted'
    WHERE j.client_id = ? AND j.status = 'completed'
    ORDER BY j.created_at DESC LIMIT 5
");
$stmt->execute([$userId]);
$recentTransactions = $stmt->fetchAll();

// Fetch notifications (pending bids on client's jobs)
$stmt = $pdo->prepare("
    SELECT b.*, j.title as job_title, u.first_name, u.last_name
    FROM bids b
    JOIN jobs j ON b.job_id = j.id
    JOIN users u ON b.worker_id = u.id
    WHERE j.client_id = ? AND b.status = 'pending'
    ORDER BY b.created_at DESC LIMIT 5
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();
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

<style>
    .dash-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .dash-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
    
    /* Welcome Banner */
    .welcome-card { background: radial-gradient(circle at top right, rgba(255,255,255,0.2) 0%, transparent 60%), linear-gradient(135deg, #10b981 0%, #047857 100%); color: white; padding: 40px; border-radius: 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 40px rgba(16,185,129,0.25); margin-bottom: 35px; position: relative; }
    .welcome-card h1 { font-size: 2.6rem; font-weight: 800; margin-bottom: 8px; position: relative; z-index: 2; }
    .welcome-card p { font-size: 1.1rem; opacity: 0.9; position: relative; z-index: 2; }
    
    .banner-actions { display: flex; gap: 15px; align-items: center; position: relative; z-index: 50; }
    
    /* Notification & Chat Buttons */
    .btn-notify { width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; transition: 0.3s; cursor: pointer; position: relative; text-decoration: none; }
    .btn-notify:hover { background: rgba(255,255,255,0.3); transform: scale(1.05); color: white; }
    .btn-notify.has-badge::after { content: ''; position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #059669; }

    .logout-btn { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); color: white; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; cursor: pointer; }
    .logout-btn:hover { background: #ef4444; border-color: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3); }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 35px; }
    .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 35px rgba(0,0,0,0.04); padding: 25px; border-radius: 25px; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .stat-icon { width: 55px; height: 55px; border-radius: 16px; display: flex; justify-content: center; align-items: center; font-size: 1.8rem; }
    .stat-info p { font-size: 0.85rem; color: #666; font-weight: 700; text-transform: uppercase; margin-bottom: 3px; }
    .stat-info h3 { font-size: 1.6rem; font-weight: 800; color: #1a1a1a; }

    /* Main Grid Layout */
    .dash-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
    
    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 30px; margin-bottom: 30px; }
    .glass-box h3 { font-size: 1.3rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 1.5px solid #f0ebe1; padding-bottom: 15px; }
    
    /* Action Buttons */
    .action-btn { background: #1a1a1a; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.15); font-size: 1rem; justify-content: center; }
    .action-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0,0,0,0.25); background: #000000; }
    
    /* Job Item */
    .job-item { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 20px; display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; transition: 0.3s; position: relative; overflow: hidden; z-index: 1; cursor: pointer; text-align: left; width: 100%; font-family: inherit; }
    .job-item::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 5px; background: #f59e0b; }
    .job-item:hover { border-color: #3b82f6; box-shadow: 0 12px 30px rgba(59,130,246,0.1); transform: translateY(-2px); z-index: 10; }
    .job-header { display: flex; justify-content: space-between; align-items: flex-start; width: 100%; }
    .job-title-group { display: flex; align-items: center; gap: 15px; }
    .job-cat-icon { width: 45px; height: 45px; border-radius: 12px; background: #f8fafc; color: #10b981; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; border: 1px solid #e2e8f0; }
    .badge-status { background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-active { background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .job-meta { display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.9rem; color: #666; font-weight: 500; }
    .job-meta span { display: flex; align-items: center; gap: 5px; }

    /* Transactions Table */
    .tx-table { width: 100%; border-collapse: collapse; }
    .tx-table th { text-align: left; padding: 10px; color: #a3a3a3; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid #f0ebe1; }
    .tx-table td { padding: 15px 10px; font-size: 0.95rem; color: #1a1a1a; font-weight: 600; border-bottom: 1px solid #f0ebe1; }
    .tx-table tr:last-child td { border-bottom: none; }
    .tx-success { color: #10b981; background: rgba(16,185,129,0.1); padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; }

    /* Profile Card Advanced */
    .profile-card-header { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 25px; }
    .profile-avatar-view { width: 100px; height: 100px; border-radius: 50%; border: 4px solid #ffffff; box-shadow: 0 10px 25px rgba(16,185,129,0.2); margin-bottom: 15px; object-fit: cover; background: #e5dfd5; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; color: #a3a3a3; }
    .trusted-badge { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 4px 10px rgba(16,185,129,0.3); margin-top: 5px;}
    
    .profile-progress-wrap { background: #f8fafc; border-radius: 12px; padding: 15px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .progress-text { display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 8px; }
    .progress-bar { height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; background: #10b981; border-radius: 10px; transition: width 1s ease-in-out; }

    .profile-detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed #f0ebe1; }
    .profile-detail-row:last-of-type { border-bottom: none; margin-bottom: 15px; }
    .profile-detail-label { color: #666; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .profile-detail-value { color: #1a1a1a; font-size: 0.95rem; font-weight: 700; text-align: right; max-width: 60%; word-break: break-all; }

    /* Modals (With Blur Background Effect) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 9999; display: flex; justify-content: center; align-items: center; opacity: 0; pointer-events: none; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-box { background: #ffffff; width: 90%; max-width: 600px; border-radius: 30px; padding: 35px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: translateY(30px) scale(0.95); transition: 0.4s; position: relative; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-box { transform: translateY(0) scale(1); }
    .close-btn { position: absolute; top: 25px; right: 25px; font-size: 1.5rem; color: #a3a3a3; cursor: pointer; transition: 0.3s; border: none; background: transparent; }
    .close-btn:hover { color: #ef4444; transform: rotate(90deg); }
    .modal-title { font-size: 1.6rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    
    /* Notification Modal Specific Styles */
    .notif-list { display: flex; flex-direction: column; gap: 10px; }
    .notif-item { display: flex; gap: 15px; padding: 15px; border-radius: 15px; border: 1.5px solid #f0ebe1; text-decoration: none; transition: 0.3s; background: #ffffff; cursor: pointer; text-align: left; width: 100%; font-family: inherit; }
    .notif-item:hover { border-color: #3b82f6; transform: translateX(5px); }
    .notif-item.unread { background: rgba(16,185,129,0.05); border-color: rgba(16,185,129,0.3); }
    .notif-icon { width: 45px; height: 45px; border-radius: 12px; background: #f8fafc; color: #64748b; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; flex-shrink: 0; border: 1px solid #e2e8f0; }
    .notif-item.unread .notif-icon { background: #10b981; color: white; border-color: #10b981; }
    .notif-content p { font-size: 0.95rem; color: #1a1a1a; margin-bottom: 5px; line-height: 1.5; }
    .notif-content span { font-size: 0.8rem; color: #a3a3a3; font-weight: 600; }

    /* Job Details Modal Specific Styles */
    .jd-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .jd-icon { width: 60px; height: 60px; border-radius: 18px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; justify-content: center; align-items: center; font-size: 2rem; }
    .jd-info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 15px; padding: 15px; margin-bottom: 20px; }
    .jd-desc { font-size: 0.95rem; color: #4a4a4a; line-height: 1.6; margin-bottom: 20px; }
    .jd-worker { display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1.5px dashed #cbd5e1; border-radius: 15px; margin-bottom: 25px; background: #ffffff;}

    /* Form Styles */
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.9rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 12px 18px; border-radius: 12px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 0.95rem; transition: 0.3s; outline: none; font-weight: 500; font-family: inherit; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    select.form-control { appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%231a1a1a" viewBox="0 0 256 256"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path></svg>'); background-repeat: no-repeat; background-position: right 15px center; }
    
    /* Upload Styles */
    .profile-upload-wrap { text-align: center; margin-bottom: 20px; }
    .profile-img-box { width: 100px; height: 100px; border-radius: 50%; border: 3px dashed #10b981; margin: 0 auto 10px; position: relative; overflow: hidden; background: #f8fafc; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: 0.3s; }
    .profile-img-box:hover { background: rgba(16,185,129,0.1); }
    .profile-img-box img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; display: none; }
    .profile-img-box i { font-size: 2rem; color: #10b981; z-index: 1; }
    
    /* Post Job Multi Image Upload */
    .multi-upload-grid { display: flex; gap: 15px; margin-bottom: 5px; }
    .job-img-upload { flex: 1; border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 12px; padding: 15px; text-align: center; cursor: pointer; transition: 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; position: relative; overflow: hidden; height: 100px; }
    .job-img-upload:hover { border-color: #10b981; background: rgba(16,185,129,0.05); }
    .job-img-upload i { font-size: 1.8rem; color: #94a3b8; }
    .job-img-upload span { font-size: 0.8rem; color: #64748b; font-weight: 600; }
    .job-img-upload img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; display: none; }

    @media (max-width: 992px) {
        .dash-grid, .stats-grid { grid-template-columns: 1fr; }
        .welcome-card { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .stats-grid { grid-template-columns: 1fr; }
        .jd-worker { flex-direction: column; gap: 15px; text-align: center; }
        .jd-worker .action-btn { width: 100%; }
        .jd-info-box > div { grid-template-columns: 1fr !important; }
    }
</style>

<section class="dash-section bg-cream">
    <div class="blob-bg"></div>

    <div class="dash-container">
        
        <!-- Welcome Banner -->
        <div class="welcome-card fade-up show">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Customer'); ?>! 👋</h1>
                <p>Ready to get some things fixed around the house today?</p>
            </div>
            
            <div class="banner-actions">
                <!-- Chat Page Button (NEW) -->
                <a href="chat.php" class="btn-notify has-badge" title="Messages">
                    <i class="ph-bold ph-chats"></i>
                </a>
                
                <!-- Notifications Button -->
                <button class="btn-notify has-badge" onclick="openModal('notifModal')" title="Notifications">
                    <i class="ph-bold ph-bell"></i>
                </button>
                
                <a href="../logout.php" class="logout-btn"><i class="ph-bold ph-sign-out"></i> Log Out</a>
            </div>
        </div>

        <!-- System Stats Grid -->
        <div class="stats-grid fade-up show" style="transition-delay: 50ms;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-fill ph-clipboard-text"></i></div>
                <div class="stat-info">
                    <p>Total Requests</p>
                    <h3><?php echo $totalRequests; ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph-fill ph-wrench"></i></div>
                <div class="stat-info">
                    <p>Active Jobs</p>
                    <h3><?php echo $activeJobsCount; ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="ph-fill ph-check-circle"></i></div>
                <div class="stat-info">
                    <p>Completed Jobs</p>
                    <h3><?php echo $completedJobsCount; ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph-fill ph-wallet"></i></div>
                <div class="stat-info">
                    <p>Total Spent</p>
                    <h3>Rs. <?php echo number_format($totalSpent, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            
            <!-- Left Column: Active Bookings & History -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 100ms;">
                    <h3><i class="ph-fill ph-clock-counter-clockwise" style="color: #10b981;"></i> Current & Recent Requests</h3>
                    
                    <?php if (empty($recentRequests)): ?>
                        <div style="text-align:center; padding: 20px; color:#64748b;">No recent requests found.</div>
                    <?php else: ?>
                        <?php foreach($recentRequests as $request): 
                            $status = $request['status'];
                            $statusLabel = 'Pending Bids';
                            $badgeClass = 'badge-status';
                            $color = '#f59e0b';
                            $icon = $request['icon_class'] ?: 'ph-tools';

                            if ($status === 'in_progress') {
                                $statusLabel = 'In Progress';
                                $badgeClass = 'badge-active';
                                $color = '#3b82f6';
                            } elseif ($status === 'completed') {
                                $statusLabel = 'Completed';
                                $badgeClass = 'badge-active';
                                $color = '#10b981';
                            } elseif ($status === 'cancelled') {
                                $statusLabel = 'Cancelled';
                                $badgeClass = 'badge-status';
                                $color = '#ef4444';
                            }
                            
                            $workerName = 'Not Assigned';
                            $workerIdVal = 'null';
                            if (!empty($request['worker_first'])) {
                                $workerName = $request['worker_first'] . ' ' . $request['worker_last'];
                                $workerIdVal = $request['worker_id'];
                            }
                            
                            $agreedBudget = $request['accepted_bid_amount'] ?? $request['budget'] ?? 0;
                        ?>
                            <button class="job-item" style="border-left: 5px solid <?php echo $color; ?>;" onclick="viewJobDetails('<?php echo $request['id']; ?>', '<?php echo addslashes(htmlspecialchars($request['title'])); ?>', '<?php echo $statusLabel; ?>', '<?php echo addslashes(htmlspecialchars($request['description'])); ?>', 'GPS: <?php echo htmlspecialchars($request['location_lat'].",".$request['location_lng']); ?>', '<?php echo date('M d, Y', strtotime($request['created_at'])); ?>', '<?php echo addslashes(htmlspecialchars($workerName)); ?>', <?php echo $workerIdVal; ?>, '<?php echo $color; ?>', '<?php echo $icon; ?>')">
                                <div class="job-header">
                                    <div class="job-title-group">
                                        <div class="job-cat-icon" style="color: <?php echo $color; ?>;"><i class="ph-fill <?php echo $icon; ?>"></i></div>
                                        <div>
                                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #1a1a1a; margin-bottom: 3px;"><?php echo htmlspecialchars($request['title']); ?></h4>
                                            <p style="font-size: 0.85rem; color: #a3a3a3;">Job ID: #<?php echo $request['id']; ?> <?php if($status === 'in_progress'): ?>&bull; Assigned to: <?php echo htmlspecialchars($workerName); ?><?php endif; ?></p>
                                        </div>
                                    </div>
                                    <span class="<?php echo $badgeClass; ?>" style="color: <?php echo $color; ?>; background: <?php echo $color; ?>1a;"><?php echo $statusLabel; ?></span>
                                </div>
                                <div class="job-meta">
                                    <span><i class="ph-bold ph-calendar-blank"></i> Posted: <?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                                    <span><i class="ph-bold ph-money"></i> Budget: Rs. <?php echo number_format($agreedBudget, 2); ?></span>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 35px;">
                        <button onclick="openModal('serviceModal')" class="action-btn" style="background: #10b981; padding: 15px 30px;"><i class="ph-bold ph-plus-circle"></i> Request New Service</button>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="glass-box fade-up show" style="transition-delay: 150ms;">
                    <h3><i class="ph-fill ph-receipt" style="color: #8b5cf6;"></i> Recent Transactions</h3>
                    <div style="overflow-x: auto;">
                        <table class="tx-table">
                            <thead>
                                <tr>
                                    <th>Job ID</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentTransactions)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center; color:#64748b;">No recent transactions.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($recentTransactions as $tx): ?>
                                    <tr>
                                        <td>#<?php echo $tx['id']; ?></td>
                                        <td><?php echo htmlspecialchars($tx['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($tx['created_at'])); ?></td>
                                        <td>Rs. <?php echo number_format($tx['amount'], 2); ?></td>
                                        <td><span class="tx-success">Paid</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Trusted Profile -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                    <h3><i class="ph-fill ph-shield-check" style="color: #10b981;"></i> Trusted Profile</h3>
                    
                    <div class="profile-card-header">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" class="profile-avatar-view" alt="Profile Picture">
                        <?php else: ?>
                            <div class="profile-avatar-view">
                                <i class="ph-fill ph-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 style="font-size: 1.3rem; font-weight: 800; color: #1a1a1a; margin-bottom: 2px;">
                            <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                        </h4>
                        <span class="trusted-badge"><i class="ph-fill ph-seal-check"></i> Verified Customer</span>
                    </div>

                    <div class="profile-progress-wrap">
                        <div class="progress-text">
                            <span>Profile Completeness</span>
                            <span style="color: #10b981;"><?php echo $profileComplete; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $profileComplete; ?>%;"></div>
                        </div>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label"><i class="ph-fill ph-envelope-simple"></i> Email</span>
                        <span class="profile-detail-value"><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : '<span style="color:#ef4444; font-size:0.8rem;">Required</span>'; ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="profile-detail-label"><i class="ph-fill ph-phone"></i> Mobile</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="profile-detail-label"><i class="ph-fill ph-phone-call"></i> Alt Phone</span>
                        <span class="profile-detail-value"><?php echo !empty($user['alt_phone']) ? htmlspecialchars($user['alt_phone']) : '<span style="color:#a3a3a3; font-size:0.8rem;">Optional</span>'; ?></span>
                    </div>
                    <div class="profile-detail-row">
                        <span class="profile-detail-label"><i class="ph-fill ph-map-pin"></i> Address</span>
                        <span class="profile-detail-value" style="font-size:0.85rem;"><?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : '<span style="color:#ef4444; font-size:0.8rem;">Required</span>'; ?></span>
                    </div>

                    <button onclick="openModal('profileModal')" class="action-btn" style="width: 100%; margin-top: 15px;"><i class="ph-bold ph-pencil-simple"></i> Edit Profile Info</button>
                </div>

                <!-- Support Card -->
                <div class="glass-box fade-up show" style="transition-delay: 250ms; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none;">
                    <h3 style="color: white; border-bottom-color: rgba(255,255,255,0.1);"><i class="ph-fill ph-lifebuoy" style="color: #3b82f6;"></i> Need Help?</h3>
                    <p style="font-size: 0.95rem; color: #94a3b8; line-height: 1.5; margin-bottom: 20px;">Having trouble with a worker or need assistance with a payment?</p>
                    <a href="../contact.php" style="color: #3b82f6; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 5px;">Contact Support <i class="ph-bold ph-arrow-right"></i></a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================= MODALS ================= -->

<!-- 0. Notifications Modal -->
<div class="modal-overlay" id="notifModal">
    <div class="modal-box" style="max-width: 480px;">
        <button class="close-btn" onclick="closeModal('notifModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-bell-ringing" style="color: #f59e0b;"></i> Notifications</h2>
        
        <div class="notif-list">
            <?php if(empty($notifications)): ?>
                <div style="text-align:center; padding: 20px; color:#64748b;">No new notifications.</div>
            <?php else: ?>
                <?php foreach($notifications as $notif): ?>
                    <button class="notif-item unread" onclick="closeModal('notifModal'); window.location.href='chat.php?worker_id=<?php echo $notif['worker_id']; ?>&job_id=<?php echo $notif['job_id']; ?>'">
                        <div class="notif-icon"><i class="ph-fill ph-handshake"></i></div>
                        <div class="notif-content">
                            <p><strong><?php echo htmlspecialchars($notif['first_name'] . ' ' . $notif['last_name']); ?></strong> sent a bid of Rs. <?php echo number_format($notif['amount'], 2); ?> for your job: "<?php echo htmlspecialchars($notif['job_title']); ?>".</p>
                            <span><?php echo date('M d, Y', strtotime($notif['created_at'])); ?></span>
                        </div>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 1. Job Details Modal -->
<div class="modal-overlay" id="jobDetailsModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('jobDetailsModal')"><i class="ph-bold ph-x"></i></button>
        
        <div class="jd-header">
            <div class="jd-icon" id="jdIcon"><i class="ph-fill ph-tools"></i></div>
            <div>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;" id="jdTitle">Job Title</h2>
                <span class="badge-status" id="jdStatus" style="font-size: 0.75rem;">Status</span>
            </div>
        </div>

        <div class="jd-info-box">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <p style="font-size: 0.8rem; color: #a3a3a3; font-weight: 700; text-transform: uppercase;">Location</p>
                    <p style="font-weight: 700; color: #1a1a1a; font-size: 1.05rem;" id="jdLocation">Colombo 07</p>
                </div>
                <div>
                    <p style="font-size: 0.8rem; color: #a3a3a3; font-weight: 700; text-transform: uppercase;">Date / Time</p>
                    <p style="font-weight: 700; color: #1a1a1a; font-size: 1.05rem;" id="jdDate">Aug 04, 2026</p>
                </div>
            </div>
        </div>

        <p class="jd-desc" id="jdDesc">Job description goes here...</p>

        <div class="jd-worker">
            <div>
                <p style="font-size: 0.8rem; color: #a3a3a3; font-weight: 700; text-transform: uppercase; margin-bottom: 5px;">Assigned Worker</p>
                <h4 style="font-weight: 800; color: #1a1a1a; font-size: 1.1rem;" id="jdWorkerName">Not Assigned</h4>
            </div>
            <button id="jdChatBtn" class="action-btn" style="background: #1a1a1a; padding: 10px 20px; font-size: 0.95rem;"><i class="ph-bold ph-chat-circle-dots"></i> Message</button>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button class="action-btn" style="flex: 1; background: #ef4444; box-shadow: 0 10px 20px rgba(239,68,68,0.2);" onclick="closeModal('jobDetailsModal')">Close</button>
            <button class="action-btn" style="flex: 1; background: #3b82f6; box-shadow: 0 10px 20px rgba(59,130,246,0.2);"><i class="ph-bold ph-check"></i> Mark Complete</button>
        </div>
    </div>
</div>

<!-- 2. Request New Service Modal -->
<div class="modal-overlay" id="serviceModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('serviceModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-tools" style="color: #10b981;"></i> Post a New Job</h2>
        
        <form action="../api/job-process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="post_job">
            
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" class="form-control" name="title" placeholder="E.g. Fix a leaking pipe" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Service Category</label>
                    <select class="form-control" name="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="carpentry">Carpentry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Job Location / Address</label>
                <input type="text" class="form-control" name="location" placeholder="E.g. No 15, Kandy Road, Dalugama" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Attach Photos (Up to 2 Optional)</label>
                <div class="multi-upload-grid">
                    <label for="jobImage1" class="job-img-upload">
                        <i class="ph-fill ph-image" id="iconImg1"></i>
                        <span id="textImg1">Photo 1</span>
                        <img id="prevImg1" src="" alt="Preview 1">
                    </label>
                    <input type="file" id="jobImage1" name="job_image_1" accept="image/png, image/jpeg" style="display: none;" onchange="previewMultiImage(event, 'prevImg1', 'iconImg1', 'textImg1')">

                    <label for="jobImage2" class="job-img-upload">
                        <i class="ph-fill ph-image" id="iconImg2"></i>
                        <span id="textImg2">Photo 2</span>
                        <img id="prevImg2" src="" alt="Preview 2">
                    </label>
                    <input type="file" id="jobImage2" name="job_image_2" accept="image/png, image/jpeg" style="display: none;" onchange="previewMultiImage(event, 'prevImg2', 'iconImg2', 'textImg2')">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Explain what needs to be done in detail..." required></textarea>
            </div>

            <button type="submit" class="action-btn" style="width: 100%; background: #10b981; padding: 15px;"><i class="ph-bold ph-paper-plane-tilt"></i> Broadcast Request</button>
        </form>
    </div>
</div>

<!-- 3. Edit Trusted Profile Modal -->
<div class="modal-overlay" id="profileModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('profileModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-shield-check" style="color: #10b981;"></i> Edit Trusted Profile</h2>
        
        <form action="../api/profile-process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="profile-upload-wrap">
                <label for="profilePicUpload" class="profile-img-box">
                    <i class="ph-bold ph-camera-plus" id="uploadIcon" style="<?php echo !empty($user['profile_picture']) ? 'display:none;' : 'display:block;'; ?>"></i>
                    <img id="profilePreview" src="<?php echo !empty($user['profile_picture']) ? '../' . $user['profile_picture'] : ''; ?>" alt="Profile Preview" style="<?php echo !empty($user['profile_picture']) ? 'display:block;' : 'display:none;'; ?>">
                </label>
                <p style="font-size: 0.8rem; color: #666; font-weight: 600;">Upload a clear photo for trust</p>
                <input type="file" id="profilePicUpload" class="upload-input" name="profile_picture" accept="image/png, image/jpeg" onchange="previewSingleImage(event, 'profilePreview', 'uploadIcon')">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="example@email.com" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Primary Mobile</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Alt. Mobile (Optional)</label>
                    <input type="text" class="form-control" name="alt_phone" value="<?php echo htmlspecialchars($user['alt_phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Home Address</label>
                <textarea class="form-control" name="address" rows="2" placeholder="Full residential address..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="action-btn" style="width: 100%; padding: 15px; background: #1a1a1a;"><i class="ph-bold ph-floppy-disk"></i> Update Profile Settings</button>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden'; 
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = 'auto'; 
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }

    function viewJobDetails(jobId, title, status, desc, location, date, worker, workerId, color, icon) {
        document.getElementById('jdTitle').innerText = title;
        
        const statusBadge = document.getElementById('jdStatus');
        statusBadge.innerText = status;
        statusBadge.style.color = color;
        statusBadge.style.background = color + '20'; 

        const iconBox = document.getElementById('jdIcon');
        iconBox.innerHTML = `<i class="ph-fill ${icon}"></i>`;
        iconBox.style.color = color;
        iconBox.style.background = color + '20';

        document.getElementById('jdDesc').innerText = desc;
        document.getElementById('jdLocation').innerText = location;
        document.getElementById('jdDate').innerText = date;
        document.getElementById('jdWorkerName').innerText = worker;

        const chatBtn = document.getElementById('jdChatBtn');
        if(workerId) {
            chatBtn.style.display = 'inline-flex';
            chatBtn.onclick = function() {
                window.location.href = `chat.php?worker_id=${workerId}&job_id=${jobId}`;
            };
        } else {
            chatBtn.style.display = 'none';
        }

        openModal('jobDetailsModal');
    }

    function previewSingleImage(event, previewId, iconId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        const icon = document.getElementById(iconId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(icon) icon.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewMultiImage(event, previewId, iconId, textId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        const icon = document.getElementById(iconId);
        const text = document.getElementById(textId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                text.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>