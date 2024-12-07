<?php
session_start();
require '../config/config.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasNewNotifications' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM `new-reports` WHERE user_id = :user_id AND read_status = 0)
            +
            (SELECT COUNT(*) FROM `recorded-reports` WHERE user_id = :user_id AND read_status = 0)
            AS unread_count
    ");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread_count = $result['unread_count'] ?? 0;

    // Check if new notifications are available
    echo json_encode(['hasNewNotifications' => $unread_count > 0]);
} catch (PDOException $e) {
    echo json_encode(['hasNewNotifications' => false]);
}
