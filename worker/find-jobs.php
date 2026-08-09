// worker/find-jobs.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$userId = $_SESSION['user_id'];

// Get worker profile details to query jobs matching category
$stmt = $pdo->prepare("SELECT category_id FROM worker_profiles WHERE worker_id = ?");
$stmt->execute([$userId]);
$catId = $stmt->fetchColumn();

// If we have search/filter queries
$search = $_GET['search'] ?? '';
$jobs = [];

if ($catId) {
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT j.*, u.first_name, u.last_name 
            FROM jobs j 
            JOIN users u ON j.client_id = u.id 
            WHERE j.status = 'open' AND j.category_id = ? 
            AND (j.title LIKE ? OR j.description LIKE ?)
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$catId, "%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->prepare("
            SELECT j.*, u.first_name, u.last_name 
            FROM jobs j 
            JOIN users u ON j.client_id = u.id 
            WHERE j.status = 'open' AND j.category_id = ?
            ORDER BY j.created_at DESC
        ");
        $stmt->execute([$catId]);
    }
    $jobs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - Essential Lanka</title>
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

    /* Search & Filter Bar */
    .filter-bar { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 35px rgba(0,0,0,0.05); padding: 20px; border-radius: 25px; margin-bottom: 35px; display: flex; gap: 15px; align-items: center; }
    .search-input { flex: 1; position: relative; }
    .search-input i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-size: 1.2rem; color: #a3a3a3; }
    .search-input input { width: 100%; padding: 15px 20px 15px 50px; border-radius: 15px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; outline: none; transition: 0.3s; }
    .search-input input:focus { border-color: #3b82f6; }
    
    .filter-btn { background: #1a1a1a; color: white; padding: 15px 25px; border-radius: 15px; font-weight: 700; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .filter-btn:hover { background: #000000; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 40px; border-radius: 35px; }
    
    /* Available Job Card */
    .job-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .job-card:hover { border-color: #3b82f6; box-shadow: 0 12px 35px rgba(59,130,246,0.1); transform: translateY(-3px); }
    
    .job-info { flex: 1; }
    
    .badge-urgent { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 12px; }
    .badge-standard { background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 12px; }

    .job-meta { display: flex; gap: 15px; margin-top: 15px; flex-wrap: wrap; }
    .job-meta span { display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem; color: #666; background: #f8fafc; padding: 8px 15px; border-radius: 10px; border: 1px solid #e2e8f0; }

    .job-desc { color: #666; font-size: 0.95rem; line-height: 1.6; margin-top: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .action-group { display: flex; flex-direction: column; gap: 12px; min-width: 180px; }
    
    .price-tag { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; text-align: center; margin-bottom: 5px; }
    
    .btn-bid { background: #3b82f6; color: white; padding: 14px 25px; border-radius: 50px; font-weight: 800; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(59,130,246,0.2); display: flex; align-items: center; gap: 8px; justify-content: center; width: 100%; font-size: 1.05rem; }
    .btn-bid:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(59,130,246,0.3); background: #2563eb; }

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

    @media (max-width: 768px) {
        .filter-bar { flex-direction: column; }
        .search-input, .filter-btn { width: 100%; }
        .job-card { flex-direction: column; }
        .action-group { width: 100%; margin-top: 15px; border-top: 1px solid #f0ebe1; padding-top: 20px; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Find Jobs</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="find-jobs.php" class="filter-bar fade-up show" style="transition-delay: 50ms;">
            <div class="search-input">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Search by keyword..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="filter-btn"><i class="ph-bold ph-magnifying-glass"></i> Search</button>
        </form>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <?php if(empty($jobs)): ?>
                <div style="text-align:center; padding: 40px; color:#64748b;">
                    <i class="ph-fill ph-magnifying-glass" style="font-size: 3rem; margin-bottom: 10px; color:#cbd5e1;"></i>
                    <p>No open jobs found in your service category.</p>
                </div>
            <?php else: ?>
                <?php foreach($jobs as $job): ?>
                <div class="job-card">
                    <div class="job-info">
                        <span class="badge-standard"><i class="ph-fill ph-lightning"></i> Open Request</span>
                        <h3 style="font-size: 1.4rem; font-weight: 800; color: #1a1a1a;"><?php echo htmlspecialchars($job['title']); ?></h3>
                        
                        <p class="job-desc"><?php echo htmlspecialchars($job['description']); ?></p>
                        
                        <div class="job-meta">
                            <span><i class="ph-fill ph-map-pin"></i> GPS: <?php echo htmlspecialchars($job['location_lat'].",".$job['location_lng']); ?></span>
                            <span><i class="ph-fill ph-calendar"></i> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                            <span><i class="ph-fill ph-user"></i> <?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?></span>
                        </div>
                    </div>
                    
                    <div class="action-group">
                        <div class="price-tag">Budget: Rs. <?php echo number_format($job['budget'], 2); ?></div>
                        <button class="btn-bid" onclick="openBidModal(<?php echo $job['id']; ?>, '<?php echo addslashes(htmlspecialchars($job['title'])); ?>')"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</section>

<!-- Send Bid Modal -->
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

            <button type="submit" class="action-btn btn-bid" style="background: #3b82f6;"><i class="ph-bold ph-paper-plane-right"></i> Send Proposal</button>
        </form>
    </div>
</div>

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

    function openBidModal(jobId, jobTitle) {
        document.getElementById('bidJobId').value = jobId;
        document.getElementById('bidJobTitle').innerText = jobTitle;
        openModal('bidModal');
    }
</script>

</body>
</html>