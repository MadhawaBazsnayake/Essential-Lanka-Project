<?php
// worker/my-gigs.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Gigs - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 1000px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; margin-bottom: 10px; display: block; }
    .back-btn:hover { color: #10b981; }

    .btn-create { background: #1a1a1a; color: white; padding: 14px 25px; border-radius: 50px; font-weight: 800; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .btn-create:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.25); background: #000000; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 40px; border-radius: 35px; }

    /* Gig Card Styling */
    .gig-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .gig-card:hover { border-color: #10b981; box-shadow: 0 12px 35px rgba(16,185,129,0.1); transform: translateY(-3px); }
    
    .gig-info { flex: 1; min-width: 250px; }
    .badge-active { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 12px; }
    .badge-paused { background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 12px; }

    .gig-title { font-size: 1.3rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px; }
    .gig-meta { font-size: 0.95rem; color: #666; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .gig-meta span { display: inline-flex; align-items: center; gap: 5px; }

    .action-group { display: flex; gap: 10px; }
    .btn-icon { width: 45px; height: 45px; border-radius: 15px; border: 2px solid #e5dfd5; background: #ffffff; color: #1a1a1a; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
    .btn-icon:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
    .btn-icon.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }

    @media (max-width: 768px) {
        .gig-card { flex-direction: column; align-items: flex-start; }
        .action-group { width: 100%; border-top: 1px solid #f0ebe1; padding-top: 15px; justify-content: flex-end; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <a href="dashboard.php" class="back-btn fade-up show"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        
        <div class="header-action fade-up show" style="transition-delay: 50ms;">
            <h1 class="page-title">My Gigs</h1>
            <a href="#" class="btn-create"><i class="ph-bold ph-plus"></i> Create New Gig</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Gig Item 1 -->
            <div class="gig-card" id="gig-1">
                <div class="gig-info">
                    <span class="badge-active"><i class="ph-fill ph-check-circle"></i> Active</span>
                    <h3 class="gig-title">Expert Residential Plumbing & Leak Fixes</h3>
                    <div class="gig-meta">
                        <span><i class="ph-fill ph-tag"></i> Plumbing</span>
                        <span><i class="ph-fill ph-money"></i> Base Price: Rs. 1,500</span>
                        <span><i class="ph-fill ph-star" style="color: #f59e0b;"></i> 4.9 (12 Reviews)</span>
                    </div>
                </div>
                <div class="action-group">
                    <button class="btn-icon" title="Edit Gig"><i class="ph-bold ph-pencil-simple"></i></button>
                    <button class="btn-icon" title="Pause Gig" onclick="togglePause(this)"><i class="ph-bold ph-pause"></i></button>
                    <button class="btn-icon delete" title="Delete Gig" onclick="deleteGig('gig-1')"><i class="ph-bold ph-trash"></i></button>
                </div>
            </div>

            <!-- Gig Item 2 -->
            <div class="gig-card" id="gig-2">
                <div class="gig-info">
                    <span class="badge-paused" id="badge-2"><i class="ph-fill ph-pause-circle"></i> Paused</span>
                    <h3 class="gig-title">Complete Home Electrical Wiring</h3>
                    <div class="gig-meta">
                        <span><i class="ph-fill ph-tag"></i> Electrical</span>
                        <span><i class="ph-fill ph-money"></i> Base Price: Rs. 3,000</span>
                        <span><i class="ph-fill ph-star" style="color: #f59e0b;"></i> 5.0 (4 Reviews)</span>
                    </div>
                </div>
                <div class="action-group">
                    <button class="btn-icon" title="Edit Gig"><i class="ph-bold ph-pencil-simple"></i></button>
                    <button class="btn-icon" title="Activate Gig" onclick="togglePause(this)"><i class="ph-bold ph-play"></i></button>
                    <button class="btn-icon delete" title="Delete Gig" onclick="deleteGig('gig-2')"><i class="ph-bold ph-trash"></i></button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function togglePause(btn) {
        const icon = btn.querySelector('i');
        if (icon.classList.contains('ph-pause')) {
            icon.classList.replace('ph-pause', 'ph-play');
            btn.title = "Activate Gig";
            alert("Gig has been paused. Customers will not see this service until you activate it.");
        } else {
            icon.classList.replace('ph-play', 'ph-pause');
            btn.title = "Pause Gig";
            alert("Gig activated! Customers can now see and request this service.");
        }
    }

    function deleteGig(gigId) {
        if (confirm("Are you sure you want to delete this gig? This action cannot be undone.")) {
            const gigCard = document.getElementById(gigId);
            gigCard.style.opacity = '0';
            gigCard.style.transform = 'scale(0.95)';
            setTimeout(() => {
                gigCard.remove();
            }, 300);
        }
    }
</script>

</body>
</html>