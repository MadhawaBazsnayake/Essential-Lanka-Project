<?php
// api/portfolio-process.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit;
}

$workerId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add_portfolio') {
    $title = trim($_POST['title'] ?? '');
    
    if (isset($_FILES['portfolio_image']) && $_FILES['portfolio_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/portfolios/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['portfolio_image']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $newFileName = 'portfolio_' . $workerId . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;
            $dbPath = 'uploads/portfolios/' . $newFileName;

            if (move_uploaded_file($_FILES['portfolio_image']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO portfolios (worker_id, title, image_path) VALUES (?, ?, ?)");
                $stmt->execute([$workerId, $title, $dbPath]);
            }
        }
    }
    header("Location: ../worker/portfolio.php?status=success");
    exit;
}

if ($action === 'delete_portfolio') {
    $id = $_POST['portfolio_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM portfolios WHERE id = ? AND worker_id = ?");
    $stmt->execute([$id, $workerId]);
    echo json_encode(['status' => 'success']);
    exit;
}
?>
