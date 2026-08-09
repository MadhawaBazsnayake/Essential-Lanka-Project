<?php
// api/sms-webhook.php
require_once '../config/db.php';

// Set header to acknowledge receipt of the webhook
header('Content-Type: application/json');

// Retrieve the incoming payload from the SMS Gateway
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Fallback to $_POST if the gateway sends form-urlencoded data instead of JSON
if (empty($data)) {
    $data = $_POST;
}

// Extract standard SMS details (Adjust keys based on your specific SMS provider's API documentation)
$senderPhone = $data['sender'] ?? ($data['sourceAddress'] ?? '');
$message = trim($data['message'] ?? ($data['text'] ?? ''));

if (empty($senderPhone) || empty($message)) {
    // Log the error for debugging purposes
    error_log("SMS Webhook Error: Missing sender or message data. Payload: " . $input);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload structure.']);
    exit;
}

// Standardize phone number format (e.g., converting +9471... or 9471... to 071...)
$cleanPhone = preg_replace('/^(\+94|94)/', '0', $senderPhone);

// Identify the user based on their registered phone number
$stmt = $pdo->prepare("SELECT id, role FROM users WHERE phone = ? LIMIT 1");
$stmt->execute([$cleanPhone]);
$user = $stmt->fetch();

if (!$user) {
    error_log("SMS Webhook Error: Received SMS from unregistered phone number - " . $cleanPhone);
    echo json_encode(['status' => 'ignored', 'message' => 'Unregistered user.']);
    exit;
}

$userId = $user['id'];
$role = $user['role'];

// Parse the SMS command (Example format expected: "ACCEPT 105")
$parts = explode(' ', strtoupper($message));
$command = $parts[0] ?? '';
$targetId = $parts[1] ?? '';

// ============================================
// COMMAND LOGIC: Worker accepting a job via SMS
// ============================================
if ($role === 'worker' && $command === 'ACCEPT' && is_numeric($targetId)) {
    try {
        // Verify if the job is still available
        $jobStmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ? AND status = 'open'");
        $jobStmt->execute([$targetId]);
        
        if ($jobStmt->rowCount() > 0) {
            // Update the job status to 'in_progress' and assign it to this worker
            $updateStmt = $pdo->prepare("UPDATE jobs SET status = 'in_progress', assigned_worker_id = ? WHERE id = ?");
            $updateStmt->execute([$userId, $targetId]);
            
            error_log("SMS Webhook Success: Job #$targetId accepted by Worker #$userId via SMS.");
            
            // Note: Here you can trigger an outgoing API call to your SMS gateway 
            // to send a confirmation SMS back to the worker and the client.
        } else {
            error_log("SMS Webhook Info: Job #$targetId is no longer open or does not exist.");
        }
    } catch (PDOException $e) {
        error_log("SMS Webhook DB Error: " . $e->getMessage());
    }
} else {
    // Log any unrecognized commands
    error_log("SMS Webhook Info: Unhandled command '$command' from User #$userId.");
}

// Always respond with a 200 HTTP status code so the SMS gateway knows the webhook was received successfully
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'SMS payload processed successfully.']);
?>