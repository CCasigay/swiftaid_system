<?php
$servername = "localhost";
$username = "root"; // Default MySQL username
$password = ""; // Default MySQL password
$dbname = "swiftbase"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get latitude and longitude from POST request
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

// Insert data into the database
$sql = "INSERT INTO location_data (latitude, longitude) VALUES ('$latitude', '$longitude')";

if ($conn->query($sql) === TRUE) {
  echo "Location data saved successfully!";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
