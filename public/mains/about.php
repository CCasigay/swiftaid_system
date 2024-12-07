<?php
session_start();
require '../config/config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../mains/login.php"); // Redirect to the login page
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch user details for the logged-in user
    $stmt = $pdo->prepare("SELECT name, email, contact, address FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_details) {
        header("Location: ../mains/login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user details: " . $e->getMessage());
    die("An error occurred while fetching user details. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftAid - About</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/about.css"> <!-- Custom CSS -->

    <!-- Metadata -->
    <meta name="description" content="SwiftAid About Page">
    <meta name="author" content="SwiftAid Team">
</head>
<body>
    <!-- Notification Bar -->
    <div id="notificationBar" aria-live="polite"></div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-danger">
        <div class="container-fluid">
            <!-- Toggle Button -->
            <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Brand Name -->
            <a class="navbar-brand" href="#">SwiftAid</a>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../mains/home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href=" ../mains/notification.php">Notifications</a>
                    </li>
                </ul>
            </div>

            <!-- Profile Image -->
            <ul class="navbar-nav mobile-profile">
                <li class="nav-item">
                    <img src="../images/profile-pic.jpg" alt="Profile Picture" class="profile-pic" id="profilePic" data-bs-toggle="offcanvas" data-bs-target="#userDetailsPanel">
                </li>
            </ul>
        </div>
    </nav>

    <!-- Side Panel -->
    <div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="userDetailsPanel" aria-labelledby="userDetailsLabel">
        <div class="offcanvas-header" style="background: linear-gradient(45deg, #f04c92, #7a2ff7, #ff8f00);">
            <h5 id="userDetailsLabel" class="fw-bold text-dark">User Details</h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body bg-light d-flex flex-column">
            <div class="d-flex flex-column align-items-center mb-4">
                <div class="c-sign bg-dark text-white d-flex justify-content-center align-items-center rounded-circle mb-2" style="width: 50px; height: 50px; font-size: 1.5rem; font-weight: bold;">
                    <?php echo strtoupper(substr(htmlspecialchars(explode(" ", $user_details['name'])[0]), 0, 1)); ?>
                </div>
                <p class="mb-0 fw-semibold fs-5 text-dark"><?php echo htmlspecialchars($user_details['name']); ?></p>
                <p class="text-muted small"><?php echo htmlspecialchars($user_details['email']); ?></p>
            </div>
            <div class="mb-3">
                <p class="text-muted mb-1"><strong>Contact:</strong></p>
                <p class="text-dark"><?php echo htmlspecialchars($user_details['contact']); ?></p>
            </div>
            <div class="mb-3">
                <p class="text-muted mb-1"><strong>Address:</strong></p>
                <p class="text-dark"><?php echo htmlspecialchars($user_details['address']); ?></p>
            </div>
            <div class="mt-auto">
                <a class="btn btn-outline-primary w-100" href="../includes/logout.php">Log Out</a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container my-5">
        <table class="about-table">
            <tr>
                <td>
                    <h5>About SwiftAid</h5>
                    <p>SwiftAid is a platform designed to provide quick and efficient emergency response. With just one click, you can send an alert with your location, notifying the authorities to assist you swiftly.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <h5>Our Mission</h5>
                    <p>Our mission is to reduce emergency response times and improve the effectiveness of emergency services by leveraging real-time location tracking and immediate notification of relevant authorities.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <h5>How It Works</h5>
                    <p>SwiftAid uses the latest geolocation technology to pinpoint your exact location when you need assistance. The app immediately sends an alert to authorities and loved ones, including information about the severity of the emergency.</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 SwiftAid. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
