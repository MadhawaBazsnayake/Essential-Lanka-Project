<?php
// api/bid-process.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'submit_bid') {
        $jobId = $_POST['job_id'] ?? null;
        $amount = $_POST['bid_amount'] ?? null;
        $proposal = $_POST['cover_letter'] ?? '';
        $workerId = $_SESSION['user_id'];

        if ($jobId && $amount) {
            // Check if already bid
            $chk = $pdo->prepare("SELECT id FROM bids WHERE job_id = ? AND worker_id = ?");
            $chk->execute([$jobId, $workerId]);
            if ($chk->fetch()) {
                header("Location: ../worker/dashboard.php?msg=already_bid");
                exit();
            }

            $stmt = $pdo->prepare("INSERT INTO bids (job_id, worker_id, amount, proposal, status) VALUES (?, ?, ?, ?, 'pending')");
            if ($stmt->execute([$jobId, $workerId, $amount, $proposal])) {
                header("Location: ../worker/dashboard.php?msg=bid_submitted");
                exit();
            }
        }
    }
}
header("Location: ../worker/dashboard.php?msg=bid_failed");
