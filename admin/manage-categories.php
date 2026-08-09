<?php
// admin/manage-categories.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: ඇඩ්මින් කෙනෙක්ද කියලා බලනවා
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .admin-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .admin-container { max-width: 1100px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .admin-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 35px; }
    .glass-box h3 { font-size: 1.4rem; font-weight: 800; color: #1a1a1a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    /* Form Styles */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 15px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    
    .btn-submit { background: #10b981; color: white; padding: 15px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(16,185,129,0.2); display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16,185,129,0.3); }

    /* Category Grid */
    .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    
    .category-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 20px; text-align: center; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column; align-items: center; position: relative; }
    .category-card:hover { border-color: #10b981; box-shadow: 0 15px 35px rgba(16,185,129,0.1); transform: translateY(-5px); }
    
    .cat-icon { width: 70px; height: 70px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 20px; display: flex; justify-content: center; align-items: center; font-size: 2.2rem; margin-bottom: 15px; transition: 0.3s; }
    .category-card:hover .cat-icon { background: #10b981; color: white; }
    
    .cat-title { font-size: 1.15rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px; }
    .cat-meta { font-size: 0.85rem; color: #a3a3a3; font-weight: 600; margin-bottom: 20px; }

    .cat-actions { display: flex; gap: 10px; width: 100%; border-top: 1.5px solid #f0ebe1; padding-top: 15px; }
    .btn-action { flex: 1; border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 8px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; }
    .btn-action:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
    .btn-action.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }

    @media (max-width: 992px) {
        .admin-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="admin-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="admin-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Manage Categories</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="admin-grid">
            
            <!-- Add New Category Form -->
            <div class="glass-box fade-up show" style="transition-delay: 100ms;">
                <h3><i class="ph-fill ph-plus-circle" style="color: #10b981;"></i> Add New Category</h3>
                
                <form action="#" method="POST" onsubmit="addCategory(event)">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" class="form-control" placeholder="E.g. Electrical, Plumbing" required>
                    </div>

                    <div class="form-group">
                        <label>Icon Class (Phosphor Icons)</label>
                        <input type="text" class="form-control" placeholder="E.g. ph-lightning, ph-drop" required>
                    </div>

                    <div class="form-group">
                        <label>Description (Optional)</label>
                        <textarea class="form-control" placeholder="Short description about this service..." style="resize: vertical; min-height: 100px;"></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="ph-bold ph-floppy-disk"></i> Save Category
                    </button>
                </form>
            </div>

            <!-- Existing Categories Grid -->
            <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                <h3 style="margin-bottom: 25px;"><i class="ph-fill ph-squares-four" style="color: #3b82f6;"></i> Existing Categories</h3>
                
                <div class="category-grid">
                    
                    <!-- Category 1 -->
                    <div class="category-card" id="cat-1">
                        <div class="cat-icon"><i class="ph-fill ph-lightning"></i></div>
                        <h4 class="cat-title">Electrical</h4>
                        <span class="cat-meta">45 Active Workers</span>
                        
                        <div class="cat-actions">
                            <button class="btn-action" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                            <button class="btn-action delete" title="Delete" onclick="deleteCategory('cat-1')"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                    <!-- Category 2 -->
                    <div class="category-card" id="cat-2">
                        <div class="cat-icon"><i class="ph-fill ph-drop"></i></div>
                        <h4 class="cat-title">Plumbing</h4>
                        <span class="cat-meta">32 Active Workers</span>
                        
                        <div class="cat-actions">
                            <button class="btn-action" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                            <button class="btn-action delete" title="Delete" onclick="deleteCategory('cat-2')"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                    <!-- Category 3 -->
                    <div class="category-card" id="cat-3">
                        <div class="cat-icon"><i class="ph-fill ph-broom"></i></div>
                        <h4 class="cat-title">Cleaning</h4>
                        <span class="cat-meta">80 Active Workers</span>
                        
                        <div class="cat-actions">
                            <button class="btn-action" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                            <button class="btn-action delete" title="Delete" onclick="deleteCategory('cat-3')"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                    <!-- Category 4 -->
                    <div class="category-card" id="cat-4">
                        <div class="cat-icon"><i class="ph-fill ph-hammer"></i></div>
                        <h4 class="cat-title">Carpentry</h4>
                        <span class="cat-meta">15 Active Workers</span>
                        
                        <div class="cat-actions">
                            <button class="btn-action" title="Edit"><i class="ph-bold ph-pencil-simple"></i></button>
                            <button class="btn-action delete" title="Delete" onclick="deleteCategory('cat-4')"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function addCategory(event) {
        event.preventDefault();
        const form = event.target;
        const nameEn = form.querySelector('input[placeholder*="Electrical"]').value;
        const iconClass = form.querySelector('input[placeholder*="ph-lightning"]').value;

        const formData = new FormData();
        formData.append('action', 'add_category');
        formData.append('name_en', nameEn);
        formData.append('icon_class', iconClass);

        fetch('../api/admin-process.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success') {
                window.location.reload();
            }
        });
    }

    function deleteCategory(catId) {
        if(confirm("Are you sure you want to delete this category?")) {
            const formData = new FormData();
            formData.append('action', 'delete_category');
            formData.append('cat_id', catId.replace('cat-', ''));

            fetch('../api/admin-process.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const card = document.getElementById(catId);
                    if(card) card.remove();
                } else {
                    alert(data.message);
                }
            });
        }
    }
</script>

</body>
</html>