<?php
$host = 'localhost';
$username = 'root'; // Adjust if using a custom username
$password = '';     // Adjust if using a password
$dbname = 'swiftbase';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
