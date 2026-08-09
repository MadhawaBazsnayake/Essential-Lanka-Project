<?php
// admin/verify-users.php
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
    <title>Verify Users - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .admin-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .admin-container { max-width: 1000px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 40px; border-radius: 35px; }
    
    /* Verification Card Styling */
    .verify-card { background: #ffffff; border: 1.5px solid #f0ebe1; padding: 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); flex-wrap: wrap; gap: 20px; }
    .verify-card:hover { border-color: #3b82f6; box-shadow: 0 12px 35px rgba(59,130,246,0.1); transform: translateY(-3px); }
    
    .user-info { display: flex; align-items: center; gap: 20px; flex: 1; }
    .user-avatar { width: 60px; height: 60px; border-radius: 18px; background-color: #f8fafc; color: #64748b; display: flex; justify-content: center; align-items: center; font-size: 2rem; border: 2px solid #e2e8f0; }
    
    .badge-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }

    .action-group { display: flex; gap: 10px; }
    
    .btn-view { border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 10px 20px; border-radius: 50px; font-weight: 700; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-view:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59,130,246,0.05); }
    
    .btn-approve { background: #10b981; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-approve:hover { background: #059669; box-shadow: 0 8px 20px rgba(16,185,129,0.3); transform: translateY(-2px); }

    .btn-reject { background: #ef4444; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-reject:hover { background: #dc2626; box-shadow: 0 8px 20px rgba(239,68,68,0.3); transform: translateY(-2px); }

    /* Modal Styles */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 100; display: none; justify-content: center; align-items: center; opacity: 0; transition: 0.3s; }
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-content { background: #ffffff; width: 90%; max-width: 700px; border-radius: 25px; padding: 35px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: scale(0.9); transition: 0.3s; position: relative; }
    .modal-overlay.active .modal-content { transform: scale(1); }
    
    .close-modal { position: absolute; top: 20px; right: 20px; font-size: 1.5rem; color: #a3a3a3; cursor: pointer; transition: 0.3s; }
    .close-modal:hover { color: #ef4444; }

    .id-images-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    .id-img-box { border: 2px dashed #cbd5e1; border-radius: 15px; padding: 10px; text-align: center; }
    .id-img-box img { width: 100%; border-radius: 10px; margin-bottom: 10px; }
    .id-img-box p { font-weight: 700; color: #64748b; font-size: 0.9rem; }

    @media (max-width: 768px) {
        .verify-card { flex-direction: column; align-items: flex-start; }
        .action-group { width: 100%; flex-wrap: wrap; }
        .action-group button { flex: 1; justify-content: center; }
        .id-images-grid { grid-template-columns: 1fr; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="admin-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="admin-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Identity Verifications</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <p style="color: #666; margin-bottom: 25px; font-size: 1.05rem;">Review uploaded National Identity Cards (NIC) to verify worker accounts.</p>

            <!-- Verification Request 1 -->
            <div class="verify-card" id="req-1">
                <div class="user-info">
                    <div class="user-avatar"><i class="ph-fill ph-user"></i></div>
                    <div>
                        <span class="badge-pending"><i class="ph-fill ph-hourglass-high"></i> Pending Review</span>
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Kamal Perera</h3>
                        <p style="font-size: 0.95rem; color: #666;"><i class="ph-fill ph-identification-card"></i> NIC: 199012345678 • Electrician</p>
                    </div>
                </div>
                
                <div class="action-group">
                    <button class="btn-view" onclick="openModal('Kamal Perera')"><i class="ph-bold ph-eye"></i> View ID</button>
                    <button class="btn-approve" onclick="processRequest('req-1', 'Approve')"><i class="ph-bold ph-check"></i> Approve</button>
                    <button class="btn-reject" onclick="processRequest('req-1', 'Reject')"><i class="ph-bold ph-x"></i> Reject</button>
                </div>
            </div>

            <!-- Verification Request 2 -->
            <div class="verify-card" id="req-2">
                <div class="user-info">
                    <div class="user-avatar"><i class="ph-fill ph-user"></i></div>
                    <div>
                        <span class="badge-pending"><i class="ph-fill ph-hourglass-high"></i> Pending Review</span>
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin-bottom: 4px;">Suresh Silva</h3>
                        <p style="font-size: 0.95rem; color: #666;"><i class="ph-fill ph-identification-card"></i> NIC: 951234567V • Plumber</p>
                    </div>
                </div>
                
                <div class="action-group">
                    <button class="btn-view" onclick="openModal('Suresh Silva')"><i class="ph-bold ph-eye"></i> View ID</button>
                    <button class="btn-approve" onclick="processRequest('req-2', 'Approve')"><i class="ph-bold ph-check"></i> Approve</button>
                    <button class="btn-reject" onclick="processRequest('req-2', 'Reject')"><i class="ph-bold ph-x"></i> Reject</button>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ID View Modal -->
<div class="modal-overlay" id="idModal">
    <div class="modal-content">
        <i class="ph-bold ph-x close-modal" onclick="closeModal()"></i>
        
        <h3 style="font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px;">Review ID Documents</h3>
        <p style="color: #666; font-size: 0.95rem; margin-bottom: 20px;">User: <strong id="modalUserName">Name</strong></p>

        <div class="id-images-grid">
            <div class="id-img-box">
                <!-- Placeholder for Demo -->
                <img src="https://via.placeholder.com/400x250/e2e8f0/64748b?text=NIC+Front+Side" alt="NIC Front">
                <p>Front Side</p>
            </div>
            <div class="id-img-box">
                <!-- Placeholder for Demo -->
                <img src="https://via.placeholder.com/400x250/e2e8f0/64748b?text=NIC+Back+Side" alt="NIC Back">
                <p>Back Side</p>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 25px;">
            <button class="btn-view" onclick="closeModal()">Close Window</button>
        </div>
    </div>
</div>

<script>
    function openModal(userName) {
        document.getElementById('modalUserName').innerText = userName;
        document.getElementById('idModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('idModal').classList.remove('active');
    }

    function processRequest(workerId, action) {
        const status = action === 'Approve' ? 'verified' : 'rejected';
        const confirmMsg = action === 'Approve' 
            ? "Are you sure you want to APPROVE this user? They will receive the verified badge." 
            : "Are you sure you want to REJECT this verification request?";
            
        if(confirm(confirmMsg)) {
            const formData = new FormData();
            formData.append('action', 'verify_worker');
            formData.append('worker_id', workerId.replace('req-', ''));
            formData.append('status', status);

            fetch('../api/admin-process.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const card = document.getElementById(workerId);
                    if(card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => card.remove(), 300);
                    }
                    alert(data.message);
                } else {
                    alert(data.message || 'Error processing request');
                }
            })
            .catch(err => alert('Network error.'));
        }
    }
</script>

</body>
</html>