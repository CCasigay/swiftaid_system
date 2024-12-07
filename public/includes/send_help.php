<?php
// Receive JSON data from the frontend
$data = json_decode(file_get_contents("php://input"), true);

// Validate data
if (isset($data['phone'], $data['latitude'], $data['longitude'], $data['severity'])) {
    $phone = $data['phone'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $severity = $data['severity'];

    // Database connection
    $host = 'localhost'; // Change if needed
    $db = 'swiftbase';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 1: Insert data into the new-reports table
        $sqlReport = "INSERT INTO `new-reports` (`phone`, `latitude`, `longitude`, `severity`) 
                      VALUES (:phone, :latitude, :longitude, :severity)";
        $stmtReport = $pdo->prepare($sqlReport);
        $stmtReport->bindParam(':phone', $phone);
        $stmtReport->bindParam(':latitude', $latitude);
        $stmtReport->bindParam(':longitude', $longitude);
        $stmtReport->bindParam(':severity', $severity);
        $stmtReport->execute();

        // Step 2: Insert data into the users table (using the phone value from the report)
        $guestSessionId = uniqid(); // Generate a unique guest session ID

        $sqlUser = "INSERT INTO `users` (`name`, `email`, `password`, `address`, `contact`, `location`, `status`, `is_guest`, `guest_session_id`) 
                    VALUES (:name, :email, :password, :address, :contact, :location, :status, :is_guest, :guest_session_id)";
        $stmtUser = $pdo->prepare($sqlUser);

        // Bind values to the user insert query
        $stmtUser->bindParam(':name', $name);
        $stmtUser->bindParam(':email', $email);
        $stmtUser->bindParam(':password', $password);
        $stmtUser->bindParam(':address', $address);
        $stmtUser->bindParam(':contact', $contact);
        $stmtUser->bindParam(':location', $location);
        $stmtUser->bindParam(':status', $status);
        $stmtUser->bindParam(':is_guest', $is_guest);
        $stmtUser->bindParam(':guest_session_id', $guest_session_id);

        // Set values for the user data
        $name = 'Unknown';
        $email = null;
        $password = null;
        $address = null;
        $contact = $phone; // Use the phone from the new-reports table
        $location = null;
        $status = null;
        $is_guest = 1;

        // Step 3: Execute the user insert query
        $stmtUser->execute();
        $userId = $pdo->lastInsertId(); // Get the ID of the newly inserted user

        // Step 4: Update the new-reports table with data from the users table
        $sqlUpdateReport = "UPDATE `new-reports` nr
                            SET nr.user_id = :user_id,
                                nr.sender_name = :sender_name,
                                nr.phone = :phone,
                                nr.user_address = :user_address
                            WHERE nr.phone = :original_phone";
        $stmtUpdateReport = $pdo->prepare($sqlUpdateReport);
        $stmtUpdateReport->bindParam(':user_id', $userId);
        $stmtUpdateReport->bindParam(':sender_name', $name);
        $stmtUpdateReport->bindParam(':phone', $contact);
        $stmtUpdateReport->bindParam(':user_address', $address);
        $stmtUpdateReport->bindParam(':original_phone', $phone);
        $stmtUpdateReport->execute();

        echo json_encode(['success' => true, 'message' => 'Report and user inserted successfully, new-reports updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}
?>
