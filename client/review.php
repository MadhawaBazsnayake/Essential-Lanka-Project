<?php
// client/review.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

$jobId = $_GET['job_id'] ?? null;
$job = null;

if ($jobId) {
    $stmt = $pdo->prepare("
        SELECT j.*, w.first_name as worker_first, w.last_name as worker_last, w.profile_picture as worker_pic, w.id as worker_id
        FROM jobs j
        JOIN users w ON j.assigned_worker_id = w.id
        WHERE j.id = ? AND j.client_id = ?
    ");
    $stmt->execute([$jobId, $_SESSION['user_id']]);
    $job = $stmt->fetch();
}

if (!$job) {
    header("Location: my-requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave a Review - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .page-section { padding: 50px 5%; min-height: 85vh; position: relative; }
    .page-container { max-width: 700px; margin: 0 auto; position: relative; z-index: 10; }
    
    .header-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 1.05rem; }
    .back-btn:hover { color: #10b981; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: #1a1a1a; }

    .glass-box { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 15px 40px rgba(0,0,0,0.06); padding: 50px; border-radius: 35px; text-align: center; }
    
    /* Worker Info Snippet */
    .worker-snippet { display: flex; flex-direction: column; align-items: center; margin-bottom: 35px; padding-bottom: 30px; border-bottom: 2px solid #f0ebe1; }
    .worker-avatar { width: 80px; height: 80px; border-radius: 50%; background-image: url('https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop'); background-size: cover; background-position: center; border: 4px solid #ffffff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); margin-bottom: 15px; }
    .worker-snippet h3 { font-size: 1.4rem; font-weight: 800; color: #1a1a1a; margin-bottom: 5px; }
    .worker-snippet p { font-size: 1rem; color: #666; }

    /* Interactive Star Rating */
    .stars-container { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; direction: rtl; } /* RTL for easy CSS hover logic */
    .star-btn { font-size: 3rem; color: #e5dfd5; cursor: pointer; transition: 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    
    /* Hover & Active Effects (Using RTL trick) */
    .stars-container > .star-btn:hover,
    .stars-container > .star-btn:hover ~ .star-btn,
    .stars-container.selected > .star-btn.active,
    .stars-container.selected > .star-btn.active ~ .star-btn {
        color: #f59e0b;
    }
    
    .star-btn:hover { transform: scale(1.15); }

    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 10px; font-size: 1rem; color: #1a1a1a; text-align: center; }
    .form-control { width: 100%; padding: 20px; border-radius: 20px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; resize: vertical; min-height: 120px; }
    .form-control:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245,158,11,0.1); }

    .btn-submit { background: #1a1a1a; color: white; padding: 18px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.15rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; justify-content: center; align-items: center; gap: 10px; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.25); background: #000000; }

    @media (max-width: 768px) {
        .glass-box { padding: 35px 20px; }
        .star-btn { font-size: 2.5rem; }
        .header-action { flex-direction: column; align-items: flex-start; gap: 15px; }
    }
</style>

<section class="page-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="page-container">
        
        <div class="header-action fade-up show">
            <h1 class="page-title">Rate the Service</h1>
            <a href="my-requests.php" class="back-btn"><i class="ph-bold ph-arrow-left"></i> Back to History</a>
        </div>

        <div class="glass-box fade-up show" style="transition-delay: 100ms;">
            
            <!-- Worker Info -->
            <div class="worker-snippet">
                <?php if(!empty($job['worker_pic'])): ?>
                    <img src="../<?php echo htmlspecialchars($job['worker_pic']); ?>" class="worker-avatar" style="background-image:none; object-fit:cover;" alt="Avatar">
                <?php else: ?>
                    <div class="worker-avatar" style="background-image: none; background-color: #cbd5e1; display:flex; justify-content:center; align-items:center;"><i class="ph-fill ph-user" style="font-size:2rem; color:#64748b;"></i></div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($job['worker_first'] . ' ' . $job['worker_last']); ?></h3>
                <p><?php echo htmlspecialchars($job['title']); ?></p>
            </div>

            <form action="../api/review-process.php" method="POST" id="reviewForm">
                <input type="hidden" name="action" value="submit_review">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <input type="hidden" name="worker_id" value="<?php echo $job['worker_id']; ?>">
                
                <!-- Hidden input to store the actual rating value (1-5) -->
                <input type="hidden" name="rating" id="ratingInput" value="0" required>

                <div class="form-group">
                    <label>How was your experience?</label>
                    
                    <!-- Stars (RTL ordered: 5, 4, 3, 2, 1) -->
                    <div class="stars-container" id="starsContainer">
                        <i class="ph-fill ph-star star-btn" data-value="5" onclick="setRating(5)"></i>
                        <i class="ph-fill ph-star star-btn" data-value="4" onclick="setRating(4)"></i>
                        <i class="ph-fill ph-star star-btn" data-value="3" onclick="setRating(3)"></i>
                        <i class="ph-fill ph-star star-btn" data-value="2" onclick="setRating(2)"></i>
                        <i class="ph-fill ph-star star-btn" data-value="1" onclick="setRating(1)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <textarea name="review_text" class="form-control" placeholder="Write a few words about the quality of work, behavior, and overall satisfaction..." required></textarea>
                </div>

                <button type="submit" class="btn-submit" onclick="validateRating(event)">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Submit Review
                </button>
            </form>

        </div>
    </div>
</section>

<script>
    function setRating(val) {
        // Set the hidden input value
        document.getElementById('ratingInput').value = val;
        
        const container = document.getElementById('starsContainer');
        const stars = container.querySelectorAll('.star-btn');
        
        // Add selected class to container to activate CSS persistence rules
        container.classList.add('selected');
        
        // Remove active class from all
        stars.forEach(star => star.classList.remove('active'));
        
        // Add active class to the clicked star (CSS will handle highlighting this and subsequent RTL siblings)
        const clickedStar = container.querySelector(`[data-value="${val}"]`);
        if(clickedStar) {
            clickedStar.classList.add('active');
        }
    }

    function validateRating(event) {
        const ratingVal = document.getElementById('ratingInput').value;
        if(ratingVal == 0) {
            event.preventDefault();
            alert("Please select a star rating before submitting!");
        }
    }
</script>

</body>
</html>