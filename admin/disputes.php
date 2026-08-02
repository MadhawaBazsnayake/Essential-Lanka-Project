<?php
// admin/disputes.php
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
    <title>Manage Disputes - Essential Lanka</title>
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
    
    /* Dispute Card Styling */
    .dispute-card { background: #ffffff; border: 1.5px solid #f0ebe1; border-radius: 20px; padding: 25px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 15px; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .dispute-card:hover { border-color: #ef4444; box-shadow: 0 12px 35px rgba(239,68,68,0.1); transform: translateY(-3px); }

    .dispute-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px dashed #e5dfd5; padding-bottom: 15px; }
    
    .badge-urgent { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }
    .badge-review { background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 14px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; }

    .dispute-parties { display: grid; grid-template-columns: 1fr 40px 1fr; align-items: center; gap: 10px; background: #f8fafc; padding: 15px; border-radius: 15px; }
    .party { display: flex; flex-direction: column; }
    .party-role { font-size: 0.8rem; color: #a3a3a3; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .party-name { font-size: 1.05rem; font-weight: 800; color: #1a1a1a; }
    .party-vs { font-size: 1.2rem; font-weight: 800; color: #cbd5e1; text-align: center; }

    .dispute-reason { color: #4a4a4a; font-size: 0.95rem; line-height: 1.6; }
    .dispute-reason strong { color: #1a1a1a; }

    .action-group { display: flex; justify-content: flex-end; gap: 12px; margin-top: 5px; }
    
    .btn-outline { border: 2px solid #e5dfd5; background: transparent; color: #1a1a1a; padding: 10px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
    .btn-outline:hover { border-color: #3b82f6; color: #3b82f6; background: rgba(59,130,246,0.05); }
    
    .btn-resolve { background: #10b981; color: white; padding: 10px 25px; border-radius: 50px; font-weight: 700; text-decoration: none; transition: 0.3s; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(16,185,129,0.2); display: inline-flex; align-items: center; gap: 8px; }
    .btn-resolve:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(16,185,129,0.3); background: #059669; }

    @media (max-width: 768px) {
        .dispute-parties { grid-template-columns: 1fr; text-align: center; }
        .party-vs { transform: rotate(90deg); margin: 10px 0; }
        .action-group { flex-direction: column; }
        .btn-outline, .btn-resolve { width: 100%; justify-content: center; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="admin-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="admin-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Manage Disputes</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <p style="color: #666; margin-bottom: 25px; font-size: 1.05rem;">Review and resolve conflicts reported by customers or workers.</p>

            <!-- Dispute Item 1 -->
            <div class="dispute-card" id="dispute-1">
                <div class="dispute-header">
                    <div>
                        <span class="badge-urgent"><i class="ph-fill ph-warning-circle"></i> Unresolved (High Priority)</span>
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-top: 5px;">Job #4052 - Plumbing Leak Fix</h3>
                    </div>
                    <span style="font-size: 0.9rem; color: #a3a3a3; font-weight: 600;">Reported 2 hrs ago</span>
                </div>
                
                <div class="dispute-parties">
                    <div class="party">
                        <span class="party-role"><i class="ph-fill ph-user"></i> Customer</span>
                        <span class="party-name">Kasun Perera</span>
                    </div>
                    <div class="party-vs">VS</div>
                    <div class="party" style="text-align: right;">
                        <span class="party-role">Worker <i class="ph-fill ph-wrench"></i></span>
                        <span class="party-name">Saman Kumara</span>
                    </div>
                </div>

                <div class="dispute-reason">
                    <strong>Reason for Dispute:</strong> The customer claims the worker did not complete the job properly and left the premises without fixing the main water leak. The worker states the required parts were not provided by the customer as agreed.
                </div>

                <div class="action-group">
                    <button class="btn-outline"><i class="ph-bold ph-chat-teardrop-text"></i> Contact Both Parties</button>
                    <button class="btn-resolve" onclick="resolveDispute('dispute-1')"><i class="ph-bold ph-check-circle"></i> Mark as Resolved</button>
                </div>
            </div>

            <!-- Dispute Item 2 -->
            <div class="dispute-card" id="dispute-2">
                <div class="dispute-header">
                    <div>
                        <span class="badge-review"><i class="ph-fill ph-magnifying-glass"></i> Under Review</span>
                        <h3 style="font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin-top: 5px;">Job #3920 - AC Servicing</h3>
                    </div>
                    <span style="font-size: 0.9rem; color: #a3a3a3; font-weight: 600;">Reported Yesterday</span>
                </div>
                
                <div class="dispute-parties">
                    <div class="party">
                        <span class="party-role"><i class="ph-fill ph-user"></i> Customer</span>
                        <span class="party-name">Amali Silva</span>
                    </div>
                    <div class="party-vs">VS</div>
                    <div class="party" style="text-align: right;">
                        <span class="party-role">Worker <i class="ph-fill ph-wrench"></i></span>
                        <span class="party-name">Nimal Fernando</span>
                    </div>
                </div>

                <div class="dispute-reason">
                    <strong>Reason for Dispute:</strong> Payment issue. The worker states that the customer refused to pay the additional Rs. 1500 for the extra gas refilling service which was agreed upon verbally.
                </div>

                <div class="action-group">
                    <button class="btn-outline"><i class="ph-bold ph-chat-teardrop-text"></i> Contact Both Parties</button>
                    <button class="btn-resolve" onclick="resolveDispute('dispute-2')"><i class="ph-bold ph-check-circle"></i> Mark as Resolved</button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function resolveDispute(disputeId) {
        if(confirm("Are you sure you want to mark this dispute as resolved?")) {
            const card = document.getElementById(disputeId);
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                card.remove();
                alert("Dispute resolved successfully and both parties will be notified.");
            }, 300);
        }
    }
</script>

</body>
</html>