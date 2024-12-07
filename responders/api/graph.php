<?php
// Set up database connection
$servername = "localhost";  // Your MySQL server
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password (if any)
$dbname = "swiftbase";       // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get the count of reports grouped by date
$sql = "SELECT date, COUNT(id) AS report_count FROM `recorded-reports` GROUP BY date ORDER BY date ASC";

// Execute the query
$result = $conn->query($sql);

// Initialize an array to hold the data
$reports = [];

// Check if there are results and populate the array
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reports[] = [
            'date' => $row['date'],
            'count' => $row['report_count']
        ];
    }
} else {
    // If no data found, return an empty array
    $reports = [];
}

// Close the database connection
$conn->close();

// Set the response content type to JSON
header('Content-Type: application/json');

// Return the data as JSON
echo json_encode($reports);
?>
