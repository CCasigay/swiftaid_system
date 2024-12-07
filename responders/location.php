<?php
// location.php

// Connect to your MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "swiftbase"; // Use your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Retrieve the location data
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$timestamp = $data['timestamp'];

// Insert data into the 'user_reports' table
$sql = "INSERT INTO `new-reports` (latitude, longitude, timestamp) VALUES ('$latitude', '$longitude', '$timestamp')";

if ($conn->query($sql) === TRUE) {
    echo "Location saved successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();
?>
