<?php
session_start();
// Database configuration
$host = 'localhost'; // Change if using a different server
$db = 'swiftbase'; // Updated database name
$user = 'root'; // Default username for XAMPP
$pass = ''; // Default password for XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the user is a guest (not logged in)
$isGuest = !isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/home.css">
    <title>SwiftAid - Emergency Guest Mode</title>
</head>
<body>
<!-- Notification Bar -->
<div id="notificationBar" aria-live="polite"></div>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-red bg-red">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="#">SwiftAid</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../mains/signup.php">Sign up now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <h2>Emergency Alert</h2>
    <p class="help-text">If you're in urgent need of help, press the button below to send your location and alert authorities!</p>
    <p class="help-text">You're currently in guest mode. This page will automatically redirect you to the sign-up form after 30 minutes.</p>
    <button class="send-help-btn" id="sendHelpBtn" onclick="getLocation()">Send Help</button>
</div>

<!-- Phone Number Modal -->
<div class="modal fade" id="phoneModal" tabindex="-1" aria-labelledby="phoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="phoneModalLabel">Enter Your Phone Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="phoneForm">
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber" name="phone" 
                               required minlength="10" maxlength="15" pattern="\d{10,15}" 
                               title="Enter a valid phone number (10-15 digits)">
                        <div class="form-text">Please enter your phone number so emergency services can contact you.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Severity Modal -->
<div class="modal fade" id="severityModal" tabindex="-1" aria-labelledby="severityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="severityModalLabel">Select Emergency Severity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column align-items-center">
                    <p class="text-muted mb-3 text-center" style="font-size: 1rem;">
                        <strong>Critical:</strong> <br> Indicates severe, life-threatening situations requiring immediate, high-priority response.<br>
                        <strong>Emergent:</strong> <br> Denotes serious situations that are urgent but not immediately life-threatening.
                    </p>
                    <button class="btn btn-danger btn-lg mb-2 severity-btn" onclick="submitSeverity('Critical')">Critical</button>
                    <button class="btn btn-warning btn-lg mb-2 severity-btn" onclick="submitSeverity('Emergent')">Emergent</button>
                </div>
            </div>
        </div>
    </div>
</div>

<footer>
        <p>&copy; 2024 SwiftAid. All Rights Reserved.</p>
</footer>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Request Sent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your emergency request has been sent successfully. Remain Calm!</p>
                <p>Please sign up to complete the process.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                Authorities are on their way!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="closeModalButton">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/guest.js"></script>
</body>
</html>
