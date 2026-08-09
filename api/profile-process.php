<?php
// api/profile-process.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db.php';

// Form එක Submit කරලා තියෙන්නේ POST මෙතඩ් එකෙන්ද සහ action එක update_profile ද කියලා බලනවා
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    
    $userId = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    // Form එකෙන් එන දත්ත ආරක්ෂිතව ලබා ගැනීම
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    $profilePicPath = null;

    // පින්තූරයක් අප්ලෝඩ් කරලා තියෙනවද කියලා බලනවා
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        
        $uploadDir = '../uploads/profiles/';
        
        // uploads/profiles ෆෝල්ඩරය නැත්නම් ඒක හදනවා
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // අනුමත කරන File Types (ආරක්ෂාව සඳහා)
        $allowedExts = ['jpg', 'jpeg', 'png'];
        
        if (in_array($fileExtension, $allowedExts)) {
            // ෆොටෝ එකට අලුත් අද්විතීය නමක් දෙනවා (උදා: user_5_169450302.jpg)
            $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            // ෆොටෝ එක අපේ සර්වර් එකේ ෆෝල්ඩරයට මාරු කරනවා
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // ඩේටාබේස් එකට සේව් කරන්න ඕන පාත් එක හදාගන්නවා
                $profilePicPath = 'uploads/profiles/' . $newFileName;
            }
        } else {
            // වැරදි ෆයිල් එකක් නම්, ආපසු හරවා යවනවා (Error message එකක් සමඟ)
            $redirectUrl = ($role === 'client') ? '../client/dashboard.php' : '../worker/dashboard.php';
            header("Location: " . $redirectUrl . "?error=invalid_file_type");
            exit();
        }
    }

    try {
        // ෆොටෝ එකක් අප්ලෝඩ් කරලා තියෙනවා නම්, ෆොටෝ එකත් එක්කම අප්ඩේට් කරනවා
        if ($profilePicPath) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $phone, $profilePicPath, $userId]);
        } else {
            // ෆොටෝ එකක් අප්ලෝඩ් කරලා නැත්නම්, අනිත් විස්තර විතරක් අප්ඩේට් කරනවා
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $phone, $userId]);
        }
        
        // Session එකේ තියෙන නමත් අප්ඩේට් කරනවා (එතකොට පේජ් එක රීලෝඩ් වෙද්දිම අලුත් නම පෙන්නනවා)
        $_SESSION['name'] = $firstName;
        
        // පරිශීලකයාගේ Role එක අනුව අදාළ Dashboard එකට හරවා යවනවා
        $redirectUrl = ($role === 'client') ? '../client/dashboard.php' : '../worker/dashboard.php';
        header("Location: " . $redirectUrl . "?status=profile_updated");
        exit();
        
    } catch (PDOException $e) {
        // මොකක් හරි ඩේටාබේස් අවුලක් ගියොත්
        die("Error updating profile: " . $e->getMessage());
    }
} else {
    // කෙලින්ම URL එකෙන් මේ පේජ් එකට ආවොත්, ආපහු යවනවා
    header("Location: ../index.php");
    exit();
}
?>