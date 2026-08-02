<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$db_path = '../config/db.php';
if (!file_exists($db_path)) {
    $status = 'error';
    $title = 'Database Error';
    $message = 'config/db.php file not found. Please check your folder structure.';
    $redirect_url = '../register.php';
    $redirect_text = 'Back to Register';
    include '../includes/message.php';
    exit();
}

require_once $db_path; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (empty($action)) {
        $status = 'error';
        $title = 'Invalid Action';
        $message = 'Form action is missing.';
        $redirect_url = '../register.php';
        $redirect_text = 'Back';
        include '../includes/message.php';
        exit();
    }

    // ==========================================
    // REGISTER LOGIC
    // ==========================================
    if ($action === 'register') {
        $role = $_POST['role'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $status = 'error';
                $title = 'Phone Already Registered';
                $message = 'This mobile number is already linked with another account. Please log in.';
                $redirect_url = '../login.php';
                $redirect_text = 'Go to Login';
                include '../includes/message.php';
                exit();
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $phone, $password, $role]);
            
            $userId = $pdo->lastInsertId();

            if ($role === 'worker') {
                $nic = $_POST['nic'] ?? 'N/A';
                $categoryId = $_POST['category_id'] ?? 1;
                $stmt = $pdo->prepare("INSERT INTO worker_profiles (worker_id, category_id, nic_number) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $categoryId, $nic]);
            }

            $pdo->commit();

            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $firstName;

            // Success Message View
            $status = 'success';
            $title = 'Registration Successful! 🎉';
            $message = "Welcome to Essential Lanka, {$firstName}! Your account has been created successfully as a " . ucfirst($role) . ".";
            $redirect_url = '../index.php';
            $redirect_text = 'Explore Home';
            include '../includes/message.php';
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $status = 'error';
            $title = 'Database Error';
            $message = $e->getMessage();
            $redirect_url = '../register.php';
            $redirect_text = 'Try Again';
            include '../includes/message.php';
            exit();
        }
    }

    // ==========================================
    // LOGIN LOGIC
    // ==========================================
    elseif ($action === 'login') {
        $phone = $_POST['phone'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'];

            $status = 'success';
            $title = 'Login Successful! 🎉';
            $message = "Welcome back, " . $user['first_name'] . "! You are now signed in.";
            $redirect_url = '../index.php';
            $redirect_text = 'Go to Dashboard';
            include '../includes/message.php';
            exit();
        } else {
            $status = 'error';
            $title = 'Login Failed';
            $message = 'Invalid mobile number or password. Please check your credentials and try again.';
            $redirect_url = '../login.php';
            $redirect_text = 'Try Again';
            include '../includes/message.php';
            exit();
        }
    }
} else {
    $status = 'error';
    $title = 'Direct Access Denied';
    $message = 'You cannot access this page directly. Please use the registration or login form.';
    $redirect_url = '../index.php';
    $redirect_text = 'Go to Home';
    include '../includes/message.php';
    exit();
}
?>