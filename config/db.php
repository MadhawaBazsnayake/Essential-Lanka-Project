<?php
// config/db.php

$host = 'localhost';
$dbname = 'essential_lanka'; // අපි schema.sql එකේ හදපු Database එකේ නම
$username = 'root';          // XAMPP හි default username
$password = '';              // XAMPP හි default password (හිස්ව තබන්න)

try {
    // Database එකට සම්බන්ධ වීම (UTF-8 සහාය ද සහිතව)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Security සහ Error Handling සැකසුම්
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // සම්බන්ධතාවය අසාර්ථක වුවහොත් පෙන්වන පණිවිඩය
    die("Database Connection Failed: " . $e->getMessage());
}
?>