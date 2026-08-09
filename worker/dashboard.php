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

// Fetch Worker stats
$stmt = $pdo->prepare("SELECT SUM(amount) as earnings FROM bids b JOIN jobs j ON b.job_id = j.id WHERE b.worker_id = ? AND b.status = 'accepted' AND MONTH(b.created_at) = MONTH(CURRENT_DATE())");
$stmt->execute([$userId]);
$monthlyEarnings = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE assigned_worker_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$jobsCompleted = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT rating FROM worker_profiles WHERE worker_id = ?");
$stmt->execute([$userId]);
$rating = $stmt->fetchColumn() ?: 0.0;
$memberSince = !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'Unknown';

// Fetch New Job Requests matching worker category
$stmt = $pdo->prepare("
    SELECT j.*, c.name_en as category_name 
    FROM jobs j 
    JOIN categories c ON j.category_id = c.id 
    WHERE j.status = 'open' 
    AND j.category_id = (SELECT category_id FROM worker_profiles WHERE worker_id = ?)
    ORDER BY j.created_at DESC LIMIT 5
");
$stmt->execute([$userId]);
$newJobs = $stmt->fetchAll();
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
    .welcome-card { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); color: white; padding: 40px; border-radius: 35px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 40px rgba(0,0,0,0.15); margin-bottom: 40px; position: relative; overflow: hidden; }
    .welcome-card::after { content: ''; position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%); border-radius: 50%; }
    .welcome-card h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; position: relative; z-index: 2; }
    .welcome-card p { font-size: 1.1rem; color: #9ca3af; position: relative; z-index: 2; }
    
    .banner-actions { display: flex; gap: 15px; align-items: center; position: relative; z-index: 50; }
    .btn-notify { width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; display: flex; justify-content: center; align-items: center; font-size: 1.3rem; transition: 0.3s; cursor: pointer; text-decoration: none; position: relative; }
    .btn-notify:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
    .btn-notify.has-badge::after { content: ''; position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; border: 2px solid #1f2937; }

    .logout-btn { background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; border: 1px solid rgba(239, 68, 68, 0.2); }
    .logout-btn:hover { background: #ef4444; color: white; border-color: #ef4444; }

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
    
    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 30px; margin-bottom: 30px; }
    .glass-box h3 { font-size: 1.4rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 1.5px solid #f0ebe1; padding-bottom: 15px; }

    /* Job Alert Item */
    .job-alert-item { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s; }
    .job-alert-item:hover { border-color: #3b82f6; box-shadow: 0 10px 25px rgba(59,130,246,0.1); transform: translateY(-2px); }
    
    .job-meta { display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap; }
    .job-meta span { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #64748b; background: #f8fafc; padding: 6px 12px; border-radius: 8px; font-weight: 600; border: 1px solid #e2e8f0; }

    .btn-accept { background: #3b82f6; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 1rem; box-shadow: 0 10px 20px rgba(59,130,246,0.2); }
    .btn-accept:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 12px 25px rgba(59,130,246,0.3); }
    
    .action-btn { background: #1a1a1a; color: white; padding: 15px 30px; border-radius: 50px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.15); font-size: 1rem; justify-content: center; width: 100%; }
    .action-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0,0,0,0.25); background: #000000; }

    /* Profile Card specific */
    .profile-card-header { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 25px; }
    .profile-avatar-view { width: 90px; height: 90px; border-radius: 50%; border: 4px solid #ffffff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); margin-bottom: 15px; object-fit: cover; background: #e5dfd5; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; color: #a3a3a3; }
    .profile-detail-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f0ebe1; }
    .profile-detail-row:last-of-type { border-bottom: none; margin-bottom: 15px; }
    .profile-detail-label { color: #666; font-size: 0.95rem; font-weight: 600; }
    .profile-detail-value { color: #1a1a1a; font-size: 1rem; font-weight: 800; }

    /* Quick Links */
    .quick-link { display: flex; align-items: center; gap: 15px; padding: 18px 20px; background: #ffffff; border: 1.5px solid #f0ebe1; border-radius: 15px; text-decoration: none; color: #1a1a1a; font-weight: 700; margin-bottom: 12px; transition: 0.3s; }
    .quick-link i { font-size: 1.6rem; color: #3b82f6; }
    .quick-link:hover { border-color: #3b82f6; box-shadow: 0 10px 25px rgba(59,130,246,0.1); transform: translateX(5px); }

    /* Modals (With Blur Background Effect) */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 9999; display: flex; justify-content: center; align-items: center; opacity: 0; pointer-events: none; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); padding: 20px; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-box { background: #ffffff; width: 100%; max-width: 550px; border-radius: 30px; padding: 35px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: translateY(30px) scale(0.95); transition: 0.4s; position: relative; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-box { transform: translateY(0) scale(1); }
    .close-btn { position: absolute; top: 25px; right: 25px; font-size: 1.5rem; color: #a3a3a3; cursor: pointer; transition: 0.3s; border: none; background: transparent; }
    .close-btn:hover { color: #ef4444; transform: rotate(90deg); }
    .modal-title { font-size: 1.6rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 15px; border: 2px solid #e5dfd5; background: #f8fafc; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; font-family: inherit; }
    .form-control:focus { border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
    select.form-control { appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%231a1a1a" viewBox="0 0 256 256"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path></svg>'); background-repeat: no-repeat; background-position: right 15px center; }

    @media (max-width: 992px) {
        .dash-grid, .stats-grid { grid-template-columns: 1fr; }
        .welcome-card { flex-direction: column; text-align: center; gap: 20px; padding: 30px 20px; }
        .welcome-card::after { display: none; }
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
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Worker'); ?>! 🛠️</h1>
                <p>You have new job requests waiting in your area.</p>
            </div>
            <div class="banner-actions">
                <a href="chat.php" class="btn-notify has-badge" title="Messages"><i class="ph-bold ph-chats"></i></a>
                <a href="../logout.php" class="logout-btn"><i class="ph-bold ph-sign-out"></i> Log Out</a>
            </div>
        </div>

        <!-- Top Stats Grid -->
        <div class="stats-grid fade-up show" style="transition-delay: 100ms;">
            <div class="stat-card">
                <div class="stat-icon icon-green"><i class="ph-fill ph-wallet"></i></div>
                <div class="stat-info">
                    <p>This Month</p>
                    <h3>Rs. <?php echo number_format($monthlyEarnings, 2); ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-blue"><i class="ph-fill ph-check-circle"></i></div>
                <div class="stat-info">
                    <p>Jobs Completed</p>
                    <h3><?php echo $jobsCompleted; ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-yellow"><i class="ph-fill ph-star"></i></div>
                <div class="stat-info">
                    <p>Average Rating</p>
                    <h3><?php echo number_format($rating, 1); ?> <span style="font-size: 1rem; color: #666; font-weight: 500;">/ 5.0</span></h3>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            
            <!-- Left Column: New Job Alerts -->
            <div>
                <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                    <h3><i class="ph-fill ph-bell-ringing" style="color: #3b82f6;"></i> New Job Requests Near You</h3>
                    
                    <!-- Job Alerts Dynamic Loop -->
                    <?php if(empty($newJobs)): ?>
                        <div style="text-align:center; padding: 20px; color:#64748b;">No new job requests in your area right now.</div>
                    <?php else: ?>
                        <?php foreach($newJobs as $job): ?>
                        <div class="job-alert-item">
                            <div>
                                <h4 style="font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;"><?php echo htmlspecialchars($job['title']); ?></h4>
                                <div class="job-meta">
                                    <span><i class="ph-fill ph-map-pin"></i> GPS: <?php echo htmlspecialchars($job['location_lat'].",".$job['location_lng']); ?></span>
                                    <span><i class="ph-fill ph-calendar"></i> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                </div>
                            </div>
                            <button class="btn-accept" onclick="openBidModal(<?php echo $job['id']; ?>, '<?php echo addslashes(htmlspecialchars($job['title'])); ?>')"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 30px;">
                        <a href="find-jobs.php" style="color: #3b82f6; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 1.05rem;">View All Available Jobs <i class="ph-bold ph-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Profile & Quick Links -->
            <div>
                <!-- Worker Profile Summary -->
                <div class="glass-box fade-up show" style="transition-delay: 250ms;">
                    <h3><i class="ph-fill ph-user-circle" style="color: #10b981;"></i> My Profile</h3>
                    
                    <div class="profile-card-header">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" class="profile-avatar-view" alt="Profile Picture">
                        <?php else: ?>
                            <div class="profile-avatar-view">
                                <i class="ph-fill ph-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 style="font-size: 1.3rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;">
                            <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                        </h4>
                        <span style="background: rgba(59,130,246,0.1); color: #3b82f6; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;"><i class="ph-fill ph-seal-check"></i> Verified Professional</span>
                    </div>

                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Mobile Number</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Member Since</span>
                        <span class="profile-detail-value"><?php echo $memberSince; ?></span>
                    </div>

                    <button onclick="openModal('profileModal')" class="action-btn" style="margin-top: 10px;"><i class="ph-bold ph-pencil-simple"></i> Edit Profile Settings</button>
                </div>

                <!-- Quick Links -->
                <div class="glass-box fade-up show" style="transition-delay: 300ms;">
                    <h3><i class="ph-fill ph-lightning" style="color: #f59e0b;"></i> Quick Actions</h3>
                    
                    <a href="active-jobs.php" class="quick-link">
                        <i class="ph-fill ph-wrench"></i>
                        My Active Jobs
                    </a>
                    
                    <a href="completed-jobs.php" class="quick-link">
                        <i class="ph-fill ph-check-square"></i>
                        Work History
                    </a>

                    <a href="my-gigs.php" class="quick-link">
                        <i class="ph-fill ph-briefcase"></i>
                        Manage My Gigs
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ================= MODALS ================= -->

<!-- 1. Send Bid Modal -->
<div class="modal-overlay" id="bidModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('bidModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-handshake" style="color: #3b82f6;"></i> Submit Your Bid</h2>
        
        <form action="../api/bid-process.php" method="POST">
            <input type="hidden" name="action" value="submit_bid">
            <input type="hidden" name="job_id" id="bidJobId">
            
            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <p style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Bidding For</p>
                <h4 style="font-size: 1.1rem; font-weight: 800; color: #1a1a1a;" id="bidJobTitle">Job Title</h4>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Your Bid Amount (Rs.)</label>
                    <input type="number" class="form-control" name="bid_amount" placeholder="e.g. 2500" required>
                </div>
                <div class="form-group">
                    <label>Estimated Time</label>
                    <select class="form-control" name="estimated_time" required>
                        <option value="1_hour">1 Hour</option>
                        <option value="2_hours">2 Hours</option>
                        <option value="half_day">Half Day</option>
                        <option value="full_day">Full Day</option>
                        <option value="multiple_days">Multiple Days</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Message / Cover Letter</label>
                <textarea class="form-control" name="cover_letter" rows="3" placeholder="Explain why you are the best fit for this job and when you can start..." required></textarea>
            </div>

            <button type="submit" class="action-btn" style="background: #3b82f6;"><i class="ph-bold ph-paper-plane-right"></i> Send Proposal</button>
        </form>
    </div>
</div>

<!-- 2. Edit Profile Modal -->
<div class="modal-overlay" id="profileModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('profileModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-user-gear" style="color: #10b981;"></i> Edit Profile</h2>
        
        <form action="../api/profile-process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div style="text-align: center; margin-bottom: 20px;">
                <label for="profilePicUpload" style="cursor: pointer; display: inline-block; position: relative;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; border: 3px dashed #10b981; background: #f8fafc; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                        <i class="ph-fill ph-camera-plus" style="font-size: 2rem; color: #10b981;"></i>
                        <!-- Preview Image would go here dynamically -->
                    </div>
                </label>
                <input type="file" id="profilePicUpload" name="profile_picture" accept="image/png, image/jpeg" style="display: none;">
                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-top: 5px;">Update Display Picture</p>
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
                <label>Phone Number</label>
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="action-btn" style="background: #10b981;"><i class="ph-bold ph-floppy-disk"></i> Save Changes</button>
        </form>
    </div>
</div>

<!-- ================= JAVASCRIPT ================= -->
<script>
    // General Modal Functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden'; 
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = 'auto'; 
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }

    // Open Bid Modal with Dynamic Data
    function openBidModal(jobId, jobTitle) {
        document.getElementById('bidJobId').value = jobId;
        document.getElementById('bidJobTitle').innerText = jobTitle;
        openModal('bidModal');
    }
</script>

</body>
</html>