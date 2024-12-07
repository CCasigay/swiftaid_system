<?php
session_start();
require '../config/config.php';

header('Content-Type: application/json');

// Initialize the response array
$response = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['latitude'], $_POST['longitude'], $_POST['severity'])) {
        // Sanitize and get the data from the POST request
        $latitude = filter_input(INPUT_POST, 'latitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $longitude = filter_input(INPUT_POST, 'longitude', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $severity = filter_input(INPUT_POST, 'severity', FILTER_SANITIZE_STRING);

        if (isset($_SESSION['user_id'])) {
            // Case for logged-in user
            $user_id = $_SESSION['user_id'];

            try {
                // Fetch user details, including the phone number, from the 'users' table
                $stmt = $pdo->prepare("SELECT contact, name FROM users WHERE id = :user_id");
                $stmt->execute([':user_id' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Insert the new help request for the logged-in user
                    $stmt = $pdo->prepare("
                        INSERT INTO `new-reports` 
                        (user_id, sender_name, phone, latitude, longitude, severity, date, time, status) 
                        VALUES (:user_id, :sender_name, :phone, :latitude, :longitude, :severity, CURDATE(), CURTIME(), 'Pending')
                    ");
                    $stmt->execute([ 
                        ':user_id' => $user_id,
                        ':sender_name' => $user['name'],
                        ':phone' => $user['contact'], // Using the phone number of the logged-in user
                        ':latitude' => $latitude,
                        ':longitude' => $longitude,
                        ':severity' => $severity
                    ]);

                    $response['success'] = true;
                    $response['message'] = 'Help request sent successfully!';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'User not found.';
                }
            } catch (PDOException $e) {
                $response['success'] = false;
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            // No user is logged in
            $response['success'] = false;
            $response['message'] = 'User not logged in.';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Latitude, longitude, and severity are required.';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

// Return the response as JSON
echo json_encode($response);
?>
