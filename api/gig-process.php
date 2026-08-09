<?php
// api/gig-process.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// වර්කර් කෙනෙක් ලෙස ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම (ආරක්ෂාව සඳහා)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $workerId = $_SESSION['user_id'];

    // ==========================================
    // CREATE NEW GIG
    // ==========================================
    if ($action === 'create_gig') {
        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $basePrice = floatval($_POST['base_price']);
        $description = trim($_POST['description']);
        
        $imagePath = null;

        // පින්තූරයක් අප්ලෝඩ් කර ඇත්නම් එය සකස් කිරීම
        if (isset($_FILES['gig_image']) && $_FILES['gig_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/gigs/';
            
            // ෆෝල්ඩරය නොමැති නම් එය සෑදීම
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $_FILES['gig_image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExts)) {
                // එකම නම ඇති ෆයිල් ගැටීම වැළැක්වීමට අලුත් නමක් සෑදීම
                $newFileName = 'gig_' . $workerId . '_' . time() . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['gig_image']['tmp_name'], $destPath)) {
                    $imagePath = 'uploads/gigs/' . $newFileName;
                }
            }
        }

        try {
            // දත්ත සමුදායට දත්ත ඇතුළත් කිරීම
            $stmt = $pdo->prepare("INSERT INTO gigs (worker_id, title, category, base_price, description, image_path, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$workerId, $title, $category, $basePrice, $description, $imagePath]);
            
            header("Location: ../worker/my-gigs.php?status=gig_created");
            exit();
        } catch (PDOException $e) {
            die("Error creating gig: " . $e->getMessage());
        }

    // ==========================================
    // UPDATE EXISTING GIG
    // ==========================================
    } elseif ($action === 'update_gig') {
        $gigId = intval($_POST['gig_id']);
        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $basePrice = floatval($_POST['base_price']);
        $description = trim($_POST['description']);

        // මෙම Gig එක වෙනස් කරන වර්කර්ටම අයිති එකක් දැයි පරීක්ෂා කිරීම (අනවසර වෙනස් කිරීම් වැළැක්වීමට)
        $checkStmt = $pdo->prepare("SELECT image_path FROM gigs WHERE id = ? AND worker_id = ?");
        $checkStmt->execute([$gigId, $workerId]);
        $existingGig = $checkStmt->fetch();

        if (!$existingGig) {
            die("Unauthorized access or gig not found.");
        }

        $imagePath = $existingGig['image_path']; // අලුත් පින්තූරයක් නොදැම්මොත් පරණ පින්තූරයම තබා ගැනීම

        // අලුතින් පින්තූරයක් අප්ලෝඩ් කර ඇත්නම්
        if (isset($_FILES['gig_image']) && $_FILES['gig_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/gigs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = $_FILES['gig_image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExtension, $allowedExts)) {
                $newFileName = 'gig_' . $workerId . '_' . time() . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['gig_image']['tmp_name'], $destPath)) {
                    $imagePath = 'uploads/gigs/' . $newFileName;
                }
            }
        }

        try {
            // දත්ත සමුදාය යාවත්කාලීන කිරීම (Update)
            $stmt = $pdo->prepare("UPDATE gigs SET title = ?, category = ?, base_price = ?, description = ?, image_path = ? WHERE id = ? AND worker_id = ?");
            $stmt->execute([$title, $category, $basePrice, $description, $imagePath, $gigId, $workerId]);
            
            header("Location: ../worker/my-gigs.php?status=gig_updated");
            exit();
        } catch (PDOException $e) {
            die("Error updating gig: " . $e->getMessage());
        }
    }
} else {
    // කෙලින්ම URL එකෙන් ආවොත් හරවා යැවීම
    header("Location: ../index.php");
    exit();
}
?>