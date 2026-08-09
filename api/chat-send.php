<?php
// api/chat-send.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'send_message') {
        $jobId = $_POST['job_id'] ?? null;
        $receiverId = $_POST['receiver_id'] ?? null;
        $message = $_POST['message'] ?? '';
        $senderId = $_SESSION['user_id'];

        if ($jobId && $receiverId && !empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO messages (job_id, sender_id, receiver_id, message, is_read) VALUES (?, ?, ?, ?, 0)");
            if ($stmt->execute([$jobId, $senderId, $receiverId, $message])) {
                echo json_encode(['status' => 'success']);
                exit();
            }
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
