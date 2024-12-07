<?php
session_start();
require '../config/config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../mains/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT name, email, contact, address FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_details) {
        echo "<script>alert('User details not found. Please try again later.');</script>";
        header("Location: ../mains/login.php");
        exit();
    }    
} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

// Fetch unread notifications count
$unread_count = 0;
try {
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM `new-reports` WHERE user_id = :user_id AND read_status = 0 AND status IN ('Pending'))
            +
            (SELECT COUNT(*) FROM `recorded-reports` WHERE user_id = :user_id AND read_status = 0 AND status IN ('Accepted'))
            AS unread_count
    ");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread_count = $result['unread_count'] ?? 0;
} catch (PDOException $e) {
    die("Error fetching unread notifications: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/home.css">
    <title>SwiftAid - Emergency</title>
</head>
<body>
<!-- Notification Bar -->
<div id="notificationBar" aria-live="polite"></div>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-red bg-red">
    <div class="container-fluid">
        <!-- Navbar Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand Name -->
        <a class="navbar-brand" href="#">SwiftAid</a>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../mains/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href=" ../mains/notification.php">
                        Notifications
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Profile Image Outside Dropdown for Mobile Visibility -->
        <ul class="navbar-nav mobile-profile">
            <li class="nav-item">
                <img src="../images/profile-pic.jpg" alt="Profile" class="profile-pic" id="profilePic">
            </li>
        </ul>
    </div>
</nav>
<!-- Side Panel -->
<div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="userDetailsPanel" aria-labelledby="userDetailsLabel">
    <div class="offcanvas-header" style="background: linear-gradient(45deg, #f04c92, #7a2ff7, #ff8f00);">
        <h5 id="userDetailsLabel" class="fw-bold text-dark">User Details</h5>
        <button type="button" class="btn-close btn-close-black bg-dark" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body bg-light d-flex flex-column">
        <!-- User Profile Section -->
        <div class="d-flex flex-column align-items-center mb-4">
            <!-- C Sign (Dynamic) -->
            <div class="c-sign bg-dark text-white d-flex justify-content-center align-items-center rounded-circle mb-2"
                 style="width: 50px; height: 50px; font-size: 1.5rem; font-weight: bold;">
                <?php 
                    // Assuming the user's name is stored as "FirstName LastName"
                    $first_name = explode(" ", htmlspecialchars($user_details['name']))[0]; 
                    echo strtoupper(substr($first_name, 0, 1)); 
                ?>
            </div>
            <!-- Name -->
            <p class="mb-0 fw-semibold fs-5 text-dark"><?php echo htmlspecialchars($user_details['name']); ?></p>
            <!-- Email -->
            <p class="text-muted small"><?php echo htmlspecialchars($user_details['email']); ?></p>
        </div>
        <!-- Contact Section -->
        <div class="mb-3">
            <p class="text-muted mb-1"><strong>Contact:</strong></p>
            <p class="text-dark"><?php echo htmlspecialchars($user_details['contact']); ?></p>
        </div>
        <!-- Address Section -->
        <div class="mb-3">
            <p class="text-muted mb-1"><strong>Address:</strong></p>
            <p class="text-dark"><?php echo htmlspecialchars($user_details['address']); ?></p>
        </div>

        <!-- Spacer to push settings and logout to bottom -->
        <div class="mt-auto">
            <div class="d-flex flex-column align-items-center">
                <a class="dropdown-item" href="#"><i class="fas fa-cog fa-fw"></i> Settings</a>
                <a class="dropdown-item" href="../includes/logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Log Out</a>
            </div>
        </div>
    </div>
</div>

    <!-- Main Container -->
    <div class="container">
        <h2>Emergency Alert</h2>
        <p class="help-text">If you're in urgent need of help, press the button below to send your location and alert authorities!</p>
        <button class="send-help-btn" id="sendHelpBtn">Send Help</button>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 SwiftAid. All Rights Reserved.</p>
    </footer>
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
                            <strong>Emergent:</strong> <br>Denotes serious situations that are urgent but not immediately life-threatening.
                        </p>
                        <button class="btn btn-danger btn-lg mb-2 severity-btn" onclick="submitSeverity('Critical')">Critical</button>
                        <button class="btn btn-warning btn-lg mb-2 severity-btn" onclick="submitSeverity('Emergent')">Emergent</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this modal for confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Request Sent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your emergency request has been sent successfully. Remain Calm!</p>
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
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Variables for User Location
    let userLatitude, userLongitude;

    // Function: Show Notification Bar
    function showNotification(message, success = true) {
        const notificationBar = document.getElementById("notificationBar");

        if (!notificationBar) {
            console.error("Notification bar element not found!");
            return;
        }

        notificationBar.style.backgroundColor = success ? "#28a745" : "#dc3545";
        notificationBar.style.color = "white";
        notificationBar.textContent = message;
        notificationBar.style.display = "block";
        notificationBar.style.position = "fixed";
        notificationBar.style.top = "0";
        notificationBar.style.width = "100%";
        notificationBar.style.zIndex = "1050";

        setTimeout(() => {
            notificationBar.style.display = "none";
        }, 3000);
    }

    // Function: Get User Location
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;
                    const severityModal = new bootstrap.Modal(document.getElementById("severityModal"));
                    severityModal.show();
                },
                (error) => showNotification("Error getting location: " + error.message, false)
            );
        } else {
            showNotification("Geolocation is not supported by this browser.", false);
        }
    }

    // Function: Submit Severity Data
    function submitSeverity(severity) {
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

        // Hide the severity modal
        const severityModal = bootstrap.Modal.getInstance(document.getElementById("severityModal"));
        severityModal.hide();

        fetch("../includes/sendHelp.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                userId: userId,
                latitude: userLatitude,
                longitude: userLongitude,
                severity: severity,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const confirmationModal = new bootstrap.Modal(document.getElementById("confirmationModal"));
                    confirmationModal.show();
                } else {
                    showNotification("Failed to submit help request: " + data.message, false);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                showNotification("An error occurred while sending the request.", false);
            });
    }

    // Function: Check for Report Updates
    function checkForReportUpdates() {
        fetch("../includes/checkReportUpdates.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const notificationModal = new bootstrap.Modal(document.getElementById("notificationModal"));
                    document.getElementById("notificationModalBody").textContent = data.message;
                    notificationModal.show();
                }
            })
            .catch((error) => console.error("Error checking for report updates:", error));
    }

    // Function: Fetch Notifications
    function fetchNotifications() {
        fetch("../includes/fetchNotifications.php")
            .then((response) => response.json())
            .then((data) => {
                const notificationLink = document.querySelector("a[href='../mains/notification.php']");
                if (data.unread_count > 0) {
                    let badge = notificationLink.querySelector(".notification-badge");
                    if (!badge) {
                        badge = document.createElement("span");
                        badge.className = "notification-badge";
                        notificationLink.appendChild(badge);
                    }
                    badge.textContent = data.unread_count;
                } else {
                    const badge = notificationLink.querySelector(".notification-badge");
                    if (badge) {
                        badge.remove();
                    }
                }
            })
            .catch((error) => console.error("Error fetching notifications:", error));
    }

    // Event Listeners
    document.querySelector("#sendHelpBtn").addEventListener("click", getLocation);
    document.getElementById("profilePic").addEventListener("click", () => {
        const userDetailsPanel = new bootstrap.Offcanvas(document.getElementById("userDetailsPanel"));
        userDetailsPanel.show();
    });

    // Interval Calls
    setInterval(checkForReportUpdates, 30000); // Check report updates every 30 seconds
    setInterval(fetchNotifications, 30000); // Fetch notifications every 30 seconds
</script>
</body>
</html>
