<?php
// Database configuration
$host = 'localhost'; // Change if using a different server
$db = 'swiftbase'; // Updated database name
$user = 'root'; // Default username for XAMPP
$pass = ''; // Default password for XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
