<?php
// worker/find-jobs.php
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
        <div class="filter-bar fade-up show" style="transition-delay: 50ms;">
            <div class="search-input">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" placeholder="Search by keyword or location...">
            </div>
            <button class="filter-btn"><i class="ph-bold ph-sliders-horizontal"></i> Filter</button>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Job Request 1 -->
            <div class="job-card">
                <div class="job-info">
                    <span class="badge-urgent"><i class="ph-fill ph-warning-circle"></i> Urgent Request</span>
                    <h3 style="font-size: 1.4rem; font-weight: 800; color: #1a1a1a;">Broken Water Pipe Fix</h3>
                    
                    <p class="job-desc">The main water pipe in the kitchen is broken and leaking heavily. Need an experienced plumber to fix this immediately. Parts will be provided if required.</p>
                    
                    <div class="job-meta">
                        <span><i class="ph-fill ph-map-pin"></i> Colombo 05 (3km away)</span>
                        <span><i class="ph-fill ph-calendar"></i> Today, ASAP</span>
                        <span><i class="ph-fill ph-user"></i> Kasun P.</span>
                    </div>
                </div>
                
                <div class="action-group">
                    <div class="price-tag">Bid Now</div>
                    <button class="btn-bid" onclick="placeBid(this)"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                </div>
            </div>

            <!-- Job Request 2 -->
            <div class="job-card">
                <div class="job-info">
                    <span class="badge-standard"><i class="ph-fill ph-lightning"></i> Standard Request</span>
                    <h3 style="font-size: 1.4rem; font-weight: 800; color: #1a1a1a;">Install 2 Ceiling Fans</h3>
                    
                    <p class="job-desc">Looking for an electrician to install two new ceiling fans in the living room. The wiring is already done, just need to fix the fans and connect them to the switches.</p>
                    
                    <div class="job-meta">
                        <span><i class="ph-fill ph-map-pin"></i> Nugegoda (6km away)</span>
                        <span><i class="ph-fill ph-calendar"></i> Tomorrow, 10:00 AM</span>
                        <span><i class="ph-fill ph-user"></i> Amali W.</span>
                    </div>
                </div>
                
                <div class="action-group">
                    <div class="price-tag">Bid Now</div>
                    <button class="btn-bid" onclick="placeBid(this)"><i class="ph-bold ph-handshake"></i> Send Bid</button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function placeBid(btnElement) {
        const bidAmount = prompt("Enter your bid amount for this job (Rs.):");
        
        if (bidAmount && !isNaN(bidAmount) && bidAmount > 0) {
            btnElement.innerHTML = '<i class="ph-bold ph-check"></i> Bid Placed (Rs. ' + bidAmount + ')';
            btnElement.style.backgroundColor = '#10b981';
            btnElement.style.boxShadow = '0 8px 20px rgba(16,185,129,0.2)';
            btnElement.disabled = true;
            alert("Your bid of Rs. " + bidAmount + " has been successfully sent to the customer!");
        } else if (bidAmount !== null) {
            alert("Please enter a valid amount.");
        }
    }
</script>

</body>
</html>