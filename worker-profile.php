<?php
// worker-profile.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$workerId = $_GET['id'] ?? null;

if (!$workerId) {
    die("Worker ID is required.");
}

// වර්කර්ගේ මූලික දත්ත සහ ප්‍රොෆයිල් දත්ත ලබාගැනීම
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, u.profile_picture, u.phone, 
           wp.bio, wp.rating, wp.total_reviews, wp.verification_status, 
           c.name_en AS category_name, c.icon_class
    FROM users u
    JOIN worker_profiles wp ON u.id = wp.worker_id
    JOIN categories c ON wp.category_id = c.id
    WHERE u.id = ? AND u.role = 'worker'
");
$stmt->execute([$workerId]);
$worker = $stmt->fetch();

if (!$worker) {
    die("Worker not found or not active.");
}

// වර්කර්ගේ Gigs ලබාගැනීම
$gigStmt = $pdo->prepare("SELECT * FROM gigs WHERE worker_id = ? AND status = 'active'");
$gigStmt->execute([$workerId]);
$gigs = $gigStmt->fetchAll();

// වර්කර්ගේ Reviews ලබාගැනීම
$reviewStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.first_name, u.last_name 
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.worker_id = ?
    ORDER BY r.created_at DESC LIMIT 10
");
$reviewStmt->execute([$workerId]);
$reviews = $reviewStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']); ?> - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-section { padding: 40px 5%; min-height: 85vh; background: #f8fafc; }
        .profile-container { max-width: 1000px; margin: 0 auto; }
        
        /* Profile Header Card */
        .profile-header { background: #ffffff; border-radius: 25px; padding: 40px; border: 1.5px solid #e2e8f0; display: flex; gap: 30px; align-items: center; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .profile-avatar { width: 130px; height: 130px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 3rem; color: #64748b; font-weight: 800; border: 4px solid #ffffff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); object-fit: cover; flex-shrink: 0; }
        
        .profile-info { flex: 1; }
        .profile-name { font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
        .verified-badge { background: rgba(16,185,129,0.1); color: #10b981; font-size: 0.8rem; padding: 4px 10px; border-radius: 50px; display: inline-flex; align-items: center; gap: 4px; font-weight: 700; }
        .profile-cat { color: #3b82f6; font-weight: 700; display: flex; align-items: center; gap: 5px; margin-bottom: 15px; font-size: 1.1rem; }
        
        .profile-stats { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; }
        .stat-item { background: #f8fafc; padding: 8px 15px; border-radius: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 8px; border: 1px solid #f1f5f9; }
        .stat-item i { font-size: 1.2rem; color: #f59e0b; }
        
        .profile-bio { color: #475569; line-height: 1.6; font-size: 0.95rem; }

        .btn-contact { background: #1e293b; color: white; padding: 12px 25px; border-radius: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; flex-shrink: 0; }
        .btn-contact:hover { background: #3b82f6; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59,130,246,0.2); }

        /* Section Titles */
        .section-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

        /* Gigs Grid */
        .gigs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .gig-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 15px; padding: 20px; transition: 0.3s; }
        .gig-card:hover { border-color: #3b82f6; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .gig-price { color: #10b981; font-weight: 800; font-size: 1.2rem; margin-bottom: 10px; }
        .gig-title { font-size: 1.1rem; font-weight: 800; margin-bottom: 8px; color: #1e293b; }
        .gig-desc { font-size: 0.9rem; color: #64748b; line-height: 1.5; }

        /* Reviews List */
        .review-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 15px; padding: 20px; margin-bottom: 15px; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .reviewer-name { font-weight: 700; color: #1e293b; }
        .review-date { font-size: 0.8rem; color: #94a3b8; }
        .stars { color: #f59e0b; margin-bottom: 10px; }
        .review-text { color: #475569; font-size: 0.95rem; line-height: 1.5; }

        @media (max-width: 768px) {
            .profile-header { flex-direction: column; text-align: center; }
            .profile-stats { justify-content: center; }
        }
    </style>
</head>
<body>

<section class="profile-section">
    <div class="profile-container">
        
        <!-- Profile Header -->
        <div class="profile-header">
            <?php if (!empty($worker['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($worker['profile_picture']); ?>" class="profile-avatar" alt="Avatar">
            <?php else: ?>
                <div class="profile-avatar"><?php echo substr($worker['first_name'], 0, 1) . substr($worker['last_name'], 0, 1); ?></div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']); ?>
                    <?php if ($worker['verification_status'] === 'verified'): ?>
                        <span class="verified-badge"><i class="ph-fill ph-seal-check"></i> Verified</span>
                    <?php endif; ?>
                </h1>
                
                <div class="profile-cat">
                    <i class="<?php echo htmlspecialchars($worker['icon_class']); ?>"></i> 
                    <?php echo htmlspecialchars($worker['category_name']); ?> Professional
                </div>
                
                <div class="profile-stats">
                    <span class="stat-item"><i class="ph-fill ph-star"></i> <?php echo number_format($worker['rating'], 1); ?> / 5.0 (<?php echo $worker['total_reviews']; ?> Reviews)</span>
                    <span class="stat-item"><i class="ph-fill ph-check-circle" style="color:#10b981;"></i> <?php echo rand(10, 50); ?> Jobs Completed</span>
                </div>
                
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($worker['bio'] ?? 'No bio provided.')); ?></p>
            </div>

            <a href="client/chat.php?worker_id=<?php echo $workerId; ?>" class="btn-contact"><i class="ph-bold ph-chat-teardrop-dots"></i> Message Worker</a>
        </div>

        <!-- Offered Services (Gigs) -->
        <h2 class="section-title"><i class="ph-fill ph-briefcase" style="color: #3b82f6;"></i> Offered Services</h2>
        <div class="gigs-grid">
            <?php if (count($gigs) > 0): ?>
                <?php foreach ($gigs as $gig): ?>
                    <div class="gig-card">
                        <div class="gig-price">Rs. <?php echo number_format($gig['base_price'], 2); ?></div>
                        <h3 class="gig-title"><?php echo htmlspecialchars($gig['title']); ?></h3>
                        <p class="gig-desc"><?php echo htmlspecialchars($gig['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #64748b; padding: 20px 0;">This professional hasn't listed specific services yet.</p>
            <?php endif; ?>
        </div>

        <!-- Client Reviews -->
        <h2 class="section-title"><i class="ph-fill ph-star" style="color: #f59e0b;"></i> Client Reviews (<?php echo count($reviews); ?>)</h2>
        <div>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="reviewer-name"><?php echo htmlspecialchars($review['first_name'] . ' ' . substr($review['last_name'], 0, 1) . '.'); ?></span>
                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <div class="stars">
                            <?php 
                            for($i=1; $i<=5; $i++) {
                                echo $i <= $review['rating'] ? '<i class="ph-fill ph-star"></i>' : '<i class="ph-regular ph-star"></i>';
                            }
                            ?>
                        </div>
                        <p class="review-text">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #64748b; padding: 20px 0;">No reviews available yet.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

</body>
</html>