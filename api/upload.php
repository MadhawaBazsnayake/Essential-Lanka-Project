<?php
// api/upload.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$userId = $_SESSION['user_id'];
$uploadType = $_POST['upload_type'] ?? 'general'; // profile_pic, gig_image, portfolio

// File එකක් එවා ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or an upload error occurred.']);
    exit;
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];

// File Validation: අනුමත කරන Extensions
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and WEBP are allowed.']);
    exit;
}

// File Validation: උපරිම ප්‍රමාණය (5MB)
if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 5MB limit.']);
    exit;
}

// Upload කරන ෆෝල්ඩරය තීරණය කිරීම
$targetDir = '../uploads/' . $uploadType . '/';

// ෆෝල්ඩරය නොමැතිනම් අලුතින් සෑදීම
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// අලුත් අනන්‍ය නමක් සෑදීම (Duplicate files වළක්වා ගැනීමට)
$newFileName = $uploadType . '_' . $userId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
$targetFilePath = $targetDir . $newFileName;
$databasePath = 'uploads/' . $uploadType . '/' . $newFileName; // DB එකට සේව් කරන path එක (../ රහිතව)

try {
    // File එක Server එකට Move කිරීම
    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
        
        // Profile Picture එකක් නම් කෙලින්ම Database එක Update කිරීම
        if ($uploadType === 'profile_pic') {
            $updateStmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $updateStmt->execute([$databasePath, $userId]);
            
            // Session එකේ තියෙන පින්තූරයත් අප්ඩේට් කළ හැක
            $_SESSION['profile_picture'] = $databasePath;
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'File uploaded successfully.',
            'file_path' => $databasePath
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move the uploaded file.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;
?>