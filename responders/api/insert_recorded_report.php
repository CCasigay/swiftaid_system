<?php
// insert_recorded_report.php
header('Content-Type: application/json');
include '../responders/api/db_connect.php';

// Get the POST data
$data = json_decode(file_get_contents("php://input"));

$report_id = $data->report_id;
$latitude = $data->latitude;
$longitude = $data->longitude;
$responder_lat = $data->responder_lat;
$responder_lng = $data->responder_lng;

// Insert the accepted report into the recorded_reports table
$sql = "INSERT INTO `recorded-reports` (report_id, latitude, longitude, responder_latitude, responder_longitude, status) 
        VALUES (?, ?, ?, ?, ?, 'Accepted')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddddd", $report_id, $latitude, $longitude, $responder_lat, $responder_lng);

if ($stmt->execute()) {
    // Update the report status to "Accepted" in the new-reports table
    $update_sql = "UPDATE `new-reports` SET status = 'Accepted' WHERE report_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("d", $report_id);
    $update_stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
