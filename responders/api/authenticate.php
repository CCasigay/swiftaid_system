<?php
session_start();
include '../responders/api/db_connect.php';

// Ensure the user provided the username and password
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: login.php");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

// Check if the user exists
$query = "SELECT * FROM `users` WHERE `username` = '$username'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Password is correct, log the user in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect based on user role
        if ($user['role'] === 'admin') {
            header("Location: ../responders/dsboard.php"); // Admin Dashboard
        } else {
            header("Location: ../responders/user_home.php"); // Regular User Dashboard
        }
        exit;
    } else {
        // Invalid password
        echo "Invalid password.";
    }
} else {
    // User not found
    echo "User not found.";
}

$conn->close();
?>
