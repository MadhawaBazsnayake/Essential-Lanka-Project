<?php
// api/reviews.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ============================================
// 1. ADD A REVIEW (Clients Only)
// ============================================
if ($action === 'add_review') {
    if ($role !== 'client') {
        echo json_encode(['status' => 'error', 'message' => 'Only clients can submit reviews.']);
        exit;
    }

    $jobId = $_POST['job_id'] ?? '';
    $workerId = $_POST['worker_id'] ?? '';
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    // Validate inputs
    if (empty($jobId) || empty($workerId) || $rating < 1 || $rating > 5 || empty($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required and rating must be between 1 and 5.']);
        exit;
    }

    try {
        // Begin transaction to ensure both tables are updated safely
        $pdo->beginTransaction();

        // 1. Insert the new review into the reviews table
        $stmt = $pdo->prepare("INSERT INTO reviews (job_id, reviewer_id, worker_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$jobId, $userId, $workerId, $rating, $comment]);

        // 2. Calculate the new average rating and total reviews for the worker
        $avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(id) as total_reviews FROM reviews WHERE worker_id = ?");
        $avgStmt->execute([$workerId]);
        $stats = $avgStmt->fetch();

        $newAvg = round($stats['avg_rating'], 1);
        $totalReviews = $stats['total_reviews'];

        // 3. Update the worker_profiles table with the new stats
        $updateStmt = $pdo->prepare("UPDATE worker_profiles SET rating = ?, total_reviews = ? WHERE worker_id = ?");
        $updateStmt->execute([$newAvg, $totalReviews, $workerId]);

        // Commit the transaction
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Review submitted successfully!',
            'new_rating' => $newAvg,
            'total_reviews' => $totalReviews
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// 2. GET WORKER REVIEWS
// ============================================
if ($action === 'get_reviews') {
    $workerId = $_POST['worker_id'] ?? ($_GET['worker_id'] ?? '');

    if (empty($workerId)) {
        echo json_encode(['status' => 'error', 'message' => 'Worker ID is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT r.rating, r.comment, r.created_at, u.first_name, u.last_name 
            FROM reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.worker_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$workerId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $reviews]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Invalid action fallback
echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
exit;
?>