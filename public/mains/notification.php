<?php
session_start();
require '../config/config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../mains/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize reports and user_details to prevent null issues
$reports = [];
$user_details = [];

try {
    // Fetch reports for the logged-in user
    $query = "
        SELECT 
            date, time, severity, status, read_status, sender_name, NULL AS response_at
        FROM `new-reports`
        WHERE user_id = :user_id
        UNION ALL
        SELECT 
            date, time, severity, status, NULL AS read_status, sender_name, response_at
        FROM `recorded-reports`
        WHERE user_id = :user_id
        ORDER BY date DESC, time DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching reports: " . $e->getMessage());
    die("An error occurred while fetching your reports. Please try again later.");
}

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
    <title>SwiftAid - Notifications</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/notif.css"> <!-- Custom CSS -->

    <!-- Metadata -->
    <meta name="description" content="SwiftAid Notification Center">
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
                        <a class="nav-link" href="../mains/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Notifications</a>
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

    <!-- Main Content -->
    <div class="container mt-5">
        <button class="btn btn-success mb-3" id="markAsReadBtn">Mark All as Read</button>
        <?php if (!empty($reports)): ?>
            <?php foreach ($reports as $report): ?>
                <div class="notification <?php echo isset($report['read_status']) && $report['read_status'] == 0 ? 'unread' : 'read'; ?>" 
                    style="font-weight: <?php echo isset($report['read_status']) && $report['read_status'] == 0 ? 'bold' : 'normal'; ?>;">
                    <?php 
                        $status = htmlspecialchars($report['status']);
                        $sender_name = htmlspecialchars($report['sender_name']);
                        $date = htmlspecialchars($report['date']);
                        $time = htmlspecialchars($report['time']);
                        $response_at = htmlspecialchars($report['response_at'] ?? 'No response yet');

                        $formatted_date = date('F j, Y', strtotime($date));
                        $formatted_time = date('g:i A', strtotime($time));

                        $message = match ($status) {
                            'Pending' => "Hi, $sender_name. Your report is under review. Submitted on $formatted_date at $formatted_time.",
                            'Accepted' => "Hi, $sender_name. Your report has been accepted. Help is on its way. Responded on $response_at.",
                            default => "Update: Your report has been processed. Response: $response_at.",
                        };
                    ?>
                    <p><?php echo $message; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">You have no notifications at the moment.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer mt-5 text-center">
        <div class="container">
            <p>&copy; 2024 SwiftAid. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    fetchNotifications();
    setInterval(fetchNotifications, 30000); // Refresh notifications every 30 seconds
});
</script>
</body>
</html>
