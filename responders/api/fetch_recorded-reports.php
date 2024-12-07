<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once '../api/db_connect.php';

// Query the database for reports
$query = "SELECT `id`, `user_id`, `sender_name`, `latitude`, `longitude`,`severity`, `status`, `date`, `time`, `response_at` FROM `recorded-reports`";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    echo json_encode($reports);
} else {
    echo json_encode([]);
}

$conn->close();
?>
