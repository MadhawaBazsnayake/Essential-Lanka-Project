<?php
// api/auth.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ============================================
// 1. REGISTRATION LOGIC
// ============================================
if ($action === 'register') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client'; // default to client

    // මූලික වැරදි පරීක්ෂාව
    if (empty($firstName) || empty($phone) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // දුරකථන අංකය දැනටමත් තිබේදැයි පරීක්ෂා කිරීම
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $checkStmt->execute([$phone]);
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Phone number already registered.']);
        exit;
    }

    // මුරපදය හැෂ් කිරීම (Security)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $phone, $hashedPassword, $role]);
        
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// 2. LOGIN LOGIC
// ============================================
if ($action === 'login') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Phone and password are required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    // මුරපදය පරීක්ෂා කිරීම (Password Verification)
    if ($user && password_verify($password, $user['password'])) {
        
        // ගිණුම සක්‍රියදැයි පරීක්ෂා කිරීම
        if ($user['is_active'] == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Your account is deactivated. Contact admin.']);
            exit;
        }

        // Sessions සැකසීම
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

// Action එක නිවැරදි නොමැතිනම්
echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
exit;
?>