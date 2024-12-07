<?php
session_start();
include_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: ../public/mains/login.php');
    exit;
}
?>
