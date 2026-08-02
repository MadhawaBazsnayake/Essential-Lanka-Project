<?php
// සෙෂන් එකක් දැනටමත් ඇත්නම් එය නැවත ආරම්භ නොකිරීම
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language Switch Logic
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

// Load Translations
$translations = include __DIR__ . "/../lang/{$lang}.php";

// Translation helper function
function t($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Essential Lanka - Premium Local Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <i class="ph-fill ph-infinity"></i> <strong>ESSENTIAL</strong> LANKA
    </div>
    <div class="nav-links">
        <a href="#services"><?php echo t('nav_services'); ?></a>
        <a href="#how-it-works"><?php echo t('nav_how_it_works'); ?></a>
        <a href="#trust"><?php echo t('nav_safety'); ?></a>
    </div>
    <div class="nav-actions">
        <div class="lang-switch">
            <a href="?lang=en" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
            <a href="?lang=si" class="<?php echo $lang == 'si' ? 'active' : ''; ?>">සිං</a>
        </div>
        <a href="login.php" class="btn-outline"><?php echo t('nav_login'); ?></a>
    </div>
</nav>