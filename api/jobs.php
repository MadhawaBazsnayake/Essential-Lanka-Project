<?php
// api/jobs.php
session_start();
require_once '../config/db.php';

// JSON response එකක් යවන බව බ්‍රවුසරයට දැනුම් දීම
header('Content-Type: application/json');

// පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login.']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ============================================
// 1. CREATE JOB (අලුත් ජොබ් එකක් පළ කිරීම - Clients පමණි)
// ============================================
if ($action === 'create_job') {
    if ($role !== 'client') {
        echo json_encode(['status' => 'error', 'message' => 'Only clients can post new jobs.']);
        exit;
    }

    $categoryId = $_POST['category_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = $_POST['budget'] ?? 0;
    $lat = $_POST['location_lat'] ?? null;
    $lng = $_POST['location_lng'] ?? null;

    if (empty($categoryId) || empty($title) || empty($description) || empty($budget)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    try {
        // Transaction එකක් ආරම්භ කිරීම (ජොබ් එක සහ පින්තූර දෙකම එකවර සේව් කිරීමට)
        $pdo->beginTransaction();

        // Jobs වගුවට දත්ත ඇතුළත් කිරීම
        $stmt = $pdo->prepare("INSERT INTO jobs (client_id, category_id, title, description, budget, location_lat, location_lng, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'open')");
        $stmt->execute([$userId, $categoryId, $title, $description, $budget, $lat, $lng]);
        $jobId = $pdo->lastInsertId();

        // පින්තූර Upload කර ඇත්නම් ඒවා job_images වගුවට ඇතුළත් කිරීම
        if (isset($_FILES['job_images']) && !empty($_FILES['job_images']['name'][0])) {
            $uploadDir = '../uploads/jobs/';
            // ෆෝල්ඩරය නොමැතිනම් එය සෑදීම
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imgStmt = $pdo->prepare("INSERT INTO job_images (job_id, image_path) VALUES (?, ?)");
            
            foreach ($_FILES['job_images']['tmp_name'] as $key => $tmp_name) {
                // ආරක්ෂිතව File Name එකක් සෑදීම
                $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['job_images']['name'][$key]));
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $targetFilePath)) {
                    $dbPath = 'uploads/jobs/' . $fileName;
                    $imgStmt->execute([$jobId, $dbPath]);
                }
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Job posted successfully!', 'job_id' => $jobId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to post job: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// 2. UPDATE JOB STATUS (ජොබ් එකේ තත්ත්වය වෙනස් කිරීම)
// ============================================
if ($action === 'update_status') {
    $jobId = $_POST['job_id'] ?? '';
    $newStatus = $_POST['status'] ?? ''; // open, in_progress, completed, cancelled

    $validStatuses = ['open', 'in_progress', 'completed', 'cancelled'];
    
    if (empty($jobId) || !in_array($newStatus, $validStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid job ID or status.']);
        exit;
    }

    try {
        // මෙය වෙනස් කරන්නේ අදාළ Client ද, නැතහොත් Assigned Worker ද යන්න පරීක්ෂා කිරීම
        if ($role === 'client') {
            $checkStmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND client_id = ?");
            $checkStmt->execute([$jobId, $userId]);
        } else if ($role === 'worker') {
            $checkStmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND assigned_worker_id = ?");
            $checkStmt->execute([$jobId, $userId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
            exit;
        }

        if ($checkStmt->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied or job not found.']);
            exit;
        }

        // තත්ත්වය යාවත්කාලීන කිරීම (Update)
        $updateStmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $jobId]);

        echo json_encode(['status' => 'success', 'message' => 'Job status successfully updated to ' . ucfirst(str_replace('_', ' ', $newStatus)) . '.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Action එක නිවැරදි නොමැතිනම්
echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
exit;
?>