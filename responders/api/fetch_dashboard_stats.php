<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$database = "swiftbase"; // Your database name

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch counts from `new-reports`
$newReportsQuery = "SELECT COUNT(*) as new_reports_count FROM `new-reports`";
$newReportsResult = $conn->query($newReportsQuery);
$newReportsCount = $newReportsResult->fetch_assoc()['new_reports_count'] ?? 0;

// Fetch counts from `recorded-reports`
$recordedReportsQuery = "SELECT COUNT(*) as recorded_reports_count FROM `recorded-reports`";
$recordedReportsResult = $conn->query($recordedReportsQuery);
$recordedReportsCount = $recordedReportsResult->fetch_assoc()['recorded_reports_count'] ?? 0;

// Send response as JSON
$response = [
    "new_reports" => $newReportsCount,
    "recorded_reports" => $recordedReportsCount
];
echo json_encode($response);

$conn->close();
?>
