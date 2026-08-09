<?php
// worker/my-gigs.php
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

// Database එකෙන් අදාළ වර්කර්ගේ Gigs සියල්ල ලබාගැනීම
try {
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE worker_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $gigs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching gigs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Gigs - Essential Lanka</title>
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

    .btn-create { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 14px 28px; border-radius: 50px; font-weight: 800; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 10px 25px rgba(16,185,129,0.3); border: none; cursor: pointer; font-size: 1.05rem; }
    .btn-create:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(16,185,129,0.4); }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 35px; }

    /* Performance Stats Row */
    .perf-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .perf-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 20px; border-radius: 20px; display: flex; align-items: center; gap: 15px; }
    .perf-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; }
    .perf-info p { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
    .perf-info h4 { font-size: 1.4rem; font-weight: 800; color: #1a1a1a; }

    /* Gig Card Styling */
    .gig-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 25px; position: relative; overflow: hidden; }
    .gig-card::before { content: ''; position: absolute; left: 0; top: 0; width: 6px; height: 100%; background: #e2e8f0; transition: 0.3s; }
    .gig-card.active-gig::before { background: #10b981; }
    .gig-card:hover { border-color: #cbd5e1; box-shadow: 0 15px 40px rgba(0,0,0,0.04); transform: translateY(-3px); }
    
    .gig-img-placeholder { width: 100px; height: 100px; border-radius: 15px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; color: #cbd5e1; flex-shrink: 0; object-fit: cover; }
    
    .gig-info { flex: 1; min-width: 250px; display: flex; gap: 20px; align-items: center; }
    .gig-details { flex: 1; }

    .gig-title { font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 8px; }
    
    .gig-meta-tags { display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
    .meta-tag { background: #f8fafc; color: #475569; padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #e2e8f0; }
    .meta-tag.highlight { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2); }

    .action-group { display: flex; gap: 10px; align-items: center; }
    .btn-icon { width: 45px; height: 45px; border-radius: 12px; border: 2px solid #e5dfd5; background: #ffffff; color: #64748b; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
    .btn-icon:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59,130,246,0.05); }
    .btn-icon.edit:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.05); }
    .btn-icon.delete:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }

    /* Custom Toggle Switch */
    .switch-wrap { display: flex; flex-direction: column; align-items: center; gap: 5px; margin-right: 15px; }
    .switch-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
    .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #10b981; }
    input:checked + .slider:before { transform: translateX(24px); }

    /* Modals */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); z-index: 9999; display: flex; justify-content: center; align-items: center; opacity: 0; pointer-events: none; transition: 0.3s; padding: 20px; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-box { background: #ffffff; width: 100%; max-width: 600px; border-radius: 25px; padding: 35px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: translateY(30px) scale(0.95); transition: 0.4s; position: relative; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-box { transform: translateY(0) scale(1); }
    .close-btn { position: absolute; top: 25px; right: 25px; font-size: 1.5rem; color: #94a3b8; cursor: pointer; transition: 0.3s; border: none; background: transparent; }
    .close-btn:hover { color: #ef4444; transform: rotate(90deg); }
    
    .modal-title { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.9rem; color: #1e293b; }
    .form-control { width: 100%; padding: 12px 18px; border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc; font-size: 0.95rem; transition: 0.3s; outline: none; font-family: inherit; }
    .form-control:focus { border-color: #10b981; background: #ffffff; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    
    .img-upload-box { border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 15px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; position: relative; overflow: hidden; }
    .img-upload-box:hover { border-color: #10b981; background: rgba(16,185,129,0.05); }
    .img-upload-box i { font-size: 2.5rem; color: #94a3b8; margin-bottom: 10px; }
    .img-upload-box span { display: block; font-size: 0.85rem; color: #64748b; font-weight: 600; }
    .img-upload-box img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; display: none; }

    @media (max-width: 768px) {
        .gig-info { flex-direction: column; align-items: flex-start; gap: 15px; }
        .gig-img-placeholder { width: 100%; height: 150px; }
        .action-group { width: 100%; border-top: 1px solid #f0ebe1; padding-top: 20px; justify-content: space-between; }
        .switch-wrap { flex-direction: row; width: 100%; justify-content: space-between; margin-bottom: 10px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <a href="dashboard.php" class="back-btn fade-up show"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        
        <div class="header-action fade-up show" style="transition-delay: 50ms;">
            <h1 class="page-title"><i class="ph-fill ph-briefcase" style="color: #10b981;"></i> Manage My Gigs</h1>
            <button class="btn-create" onclick="openModal('newGigModal')"><i class="ph-bold ph-plus-circle"></i> Create New Gig</button>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Quick Performance Stats -->
            <div class="perf-row">
                <div class="perf-card">
                    <div class="perf-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="ph-fill ph-eye"></i></div>
                    <div class="perf-info">
                        <p>Total Views</p>
                        <h4>0</h4>
                    </div>
                </div>
                <div class="perf-card">
                    <div class="perf-icon" style="background: rgba(16,185,129,0.1); color: #10b981;"><i class="ph-fill ph-check-circle"></i></div>
                    <div class="perf-info">
                        <p>Completed Orders</p>
                        <h4>0</h4>
                    </div>
                </div>
            </div>

            <!-- Dynamically Displaying Gigs from Database -->
            <?php if (count($gigs) > 0): ?>
                <?php foreach ($gigs as $gig): ?>
                    <div class="gig-card <?php echo $gig['status'] === 'active' ? 'active-gig' : ''; ?>" id="gig-<?php echo $gig['id']; ?>">
                        <div class="gig-info">
                            
                            <!-- Gig Image -->
                            <?php if (!empty($gig['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($gig['image_path']); ?>" class="gig-img-placeholder" alt="Gig Image">
                            <?php else: ?>
                                <div class="gig-img-placeholder"><i class="ph-fill ph-image"></i></div>
                            <?php endif; ?>
                            
                            <div class="gig-details">
                                <h3 class="gig-title" id="title-<?php echo $gig['id']; ?>"><?php echo htmlspecialchars($gig['title']); ?></h3>
                                <div class="gig-meta-tags">
                                    <span class="meta-tag" id="cat-<?php echo $gig['id']; ?>">
                                        <i class="ph-fill ph-tag" style="color: #3b82f6;"></i> <?php echo ucfirst(htmlspecialchars($gig['category'])); ?>
                                    </span>
                                    <span class="meta-tag highlight">
                                        <i class="ph-fill ph-money"></i> Base: Rs. <span id="price-<?php echo $gig['id']; ?>"><?php echo htmlspecialchars($gig['base_price']); ?></span>
                                    </span>
                                </div>
                                <!-- Hidden description for edit function -->
                                <div id="desc-<?php echo $gig['id']; ?>" style="display: none;"><?php echo htmlspecialchars($gig['description']); ?></div>
                            </div>
                        </div>
                        
                        <div class="action-group">
                            <div class="switch-wrap">
                                <span class="switch-label" id="status-text-<?php echo $gig['id']; ?>"><?php echo ucfirst($gig['status']); ?></span>
                                <label class="switch">
                                    <!-- Toggle checkbox -->
                                    <input type="checkbox" <?php echo $gig['status'] === 'active' ? 'checked' : ''; ?> onchange="toggleGigStatus(this, 'gig-<?php echo $gig['id']; ?>', 'status-text-<?php echo $gig['id']; ?>')">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <button class="btn-icon edit" title="Edit Gig" onclick="openEditModal(<?php echo $gig['id']; ?>)"><i class="ph-bold ph-pencil-simple"></i></button>
                            <button class="btn-icon delete" title="Delete Gig" onclick="deleteGig('gig-<?php echo $gig['id']; ?>')"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Gigs Found State -->
                <div style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="ph-fill ph-folder-open" style="font-size: 4rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Gigs Found</h3>
                    <p>You haven't created any services yet. Click "Create New Gig" to get started!</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<!-- ================= MODALS ================= -->

<!-- 1. Create New Gig Modal -->
<div class="modal-overlay" id="newGigModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('newGigModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-plus-circle" style="color: #10b981;"></i> Create New Gig</h2>
        
        <form action="../api/gig-process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_gig">
            
            <div class="form-group">
                <label>Gig Banner Image (Recommended)</label>
                <label for="newGigImg" class="img-upload-box">
                    <i class="ph-fill ph-image" id="newImgIcon"></i>
                    <span id="newImgText">Click to browse or drag an image here</span>
                    <img id="newImgPreview" src="" alt="Preview">
                </label>
                <input type="file" id="newGigImg" name="gig_image" accept="image/png, image/jpeg" style="display: none;" onchange="previewImage(event, 'newImgPreview', 'newImgIcon', 'newImgText')">
            </div>

            <div class="form-group">
                <label>Gig Title</label>
                <input type="text" class="form-control" name="title" placeholder="I will do professional..." required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" name="category" required>
                        <option value="" disabled selected>Select...</option>
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="carpentry">Carpentry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (Rs.)</label>
                    <input type="number" class="form-control" name="base_price" placeholder="e.g. 1500" required>
                </div>
            </div>

            <div class="form-group">
                <label>Detailed Description</label>
                <textarea class="form-control" name="description" rows="4" placeholder="Describe the service you are providing, your experience, and what is included..." required></textarea>
            </div>

            <button type="submit" class="btn-create" style="width: 100%; justify-content: center;"><i class="ph-bold ph-rocket-launch"></i> Publish Gig</button>
        </form>
    </div>
</div>

<!-- 2. Edit Gig Modal -->
<div class="modal-overlay" id="editGigModal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('editGigModal')"><i class="ph-bold ph-x"></i></button>
        <h2 class="modal-title"><i class="ph-fill ph-pencil-simple" style="color: #3b82f6;"></i> Edit Gig</h2>
        
        <form action="../api/gig-process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_gig">
            <input type="hidden" name="gig_id" id="editGigId">
            
            <div class="form-group">
                <label>Update Banner Image</label>
                <label for="editGigImg" class="img-upload-box" style="height: 120px; padding: 10px;">
                    <i class="ph-fill ph-image" id="editImgIcon"></i>
                    <span id="editImgText">Click to change image</span>
                    <img id="editImgPreview" src="" alt="Preview">
                </label>
                <input type="file" id="editGigImg" name="gig_image" accept="image/png, image/jpeg" style="display: none;" onchange="previewImage(event, 'editImgPreview', 'editImgIcon', 'editImgText')">
            </div>

            <div class="form-group">
                <label>Gig Title</label>
                <input type="text" class="form-control" name="title" id="editTitle" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" name="category" id="editCategory" required>
                        <option value="electrical">Electrical</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="carpentry">Carpentry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Base Price (Rs.)</label>
                    <input type="number" class="form-control" name="base_price" id="editPrice" required>
                </div>
            </div>

            <div class="form-group">
                <label>Detailed Description</label>
                <textarea class="form-control" name="description" id="editDesc" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn-create" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 10px 25px rgba(59,130,246,0.3);"><i class="ph-bold ph-floppy-disk"></i> Save Changes</button>
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

    function toggleGigStatus(checkbox, cardId, textId) {
        const card = document.getElementById(cardId);
        const textLabel = document.getElementById(textId);
        
        if (checkbox.checked) {
            card.classList.add('active-gig');
            textLabel.innerText = 'Active';
        } else {
            card.classList.remove('active-gig');
            textLabel.innerText = 'Paused';
        }
    }

    function deleteGig(gigId) {
        if (confirm("Are you sure you want to permanently delete this gig?")) {
            const gigCard = document.getElementById(gigId);
            gigCard.style.opacity = '0';
            gigCard.style.transform = 'scale(0.95)';
            setTimeout(() => {
                gigCard.remove();
                // මෙහිදී Delete API එකට Request එකක් යැවිය හැක.
            }, 300);
        }
    }

    function openEditModal(id) {
        const title = document.getElementById('title-' + id).innerText;
        const price = document.getElementById('price-' + id).innerText;
        const desc = document.getElementById('desc-' + id).innerText;
        const catText = document.getElementById('cat-' + id).innerText.toLowerCase().trim();
        
        document.getElementById('editGigId').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editPrice').value = price;
        document.getElementById('editDesc').value = desc;

        const catSelect = document.getElementById('editCategory');
        for (let i = 0; i < catSelect.options.length; i++) {
            if (catText.includes(catSelect.options[i].value)) {
                catSelect.selectedIndex = i;
                break;
            }
        }

        openModal('editGigModal');
    }

    function previewImage(event, previewId, iconId, textId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        const icon = document.getElementById(iconId);
        const text = document.getElementById(textId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if(icon) icon.style.display = 'none';
                if(text) text.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>