<?php 
// includes/message.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$status = $status ?? 'success';
$title = $title ?? 'Notification';
$message = $message ?? '';
$redirect_url = $redirect_url ?? '../index.php';
$redirect_text = $redirect_text ?? 'Go to Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- නිවැරදි CSS Path එක (Root එකට යාමට ../ පාවිච්චි කර ඇත) -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<style>
    .msg-section { padding: 100px 5%; min-height: 80vh; display: flex; align-items: center; justify-content: center; position: relative; }
    .msg-card { max-width: 500px; width: 100%; padding: 50px 40px; border-radius: 35px; text-align: center; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.9); box-shadow: 0 20px 50px rgba(0,0,0,0.08); z-index: 10; position: relative;}
    .msg-icon { font-size: 4.5rem; margin-bottom: 20px; animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .success-icon { color: #10b981; }
    .error-icon { color: #ef4444; }
    
    @keyframes bounceIn {
        0% { transform: scale(0); opacity: 0; }
        60% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); }
    }
</style>

<section class="msg-section bg-cream">
    <div class="blob-bg"></div>
    
    <div class="msg-card fade-up show">
        <?php if($status === 'success'): ?>
            <i class="ph-fill ph-check-circle msg-icon success-icon"></i>
        <?php else: ?>
            <i class="ph-fill ph-warning-circle msg-icon error-icon"></i>
        <?php endif; ?>

        <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 15px; color: #1a1a1a;"><?php echo $title; ?></h2>
        <p style="font-size: 1.1rem; color: #666; margin-bottom: 35px; line-height: 1.6;"><?php echo $message; ?></p>

        <a href="<?php echo $redirect_url; ?>" class="btn-primary" style="display: inline-block; padding: 16px 40px; text-decoration: none;"><?php echo $redirect_text; ?></a>
    </div>
</section>

</body>
</html>