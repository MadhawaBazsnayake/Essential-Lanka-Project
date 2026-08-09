<?php
// jobs-feed.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php'; // Database connection

// Search & Filter Logic
$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT j.*, c.name_en AS category_name, c.icon_class, u.first_name, u.last_name 
        FROM jobs j
        JOIN categories c ON j.category_id = c.id
        JOIN users u ON j.client_id = u.id
        WHERE j.status = 'open'";

$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if (!empty($categoryFilter)) {
    $sql .= " AND j.category_id = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$openJobs = $stmt->fetchAll();

// Get categories for the filter dropdown
$catStmt = $pdo->query("SELECT id, name_en FROM categories WHERE status = 'active'");
$categories = $catStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .feed-section { padding: 40px 5%; min-height: 85vh; background: #f8fafc; position: relative; }
        .feed-container { max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
        
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header h1 { font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; }
        .page-header p { color: #64748b; font-size: 1.1rem; }

        /* Search & Filter Bar */
        .search-bar-container { background: #ffffff; padding: 20px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; display: flex; gap: 15px; flex-wrap: wrap; border: 1.5px solid #e2e8f0; }
        .search-input-wrap { flex: 1; min-width: 250px; position: relative; }
        .search-input-wrap i { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.2rem; }
        .search-input { width: 100%; padding: 15px 20px 15px 50px; border: 2px solid #f1f5f9; background: #f8fafc; border-radius: 12px; font-family: inherit; font-size: 1rem; outline: none; transition: 0.3s; }
        .search-input:focus { border-color: #3b82f6; background: #ffffff; }
        
        .filter-select { padding: 15px 20px; border: 2px solid #f1f5f9; background: #f8fafc; border-radius: 12px; font-family: inherit; font-size: 1rem; outline: none; min-width: 200px; cursor: pointer; }
        .btn-search { background: #3b82f6; color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 1rem; }
        .btn-search:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(59,130,246,0.3); }

        /* Jobs Grid */
        .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        
        .job-card { background: #ffffff; border-radius: 20px; padding: 25px; border: 1.5px solid #e2e8f0; transition: 0.3s; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .job-card:hover { border-color: #3b82f6; box-shadow: 0 20px 40px rgba(0,0,0,0.06); transform: translateY(-5px); }
        
        .job-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .cat-badge { background: rgba(59,130,246,0.1); color: #3b82f6; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .job-price { font-size: 1.2rem; font-weight: 800; color: #10b981; }
        
        .job-title { font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; line-height: 1.4; }
        .job-desc { color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px; flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        
        .job-meta { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .job-meta span { display: inline-flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #64748b; font-weight: 500; }
        
        .job-footer { border-top: 1px solid #f1f5f9; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .client-info { display: flex; align-items: center; gap: 10px; }
        .client-avatar { width: 35px; height: 35px; border-radius: 50%; background: #e2e8f0; display: flex; justify-content: center; align-items: center; font-size: 0.9rem; font-weight: 700; color: #64748b; }
        .client-name { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
        
        .btn-view { background: #1e293b; color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .btn-view:hover { background: #3b82f6; }

        @media (max-width: 768px) {
            .search-bar-container { flex-direction: column; }
            .btn-search { width: 100%; }
        }
    </style>
</head>
<body>

<!-- You can include your navigation bar here -->

<section class="feed-section">
    <div class="feed-container">
        
        <div class="page-header">
            <h1>Find Your Next Job</h1>
            <p>Browse through hundreds of tasks posted by clients in your area.</p>
        </div>

        <!-- Search & Filters -->
        <form action="jobs-feed.php" method="GET" class="search-bar-container">
            <div class="search-input-wrap">
                <i class="ph-bold ph-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Search for jobs (e.g., plumbing, painting)..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            
            <select name="category" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryFilter == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name_en']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-search">Search Jobs</button>
        </form>

        <!-- Jobs Grid -->
        <div class="jobs-grid">
            <?php if (count($openJobs) > 0): ?>
                <?php foreach ($openJobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <span class="cat-badge"><i class="<?php echo htmlspecialchars($job['icon_class']); ?>"></i> <?php echo htmlspecialchars($job['category_name']); ?></span>
                            <span class="job-price">Rs. <?php echo number_format($job['budget'], 2); ?></span>
                        </div>
                        
                        <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p class="job-desc"><?php echo htmlspecialchars($job['description']); ?></p>
                        
                        <div class="job-meta">
                            <span><i class="ph-fill ph-map-pin"></i> Location Available</span>
                            <span><i class="ph-fill ph-clock"></i> <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                        </div>
                        
                        <div class="job-footer">
                            <div class="client-info">
                                <div class="client-avatar">
                                    <?php echo substr($job['first_name'], 0, 1) . substr($job['last_name'], 0, 1); ?>
                                </div>
                                <span class="client-name"><?php echo htmlspecialchars($job['first_name']); ?></span>
                            </div>
                            
                            <?php 
                                // Direct to single job view or modal (can adjust link based on user role)
                                $viewLink = isset($_SESSION['role']) && $_SESSION['role'] === 'worker' 
                                            ? "worker/dashboard.php?job_id=" . $job['id'] 
                                            : "login.php"; 
                            ?>
                            <a href="<?php echo $viewLink; ?>" class="btn-view">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 20px; border: 1.5px dashed #cbd5e1;">
                    <i class="ph-fill ph-magnifying-glass" style="font-size: 3rem; color: #94a3b8; margin-bottom: 15px;"></i>
                    <h3 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 5px;">No Jobs Found</h3>
                    <p style="color: #64748b;">Try adjusting your search keywords or category filters.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

</body>
</html>