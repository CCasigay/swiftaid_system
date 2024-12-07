<?php
include '../responders/api/db_connect.php';

// Ensure the database connection is valid
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch reports with status NULL or empty
$newReportsQuery = "SELECT * FROM `new-reports` WHERE `status` IS NULL OR `status` = 'Pending'";

$result = $conn->query($newReportsQuery);

$response = [
    "newReports" => [],
];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response["newReports"][] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
