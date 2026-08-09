// worker/completed-jobs.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// වර්කර් කෙනෙක් ලෙස ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$userId = $_SESSION['user_id'];

// Get worker profile details
$stmt = $pdo->prepare("SELECT rating FROM worker_profiles WHERE worker_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch();
$rating = $profile['rating'] ?? 0.0;

// Total Completed Jobs
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE assigned_worker_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalJobsCompleted = $stmt->fetchColumn() ?: 0;

// Total Earned (Sum of bid amounts for completed jobs assigned to this worker)
$stmt = $pdo->prepare("
    SELECT SUM(b.amount) 
    FROM bids b 
    JOIN jobs j ON b.job_id = j.id 
    WHERE b.worker_id = ? AND b.status = 'accepted' AND j.status = 'completed'
");
$stmt->execute([$userId]);
$totalEarned = $stmt->fetchColumn() ?: 0;

// Fetch completed jobs
$stmt = $pdo->prepare("
    SELECT j.*, u.first_name, u.last_name, r.rating as review_rating, b.amount as bid_amount
    FROM jobs j
    JOIN users u ON j.client_id = u.id
    LEFT JOIN bids b ON b.job_id = j.id AND b.worker_id = ? AND b.status = 'accepted'
    LEFT JOIN reviews r ON r.job_id = j.id AND r.worker_id = ?
    WHERE j.assigned_worker_id = ? AND j.status = 'completed'
    ORDER BY j.created_at DESC
");
$stmt->execute([$userId, $userId, $userId]);
$completedJobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Jobs - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 1100px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; display: flex; align-items: center; gap: 10px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; margin-bottom: 15px; }
    .back-btn:hover { color: #10b981; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 35px; }

    /* Performance Stats Row */
    .perf-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .perf-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; padding: 25px; border-radius: 20px; display: flex; align-items: center; gap: 15px; color: white; box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .perf-card.highlight { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-color: #059669; box-shadow: 0 15px 30px rgba(16,185,129,0.25); }
    
    .perf-icon { width: 55px; height: 55px; border-radius: 15px; display: flex; justify-content: center; align-items: center; font-size: 1.8rem; background: rgba(255,255,255,0.1); backdrop-filter: blur(5px); }
    .perf-info p { font-size: 0.85rem; color: #cbd5e1; font-weight: 600; text-transform: uppercase; margin-bottom: 2px; }
    .perf-card.highlight .perf-info p { color: rgba(255,255,255,0.8); }
    .perf-info h4 { font-size: 1.6rem; font-weight: 800; color: #ffffff; }

    /* History Card Styling */
    .history-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 25px; position: relative; overflow: hidden; }
    .history-card::before { content: ''; position: absolute; left: 0; top: 0; width: 6px; height: 100%; background: #10b981; }
    .history-card:hover { border-color: #cbd5e1; box-shadow: 0 15px 40px rgba(0,0,0,0.04); transform: translateY(-3px); }
    
    .client-avatar { width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; border: 2px solid #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; color: #64748b; font-weight: 800; flex-shrink: 0; }
    
    .job-info { flex: 1; min-width: 250px; display: flex; gap: 20px; align-items: center; }
    .job-details { flex: 1; }

    .job-title { font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px; }
    .client-name { font-size: 0.9rem; color: #64748b; font-weight: 600; margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }
    
    .job-meta-tags { display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
    .meta-tag { background: #f8fafc; color: #475569; padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #e2e8f0; }
    .meta-tag.success { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }

    .earnings-box { text-align: right; }
    .earnings-box p { font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
    .earnings-box h3 { font-size: 1.4rem; font-weight: 800; color: #10b981; margin-bottom: 8px; }
    
    .star-rating { display: flex; gap: 3px; color: #f59e0b; font-size: 1rem; justify-content: flex-end; }
    
    .action-group { display: flex; gap: 10px; align-items: center; margin-top: 10px; justify-content: flex-end; }
    .btn-outline { padding: 8px 16px; border-radius: 50px; border: 2px solid #e2e8f0; background: transparent; color: #64748b; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 5px; }
    .btn-outline:hover { background: #f8fafc; color: #1a1a1a; border-color: #cbd5e1; }

    @media (max-width: 768px) {
        .job-info { flex-direction: column; align-items: flex-start; gap: 15px; }
        .earnings-box { text-align: left; margin-top: 10px; padding-top: 15px; border-top: 1px solid #f0ebe1; width: 100%; }
        .star-rating, .action-group { justify-content: flex-start; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <a href="dashboard.php" class="back-btn fade-up show"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        
        <div class="header-action fade-up show" style="transition-delay: 50ms;">
            <h1 class="page-title"><i class="ph-fill ph-check-circle" style="color: #10b981;"></i> Completed Jobs</h1>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Quick Earnings & Stats -->
            <div class="perf-row">
                <div class="perf-card highlight">
                    <div class="perf-icon"><i class="ph-fill ph-wallet"></i></div>
                    <div class="perf-info">
                        <p>Total Earned</p>
                        <h4>Rs. <?php echo number_format($totalEarned, 2); ?></h4>
                    </div>
                </div>
                <div class="perf-card">
                    <div class="perf-icon"><i class="ph-fill ph-briefcase"></i></div>
                    <div class="perf-info">
                        <p>Jobs Completed</p>
                        <h4><?php echo $totalJobsCompleted; ?></h4>
                    </div>
                </div>
                <div class="perf-card">
                    <div class="perf-icon" style="color: #f59e0b;"><i class="ph-fill ph-star"></i></div>
                    <div class="perf-info">
                        <p>Overall Rating</p>
                        <h4><?php echo number_format($rating, 1); ?> / 5.0</h4>
                    </div>
                </div>
            </div>

            <!-- List of Completed Jobs -->
            <?php if (count($completedJobs) > 0): ?>
                <?php foreach ($completedJobs as $job): ?>
                    <div class="history-card">
                        <div class="job-info">
                            <div class="client-avatar">
                                <?php 
                                    $initials = strtoupper(substr($job['first_name'], 0, 1) . substr($job['last_name'], 0, 1));
                                    echo htmlspecialchars($initials);
                                ?>
                            </div>
                            <div class="job-details">
                                <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="client-name"><i class="ph-fill ph-user"></i> <?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?> &bull; <i class="ph-fill ph-map-pin"></i> GPS: <?php echo htmlspecialchars($job['location_lat'].",".$job['location_lng']); ?></p>
                                
                                <div class="job-meta-tags">
                                    <span class="meta-tag"><i class="ph-bold ph-calendar-blank"></i> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                    <span class="meta-tag"><i class="ph-bold ph-hash"></i> Job ID: #<?php echo $job['id']; ?></span>
                                    <span class="meta-tag success"><i class="ph-bold ph-check"></i> Completed</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="earnings-box">
                            <p>Earned Amount</p>
                            <h3>Rs. <?php echo number_format($job['bid_amount'] ?? $job['budget'] ?? 0, 2); ?></h3>
                            <?php if(!empty($job['review_rating'])): ?>
                                <div class="star-rating">
                                    <?php for($i = 0; $i < (int)$job['review_rating']; $i++): ?>
                                        <i class="ph-fill ph-star"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <div class="action-group">
                                <button class="btn-outline" onclick="alert('Downloading Receipt for Job #<?php echo $job['id']; ?>...')"><i class="ph-bold ph-download-simple"></i> Receipt</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 40px; color:#64748b;">
                    <i class="ph-fill ph-check-circle" style="font-size: 3rem; margin-bottom: 10px; color:#cbd5e1;"></i>
                    <p>No completed jobs found.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
</body>
</html>