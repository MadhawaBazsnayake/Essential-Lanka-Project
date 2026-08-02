<?php
// admin/reports.php
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
    <title>System Reports - Essential Lanka</title>
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

    /* Overview Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 35px rgba(0,0,0,0.04); padding: 30px; border-radius: 30px; display: flex; align-items: center; gap: 20px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    
    .stat-icon { width: 60px; height: 60px; border-radius: 18px; display: flex; justify-content: center; align-items: center; font-size: 2rem; }
    
    .stat-info p { font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-info h3 { font-size: 1.8rem; font-weight: 800; color: #1a1a1a; }

    /* Main Grid */
    .admin-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 35px; border-radius: 35px; }
    .glass-box h3 { font-size: 1.4rem; font-weight: 800; color: #1a1a1a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

    /* Form Styles */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 15px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
    
    select.form-control { appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%231a1a1a" viewBox="0 0 256 256"><path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path></svg>'); background-repeat: no-repeat; background-position: right 20px center; }

    .btn-submit { background: #3b82f6; color: white; padding: 15px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(59,130,246,0.2); display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(59,130,246,0.3); background: #2563eb; }
    
    .btn-export { background: #1a1a1a; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; border: none; cursor: pointer; }
    .btn-export:hover { background: #000000; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }

    /* Report Data Table */
    .table-responsive { overflow-x: auto; margin-top: 20px; }
    .report-table { width: 100%; border-collapse: collapse; min-width: 500px; }
    .report-table th { text-align: left; padding: 15px; background: #f8fafc; color: #64748b; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .report-table td { padding: 18px 15px; border-bottom: 1px solid #f0ebe1; font-size: 1rem; color: #1a1a1a; font-weight: 500; }
    .report-table tr:hover td { background: #f8fafc; }
    
    .status-success { color: #10b981; font-weight: 700; background: rgba(16,185,129,0.1); padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; }

    @media (max-width: 992px) {
        .admin-grid { grid-template-columns: 1fr; }
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<section class="admin-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="admin-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">System Reports</h1>
            <a href="dashboard.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Financial Overview Stats -->
        <div class="stats-grid fade-up show" style="transition-delay: 50ms;">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-fill ph-money"></i></div>
                <div class="stat-info">
                    <p>Total Revenue</p>
                    <h3>Rs. 1,250,000</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph-fill ph-chart-line-up"></i></div>
                <div class="stat-info">
                    <p>Platform Fees (10%)</p>
                    <h3>Rs. 125,000</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i class="ph-fill ph-handshake"></i></div>
                <div class="stat-info">
                    <p>Total Completed Jobs</p>
                    <h3>3,420</h3>
                </div>
            </div>
        </div>

        <div class="admin-grid">
            
            <!-- Generate Report Form -->
            <div class="glass-box fade-up show" style="transition-delay: 100ms;">
                <h3><i class="ph-fill ph-funnel" style="color: #3b82f6;"></i> Generate Report</h3>
                
                <form action="#" method="POST" onsubmit="generateReport(event)">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select class="form-control" required>
                            <option value="financial">Financial Transactions</option>
                            <option value="jobs">Completed Jobs</option>
                            <option value="users">User Registration</option>
                            <option value="disputes">Disputes History</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="ph-bold ph-magic-wand"></i> Generate Data
                    </button>
                </form>
            </div>

            <!-- Report Results View -->
            <div class="glass-box fade-up show" style="transition-delay: 200ms;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="margin: 0;"><i class="ph-fill ph-table" style="color: #1a1a1a;"></i> Recent Transactions</h3>
                    
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-export" onclick="exportData('csv')"><i class="ph-bold ph-file-csv"></i> CSV</button>
                        <button class="btn-export" onclick="exportData('pdf')"><i class="ph-bold ph-file-pdf"></i> PDF</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Job ID</th>
                                <th>Category</th>
                                <th>Amount (Rs.)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aug 02, 2026</td>
                                <td>#4055</td>
                                <td>Electrical</td>
                                <td>2,500.00</td>
                                <td><span class="status-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>Aug 01, 2026</td>
                                <td>#4054</td>
                                <td>Plumbing</td>
                                <td>1,800.00</td>
                                <td><span class="status-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>Jul 31, 2026</td>
                                <td>#4051</td>
                                <td>Cleaning</td>
                                <td>4,000.00</td>
                                <td><span class="status-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>Jul 30, 2026</td>
                                <td>#4048</td>
                                <td>Carpentry</td>
                                <td>8,500.00</td>
                                <td><span class="status-success">Completed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function generateReport(event) {
        event.preventDefault();
        const btn = event.target.querySelector('.btn-submit');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation: spin 1s linear infinite;"></i> Loading...';
        btn.style.opacity = '0.8';
        btn.disabled = true;

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.opacity = '1';
            btn.disabled = false;
            alert("Report generated successfully!");
        }, 1500);
    }

    function exportData(type) {
        if(type === 'csv') {
            alert("Downloading CSV file...");
        } else {
            alert("Generating PDF document...");
        }
    }
</script>

<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

</body>
</html>