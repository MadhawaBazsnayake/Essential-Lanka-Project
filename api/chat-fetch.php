<?php
// api/chat-fetch.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$jobId = $_GET['job_id'] ?? null;
$otherUserId = $_GET['other_user_id'] ?? $_GET['receiver_id'] ?? null;

if (!$jobId || !$otherUserId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit();
}

$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE job_id = ? 
    AND (
        (sender_id = ? AND receiver_id = ?) 
        OR 
        (sender_id = ? AND receiver_id = ?)
    )
    ORDER BY created_at ASC
");
$stmt->execute([$jobId, $userId, $otherUserId, $otherUserId, $userId]);
$messages = $stmt->fetchAll();

echo json_encode(['status' => 'success', 'messages' => $messages]);
