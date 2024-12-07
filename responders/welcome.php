<?php
session_start(); // Start session
require '../responders/config/config.php'; // Include secure database connection

// Regenerate session ID for security
session_regenerate_id(true);

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../responders/admin_login.php");
    exit();
}

// Sanitize and assign the admin ID from the session
$admin_id = htmlspecialchars($_SESSION['admin_id'], ENT_QUOTES, 'UTF-8');

try {
    // Fetch admin details securely
    $stmt = $pdo->prepare("SELECT full_name, username, email, phone_number FROM admins WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $admin_id]);
    $admin_details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin_details) {
        session_destroy();
        header("Location: ../responders/admin_login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error fetching admin details. Please contact support.");
}

// Additional verification for enhanced security
if ($admin_details['username'] !== $_SESSION['username']) {
    session_destroy();
    header("Location: ../responders/admin_login.php");
    exit();
}

$full_name = htmlspecialchars($admin_details['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SwiftAid</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f8fa;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .welcome-container {
            margin-top: 10%;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #0366d6;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #586069;
        }
    </style>
</head>
<body>
    <div class="container welcome-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h1>Welcome to SwiftAid</h1>
                        <p class="mb-0">Emergency Response Center</p>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">Hello, <?php echo $full_name; ?>!</h4>
                        <p class="card-text">
                            SwiftAid is designed to make emergency response fast and efficient.
                        </p>
                        <a href="../responders/dsboard.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                    <div class="card-footer text-muted">
                        Thank you for being a part of SwiftAid.
                    </div>
                </div>
                <div class="footer text-center">
                    <p>SwiftAid Emergency Response System &copy; <?php echo date("Y"); ?></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
