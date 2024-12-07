<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../responders/login_admin.php"); // Redirect to admin login page
    exit;
}

// Check if the role is set in the session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../responders/login_admin.php"); // Redirect to admin login page if not an admin
    exit;
}
?>
