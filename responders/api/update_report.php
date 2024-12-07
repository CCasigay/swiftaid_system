<?php
include '../api/db_connect.php';

// Ensure required parameters are received
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$reportId = intval($_GET['id']);
$status = $conn->real_escape_string($_GET['status']);

// Validate the status (Only 'Accepted' is allowed)
if ($status !== 'Accepted') {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

// Fetch the report details from `new-reports` first
$getReportQuery = "SELECT * FROM `new-reports` WHERE `report_id` = $reportId";
$result = $conn->query($getReportQuery);

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}

$report = $result->fetch_assoc();

// Prepare the data to be inserted into `recorded-reports`
$name = $conn->real_escape_string($report['sender_name']);
$phone = $conn->real_escape_string($report['phone']);
$latitude = $conn->real_escape_string($report['latitude']);
$longitude = $conn->real_escape_string($report['longitude']);
$severity = $conn->real_escape_string($report['severity']);
$date = $conn->real_escape_string($report['date']);
$time = $conn->real_escape_string($report['time']);
$reportStatus = $conn->real_escape_string($status); // Only "Accepted" status allowed

// Insert the report into `recorded-reports`
$insertQuery = "INSERT INTO `recorded-reports` (`user_id`, `sender_name`, `phone`, `latitude`, `longitude`, `severity`, `date`, `time`, `status`)
                VALUES ('" . $report['user_id'] . "', '$name', '$phone', '$latitude', '$longitude', '$severity', '$date', '$time', '$reportStatus')";

if ($conn->query($insertQuery)) {
    // Delete the report from `new-reports`
    $deleteQuery = "DELETE FROM `new-reports` WHERE `report_id` = $reportId";

    if ($conn->query($deleteQuery)) {
        echo json_encode(['success' => true, 'message' => 'Report processed successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete the report from new-reports.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to insert report into recorded-reports.']);
}

$conn->close();
?>
