<?php
// check_new_reports.php
include '../api/db_connect.php'; // Include your database connection

// Query to check for new reports with status 'Pending'
$result = $conn->query("SELECT COUNT(*) AS count FROM `new-reports` WHERE status = 'Pending'");

if ($result) {
    $data = $result->fetch_assoc();
    // Check if there are any new reports
    if ($data['count'] > 0) {
        echo json_encode(["new_reports" => true]); // If new reports exist, return true
    } else {
        echo json_encode(["new_reports" => false]); // If no new reports, return false
    }
} else {
    echo json_encode(["new_reports" => false]); // Return false in case of a query error
}

$conn->close(); // Close the database connection
?>
