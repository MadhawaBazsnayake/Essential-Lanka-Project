<?php
// api/auth.php
session_start();
require_once '../config/db.php';

// JSON response එකක් යවන බව බ්‍රවුසරයට දැනුම් දීම
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ============================================
// 1. REGISTRATION LOGIC (ලියාපදිංචි වීම)
// ============================================
if ($action === 'register') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client'; // Default ලෙස Client කෙනෙක් ලෙස ගනී

    // මූලික වැරදි පරීක්ෂාව
    if (empty($firstName) || empty($phone) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // දුරකථන අංකය දැනටමත් Database එකේ තිබේදැයි පරීක්ෂා කිරීම
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $checkStmt->execute([$phone]);
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phone number already registered.']);
        exit;
    }

    // මුරපදය ආරක්ෂිතව හැෂ් කිරීම (Security)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // දත්ත සමුදායට අලුත් පරිශීලකයා ඇතුළත් කිරීම
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $phone, $hashedPassword, $role]);
        
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// 2. LOGIN LOGIC (පද්ධතියට ඇතුළත් වීම)
// ============================================
if ($action === 'login') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // තොරතුරු ලබා දී ඇත්දැයි බැලීම
    if (empty($phone) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Phone and password are required.']);
        exit;
    }

    // අදාළ දුරකථන අංකයෙන් පරිශීලකයෙක් සිටීදැයි බැලීම
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    // මුරපදය නිවැරදිදැයි පරීක්ෂා කිරීම (Password Verification)
    if ($user && password_verify($password, $user['password'])) {
        
        // ගිණුම සක්‍රිය (Active) මට්ටමේ පවතීදැයි බැලීම
        if ($user['is_active'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Your account is deactivated. Please contact support.']);
            exit;
        }

        // Login වූ පසු Sessions සැකසීම
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['phone'] = $user['phone'];

        // Role එක අනුව Redirect වන පිටුව තීරණය කිරීම
        $redirectUrl = '';
        if ($user['role'] === 'client') {
            $redirectUrl = 'client/dashboard.php';
        } elseif ($user['role'] === 'worker') {
            $redirectUrl = 'worker/dashboard.php';
        } else {
            $redirectUrl = 'admin/dashboard.php';
        }

        echo json_encode(['status' => 'success', 'message' => 'Login successful', 'redirect' => $redirectUrl]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid phone number or password.']);
    }
    exit;
}

// Action එක නිවැරදි නොමැතිනම් (Direct URL access)
echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
exit;
?>