<?php
session_start();
require '../config/config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Update all reports' read_status to 1 (marked as read)
    $query = "UPDATE `new-reports` SET read_status = 1 WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);

    // Update all recorded-reports' read_status to 1 (marked as read)
    $query = "UPDATE `recorded-reports` SET read_status = 1 WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);

    // Send success response
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Handle error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
