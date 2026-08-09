<?php
// services.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// සක්‍රීය කාණ්ඩ (Active Categories) ලබා ගැනීම
$catStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC");
$categories = $catStmt->fetchAll();

// ජනප්‍රිය සේවාවන් (Popular Gigs) ලබා ගැනීම
$gigStmt = $pdo->query("
    SELECT g.*, u.first_name, u.last_name, u.profile_picture, wp.rating, wp.total_reviews 
    FROM gigs g
    JOIN users u ON g.worker_id = u.id
    JOIN worker_profiles wp ON u.id = wp.worker_id
    WHERE g.status = 'active'
    ORDER BY wp.rating DESC, wp.total_reviews DESC
    LIMIT 6
");
$gigs = $gigStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .services-section { padding: 60px 5%; min-height: 85vh; background: #f8fafc; position: relative; overflow: hidden; }
        .services-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
        
        .page-header { text-align: center; margin-bottom: 60px; }
        .page-header h1 { font-size: 3rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; }
        .page-header p { color: #64748b; font-size: 1.15rem; max-width: 600px; margin: 0 auto; line-height: 1.6; }

        /* Categories Grid */
        .section-title { font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; margin-bottom: 60px; }
        
        .cat-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 30px 20px; text-align: center; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); text-decoration: none; display: block; color: inherit; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .cat-card:hover { transform: translateY(-8px); border-color: #3b82f6; box-shadow: 0 15px 35px rgba(59,130,246,0.1); }
        
        .cat-icon { width: 70px; height: 70px; border-radius: 20px; background: rgba(59,130,246,0.1); color: #3b82f6; display: flex; justify-content: center; align-items: center; font-size: 2.2rem; margin: 0 auto 20px; transition: 0.3s; }
        .cat-card:hover .cat-icon { background: #3b82f6; color: #ffffff; transform: scale(1.1) rotate(5deg); }
        
        .cat-title { font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 5px; }
        .cat-subtitle { font-size: 0.9rem; color: #94a3b8; font-weight: 600; }

        /* Featured Gigs Grid */
        .gigs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin-bottom: 50px; }
        .gig-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 25px; transition: 0.3s; display: flex; flex-direction: column; }
        .gig-card:hover { border-color: #10b981; box-shadow: 0 15px 35px rgba(16,185,129,0.1); transform: translateY(-5px); }
        
        .gig-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .gig-category { background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; }
        .gig-price { font-size: 1.25rem; font-weight: 800; color: #10b981; }
        
        .gig-title { font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; line-height: 1.4; }
        .gig-desc { color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .gig-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .worker-info { display: flex; align-items: center; gap: 10px; }
        .worker-avatar { width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 0.9rem; font-weight: 700; color: #64748b; }
        .worker-name { font-size: 0.95rem; font-weight: 700; color: #1e293b; display: block; }
        .worker-rating { font-size: 0.8rem; color: #f59e0b; display: flex; align-items: center; gap: 3px; font-weight: 600; }
        
        .btn-hire { background: #1e293b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .btn-hire:hover { background: #10b981; }

        @media (max-width: 768px) {
            .page-header h1 { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<section class="services-section">
    <div class="blob-bg"></div>
    
    <div class="services-container">
        
        <div class="page-header fade-up show">
            <h1>Professional Services</h1>
            <p>From quick fixes to major renovations, find verified experts for every task around your home and office.</p>
        </div>

        <!-- Categories Section -->
        <h2 class="section-title fade-up show"><i class="ph-fill ph-squares-four" style="color: #3b82f6;"></i> Browse Categories</h2>
        <div class="cat-grid fade-up show" style="transition-delay: 100ms;">
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="jobs-feed.php?category=<?php echo $cat['id']; ?>" class="cat-card">
                        <div class="cat-icon"><i class="<?php echo htmlspecialchars($cat['icon_class']); ?>"></i></div>
                        <h3 class="cat-title"><?php echo htmlspecialchars($cat['name_en']); ?></h3>
                        <div class="cat-subtitle"><?php echo htmlspecialchars($cat['name_si']); ?></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No categories found.</p>
            <?php endif; ?>
        </div>

        <!-- Featured Gigs Section -->
        <h2 class="section-title fade-up show" style="transition-delay: 200ms;"><i class="ph-fill ph-star" style="color: #f59e0b;"></i> Featured Professional Gigs</h2>
        <div class="gigs-grid fade-up show" style="transition-delay: 300ms;">
            <?php if (count($gigs) > 0): ?>
                <?php foreach ($gigs as $gig): ?>
                    <div class="gig-card">
                        <div class="gig-header">
                            <span class="gig-category"><?php echo htmlspecialchars($gig['category']); ?></span>
                            <span class="gig-price">Rs. <?php echo number_format($gig['base_price'], 2); ?></span>
                        </div>
                        
                        <h3 class="gig-title"><?php echo htmlspecialchars($gig['title']); ?></h3>
                        <p class="gig-desc"><?php echo htmlspecialchars($gig['description']); ?></p>
                        
                        <div class="gig-footer">
                            <div class="worker-info">
                                <?php if (!empty($gig['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($gig['profile_picture']); ?>" class="worker-avatar" alt="Avatar">
                                <?php else: ?>
                                    <div class="worker-avatar">
                                        <?php echo substr($gig['first_name'], 0, 1) . substr($gig['last_name'], 0, 1); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="worker-name"><?php echo htmlspecialchars($gig['first_name'] . ' ' . $gig['last_name']); ?></span>
                                    <span class="worker-rating"><i class="ph-fill ph-star"></i> <?php echo number_format($gig['rating'], 1); ?> (<?php echo $gig['total_reviews']; ?>)</span>
                                </div>
                            </div>
                            
                            <a href="worker-profile.php?id=<?php echo $gig['worker_id']; ?>" class="btn-hire">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 20px; border: 1.5px dashed #cbd5e1;">
                    <p style="color: #64748b;">More professional services will be listed soon.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

</body>
</html>