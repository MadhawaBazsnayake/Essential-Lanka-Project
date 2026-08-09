<?php
// client/my-requests.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT j.*, w.first_name as worker_first, w.last_name as worker_last,
           r.id as review_id
    FROM jobs j
    LEFT JOIN users w ON j.assigned_worker_id = w.id
    LEFT JOIN reviews r ON r.job_id = j.id AND r.reviewer_id = ?
    WHERE j.client_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$userId, $userId]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests History - Essential Lanka</title>
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
    
    /* Request Card Styling */
    .request-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .request-card:hover { border-color: #10b981; box-shadow: 0 12px 35px rgba(16,185,129,0.1); transform: translateY(-3px); }
    
    .request-info { display: flex; align-items: center; gap: 20px; }
    .icon-box-lg { width: 65px; height: 65px; border-radius: 20px; background-color: #f4f4f5; display: flex; justify-content: center; align-items: center; font-size: 2rem; color: #666; }
    
    .badge-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }
    .badge-canceled { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }

    .action-group { display: flex; gap: 12px; }
    .btn-outline { border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 12px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .btn-outline:hover { border-color: #10b981; color: #10b981; }
    .btn-review { background: #f59e0b; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; box-shadow: 0 8px 20px rgba(245,158,11,0.2); display: flex; align-items: center; gap: 8px; }
    .btn-review:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(245,158,11,0.3); background: #d97706; }

    @media (max-width: 768px) {
        .request-card { flex-direction: column; align-items: flex-start; padding: 20px; }
        .action-group { width: 100%; flex-direction: column; }
        .btn-outline, .btn-review { width: 100%; justify-content: center; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Requests History</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <?php if(empty($requests)): ?>
                <div style="text-align:center; padding: 40px; color:#64748b;">
                    <i class="ph-fill ph-clipboard-text" style="font-size: 3rem; margin-bottom: 10px; color:#cbd5e1;"></i>
                    <p>You have not made any service requests yet.</p>
                </div>
            <?php else: ?>
                <?php foreach($requests as $req): 
                    $isCompleted = ($req['status'] === 'completed');
                    $isCancelled = ($req['status'] === 'cancelled');
                    $isInProgress = ($req['status'] === 'in_progress');
                    
                    $badgeClass = 'badge-completed';
                    $statusLabel = ucfirst($req['status']);
                    $icon = 'ph-check-circle';
                    $color = '#10b981';

                    if ($isCancelled) {
                        $badgeClass = 'badge-canceled';
                        $icon = 'ph-x-circle';
                        $color = '#ef4444';
                    } elseif ($isInProgress) {
                        $badgeClass = 'badge-completed'; // Re-use styling but blue
                        $icon = 'ph-wrench';
                        $color = '#3b82f6';
                    } elseif ($req['status'] === 'open') {
                        $statusLabel = 'Pending Bids';
                        $icon = 'ph-clock';
                        $color = '#f59e0b';
                    }
                ?>
                    <div class="request-card">
                        <div class="request-info">
                            <div class="icon-box-lg" style="color: <?php echo $color; ?>; background: <?php echo $color; ?>1a;">
                                <i class="ph-fill <?php echo $icon; ?>"></i>
                            </div>
                            <div>
                                <span class="<?php echo $badgeClass; ?>" style="color: <?php echo $color; ?>; background: <?php echo $color; ?>1a;"><?php echo $statusLabel; ?></span>
                                <h3 style="font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;"><?php echo htmlspecialchars($req['title']); ?></h3>
                                <p style="font-size: 0.95rem; color: #666;">
                                    Posted on <?php echo date('d M Y', strtotime($req['created_at'])); ?> 
                                    <?php if(!empty($req['worker_first'])): ?>
                                        • Worker: <?php echo htmlspecialchars($req['worker_first'] . ' ' . $req['worker_last']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="action-group">
                            <?php if($isCompleted): ?>
                                <?php if(empty($req['review_id'])): ?>
                                    <a href="review.php?job_id=<?php echo $req['id']; ?>" class="btn-review"><i class="ph-fill ph-star"></i> Leave a Review</a>
                                <?php else: ?>
                                    <button class="btn-outline" disabled style="opacity: 0.6; cursor: not-allowed;"><i class="ph-fill ph-check"></i> Reviewed</button>
                                <?php endif; ?>
                            <?php elseif($isCancelled): ?>
                                <a href="post-job.php" class="btn-outline"><i class="ph-bold ph-arrow-counter-clockwise"></i> Re-book</a>
                            <?php else: ?>
                                <a href="chat.php?job_id=<?php echo $req['id']; ?>&worker_id=<?php echo $req['assigned_worker_id']; ?>" class="btn-outline"><i class="ph-bold ph-chats"></i> Message Worker</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</section>

</body>
</html>