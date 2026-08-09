<?php
// api/review-process.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'submit_review') {
        $jobId = $_POST['job_id'] ?? null;
        $workerId = $_POST['worker_id'] ?? null;
        $rating = $_POST['rating'] ?? null;
        $comment = $_POST['review_text'] ?? '';
        $reviewerId = $_SESSION['user_id'];

        if ($jobId && $workerId && $rating) {
            // Check if review already exists
            $chk = $pdo->prepare("SELECT id FROM reviews WHERE job_id = ? AND reviewer_id = ?");
            $chk->execute([$jobId, $reviewerId]);
            if ($chk->fetch()) {
                header("Location: ../client/my-requests.php?msg=already_reviewed");
                exit();
            }

            // Insert review
            $stmt = $pdo->prepare("INSERT INTO reviews (job_id, reviewer_id, worker_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$jobId, $reviewerId, $workerId, $rating, $comment])) {
                // Update worker rating & total reviews
                $avgStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_count FROM reviews WHERE worker_id = ?");
                $avgStmt->execute([$workerId]);
                $stats = $avgStmt->fetch();
                $avgRating = $stats['avg_rating'] ?? 0;
                $totalReviews = $stats['total_count'] ?? 0;

                $upStmt = $pdo->prepare("UPDATE worker_profiles SET rating = ?, total_reviews = ? WHERE worker_id = ?");
                $upStmt->execute([$avgRating, $totalReviews, $workerId]);

                header("Location: ../client/my-requests.php?msg=review_submitted");
                exit();
            }
        }
    }
}
header("Location: ../client/my-requests.php?msg=review_failed");
