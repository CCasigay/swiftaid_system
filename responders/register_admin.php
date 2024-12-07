<?php
include 'db_connect.php';

// Ensure required parameters are received
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo "Username and password required.";
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// Hash the password using bcrypt (secure way)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert the user into the users table
$query = "INSERT INTO `users` (`username`, `password`, `role`) VALUES ('$username', '$hashedPassword', 'admin')";

if ($conn->query($query)) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
