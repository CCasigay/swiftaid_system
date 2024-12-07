<?php
session_start();
include_once '../responders/config/config.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: ../responders/welcome.php');
    exit;
}
?>
