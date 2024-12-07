<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unread_count = 0;

try {
    // Count unread notifications with specific statuses
    $query = "
        SELECT 
            (SELECT COUNT(*) 
             FROM `new-reports` 
             WHERE user_id = :user_id 
             AND read_status = 0 
             AND status IN ('pending'))
            +
            (SELECT COUNT(*) 
             FROM `recorded-reports` 
             WHERE user_id = :user_id 
             AND read_status = 0 
             AND status IN ('accepted'))
            AS unread_count
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread_count = $result['unread_count'] ?? 0;

    echo json_encode(['unread_count' => $unread_count]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching unread notifications: ' . $e->getMessage()]);
}
?>
