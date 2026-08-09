<?php
// api/admin-process.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized admin access.']);
    exit;
}

$action = $_POST['action'] ?? '';

// 1. VERIFY WORKER ACCOUNT (Approve / Reject)
if ($action === 'verify_worker') {
    $workerId = $_POST['worker_id'] ?? '';
    $status = $_POST['status'] ?? ''; // 'verified' or 'rejected'

    if (empty($workerId) || !in_array($status, ['verified', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE worker_profiles SET verification_status = ? WHERE worker_id = ?");
        $stmt->execute([$status, $workerId]);

        echo json_encode(['status' => 'success', 'message' => 'Worker verification status updated to ' . ucfirst($status)]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 2. RESOLVE DISPUTE
if ($action === 'resolve_dispute') {
    $disputeId = $_POST['dispute_id'] ?? '';

    if (empty($disputeId)) {
        echo json_encode(['status' => 'error', 'message' => 'Dispute ID is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'completed' WHERE id = ?");
        $stmt->execute([$disputeId]);

        echo json_encode(['status' => 'success', 'message' => 'Dispute resolved successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 3. ADD CATEGORY
if ($action === 'add_category') {
    $nameEn = trim($_POST['name_en'] ?? '');
    $iconClass = trim($_POST['icon_class'] ?? 'ph-squares-four');

    if (empty($nameEn)) {
        echo json_encode(['status' => 'error', 'message' => 'Category name required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name_en, name_si, icon_class) VALUES (?, ?, ?)");
        $stmt->execute([$nameEn, $nameEn, $iconClass]);
        echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. DELETE CATEGORY
if ($action === 'delete_category') {
    $catId = $_POST['cat_id'] ?? '';

    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$catId]);
        echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
exit;
?>
