<?php
require '../responders/config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

// Ensure the required data is available
if (isset($_POST['response_id'], $_POST['status'], $_POST['location'])) {
    $responseId = $_POST['response_id'];
    $status = $_POST['status']; // 'accepted' or 'ignored'
    $user_id = $_SESSION['user_id']; // Current logged-in user (responder)
    $location = $_POST['location']; // Responder's location (latitude,longitude)

    try {
        // Fetch the help request data (including user_id, location)
        $stmt = $pdo->prepare("SELECT user_id, latitude, longitude FROM help_requests WHERE id = :response_id");
        $stmt->execute([':response_id' => $responseId]);
        $helpRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($helpRequest) {
            // Get the user_id of the responder
            $responder_id = $user_id;

            // Insert the response into the 'responses' table
            $stmt = $pdo->prepare("INSERT INTO responses (user_id, help_request_id, location, status, response_time) 
                                   VALUES (:user_id, :help_request_id, :location, :status, NOW())");
            $stmt->execute([
                ':user_id' => $responder_id,
                ':help_request_id' => $responseId,
                ':location' => $location,
                ':status' => $status
            ]);

            // Optionally, update the status of the help request (if the status should change upon responder's action)
            $updateStmt = $pdo->prepare("UPDATE help_requests SET status = :status WHERE id = :response_id");
            $updateStmt->execute([
                ':status' => $status,
                ':response_id' => $responseId
            ]);

            echo "Status updated to $status and response saved.";
        } else {
            echo "Help request not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Required data missing.";
}
?>
