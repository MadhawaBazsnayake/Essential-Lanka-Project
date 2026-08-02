<?php
// worker/portfolio.php
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
    <title>My Portfolio - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 1100px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; margin-bottom: 10px; display: block; }
    .back-btn:hover { color: #10b981; }

    .btn-create { background: #1a1a1a; color: white; padding: 14px 25px; border-radius: 50px; font-weight: 800; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    .btn-create:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.25); background: #000000; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 40px; border-radius: 35px; }

    /* Portfolio Grid Styling */
    .portfolio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
    
    .portfolio-card { background: #ffffff; border: 1.5px solid #f0ebe1; border-radius: 25px; overflow: hidden; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column; }
    .portfolio-card:hover { border-color: #10b981; box-shadow: 0 15px 40px rgba(16,185,129,0.12); transform: translateY(-5px); }
    
    .portfolio-img { width: 100%; height: 220px; background-size: cover; background-position: center; position: relative; }
    .portfolio-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); backdrop-filter: blur(5px); color: #1a1a1a; padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; gap: 5px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .portfolio-content { padding: 25px; display: flex; flex-direction: column; flex: 1; }
    .portfolio-title { font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 8px; }
    .portfolio-desc { font-size: 0.95rem; color: #666; line-height: 1.6; margin-bottom: 20px; flex: 1; }
    
    .portfolio-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f0ebe1; padding-top: 15px; }
    .date-text { font-size: 0.85rem; color: #a3a3a3; font-weight: 600; }
    
    .action-group { display: flex; gap: 10px; }
    .btn-icon { width: 40px; height: 40px; border-radius: 12px; border: 2px solid #e5dfd5; background: #ffffff; color: #1a1a1a; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; cursor: pointer; transition: 0.3s; }
    .btn-icon:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
    .btn-icon.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { font-size: 4rem; color: #d1d5db; margin-bottom: 20px; }

    @media (max-width: 768px) {
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
        .portfolio-grid { grid-template-columns: 1fr; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <a href="dashboard.php" class="back-btn fade-up show"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        
        <div class="header-action fade-up show" style="transition-delay: 50ms;">
            <h1 class="page-title">My Portfolio</h1>
            <a href="#" class="btn-create" onclick="addProject()"><i class="ph-bold ph-upload-simple"></i> Upload Project</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <div class="portfolio-grid">
                
                <!-- Portfolio Item 1 -->
                <div class="portfolio-card" id="proj-1">
                    <div class="portfolio-img" style="background-image: url('https://images.unsplash.com/photo-1585704032915-c3400ca199e7?q=80&w=600&auto=format&fit=crop');">
                        <div class="portfolio-badge"><i class="ph-fill ph-camera"></i> 3 Photos</div>
                    </div>
                    <div class="portfolio-content">
                        <h3 class="portfolio-title">Modern Bathroom Plumbing</h3>
                        <p class="portfolio-desc">Complete pipeline installation and bathroom fitting replacements for a new residential house in Colombo 05.</p>
                        
                        <div class="portfolio-actions">
                            <span class="date-text">Added Aug 12, 2026</span>
                            <div class="action-group">
                                <button class="btn-icon" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                                <button class="btn-icon delete" title="Delete" onclick="deleteProject('proj-1')"><i class="ph-bold ph-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Item 2 -->
                <div class="portfolio-card" id="proj-2">
                    <div class="portfolio-img" style="background-image: url('https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=600&auto=format&fit=crop');">
                        <div class="portfolio-badge"><i class="ph-fill ph-camera"></i> 1 Photo</div>
                    </div>
                    <div class="portfolio-content">
                        <h3 class="portfolio-title">Ceiling Lighting Setup</h3>
                        <p class="portfolio-desc">Installed hidden LED strip lights and modern chandeliers for a living room interior renovation project.</p>
                        
                        <div class="portfolio-actions">
                            <span class="date-text">Added Jul 28, 2026</span>
                            <div class="action-group">
                                <button class="btn-icon" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                                <button class="btn-icon delete" title="Delete" onclick="deleteProject('proj-2')"><i class="ph-bold ph-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Placeholder -->
                <div class="portfolio-card" style="border: 2px dashed #cbd5e1; background: transparent; cursor: pointer; justify-content: center; align-items: center; min-height: 350px;" onclick="addProject()">
                    <div class="empty-state">
                        <i class="ph-bold ph-plus-circle empty-icon" style="color: #10b981;"></i>
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px;">Add New Project</h3>
                        <p style="color: #666; font-size: 0.9rem;">Showcase your best work to attract more customers.</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<script>
    function deleteProject(projId) {
        if (confirm("Are you sure you want to delete this portfolio item?")) {
            const card = document.getElementById(projId);
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                card.remove();
            }, 300);
        }
    }

    function addProject() {
        alert("Upload form will open here! (You can implement the modal or redirect to an upload page).");
    }
</script>

</body>
</html>