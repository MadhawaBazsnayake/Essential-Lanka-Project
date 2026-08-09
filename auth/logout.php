<?php
// auth/logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page (Root directory)
header("Location: ../login.php");
exit();
?>