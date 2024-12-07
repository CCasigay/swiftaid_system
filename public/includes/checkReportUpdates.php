<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id, status, read_status
        FROM `recorded-reports`
        WHERE user_id = :user_id AND read_status = 0 AND status = 'Accepted'
    ");
    $stmt->execute([':user_id' => $user_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($reports)) {
        // Mark as read to avoid duplicate notifications
        $report_ids = array_column($reports, 'id');
        $ids_placeholder = implode(',', array_fill(0, count($report_ids), '?'));

        $update_stmt = $pdo->prepare("UPDATE `recorded-reports` SET read_status = 1 WHERE id IN ($ids_placeholder)");
        $update_stmt->execute($report_ids);

        echo json_encode(['success' => true, 'message' => 'Authorities are on their way!', 'report_ids' => $report_ids]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No new updates.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
