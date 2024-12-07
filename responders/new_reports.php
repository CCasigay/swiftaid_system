<?php
// Start the session and include necessary files
session_start(); // Start session
require '../responders/config/config.php'; // Secure database connection

// Regenerate session ID for security
session_regenerate_id(true);

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'], $_SESSION['username'])) {
    header("Location: ../responders/admin_login.php");
    exit();
}

// Sanitize and assign the admin ID from the session
$admin_id = htmlspecialchars($_SESSION['admin_id'], ENT_QUOTES, 'UTF-8');

try {
    // Fetch admin details securely using prepared statements
    $stmt = $pdo->prepare("SELECT full_name, username, email, phone_number FROM admins WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $admin_id]);
    $admin_details = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no admin details are found, destroy the session and redirect to login
    if (!$admin_details) {
        session_destroy();
        header("Location: ../responders/admin_login.php");
        exit();
    }

    // Additional verification for enhanced security
    if ($admin_details['username'] !== $_SESSION['username']) {
        session_destroy();
        header("Location: ../responders/admin_login.php");
        exit();
    }

    // Assign sanitized admin name for display in the UI (e.g., in a dropdown)
    $adminName = htmlspecialchars($admin_details['full_name'], ENT_QUOTES, 'UTF-8');

    // Fetch new reports
    $sql = "SELECT * FROM `new-reports` WHERE status = 'Pending'"; // Adjust the query to match your table structure
    $result = $pdo->query($sql); // Using the same $pdo connection for consistency

    $reports = [];

    if ($result->rowCount() > 0) {
        // Fetch all the rows
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $reports[] = $row;
        }
    }

} catch (PDOException $e) {
    // Log database errors securely
    error_log("Database error: " . $e->getMessage());
    die("Error fetching admin details or reports. Please contact support.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftAid Emergency Response Center</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../responders/css/new_reports.css">
    <link href=" ../responders/css/dsboard.css" rel="stylesheet">
</head>

<body>
 <!-- Navigation -->
 <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../responders/assets/images/logo.png" alt="SwiftAid Logo" class="logo-img me-2">
            SwiftAid
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="../responders/new_reports.php">Urgent Alerts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../responders/recorded_reports.php">Incident Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../responders/dsboard.php">Dashboard</a>
                </li>
                

                <!-- Profile Icon Outside Dropdown for Mobile -->
                <ul class="navbar-nav mobile-profile">
                    <li class="nav-item">
                        <img src=" ../responders/assets/images/profile-pic.jpg" alt="Profile" class="profile-pic" id="profilePic" data-bs-toggle="offcanvas" data-bs-target="#userDetailsPanel" aria-controls="userDetailsPanel">
                    </li>
                </ul>
            </ul>
        </div>
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
                    // Assuming the admin's name is stored as "FirstName LastName"
                    $first_name = explode(" ", htmlspecialchars($admin_details['full_name']))[0]; 
                    echo strtoupper(substr($first_name, 0, 1)); 
                ?>
            </div>
            <!-- Name -->
            <p class="mb-0 fw-semibold fs-5 text-dark"><?php echo htmlspecialchars($admin_details['full_name']); ?></p>
            <!-- Email -->
            <p class="text-muted small"><?php echo htmlspecialchars($admin_details['email']); ?></p>
        </div>
        <!-- Spacer to push settings and logout to bottom -->
        <div class="mt-auto">
        <div class="d-flex flex-column align-items-center">
            <!-- Settings Link -->
            <a class="dropdown-item d-flex align-items-center justify-content-center p-2 mb-2" href="#">
                <i class="fas fa-cog fa-fw me-2 text-secondary"></i>
                <span class="text-dark">Settings</span>
            </a>
            <!-- Logout Link -->
            <a class="dropdown-item d-flex align-items-center justify-content-center p-2" href="../responders/logout.php">
                <i class="fas fa-sign-out-alt fa-fw me-2 text-danger"></i>
                <span class="text-dark">Log Out</span>
            </a>
        </div>
    </div>
</div>


         </div>
    </div>
</div>

<main>
    <section id="reports" class="container-custom">
        <h2>New Reports</h2>
        <?php
        // Fetch reports, assuming $reports is an array with all the reports
        if (!empty($reports)) {
            foreach ($reports as $report) {
                // Format the date
                $date = new DateTime($report['date']);
                $formattedDate = $date->format('F d, Y');

                // Fetch the phone number directly from the report data
                $phone = $report['phone']; // Assuming the phone number is directly available in the report

                // Determine notification class based on severity
                $alertClass = '';
                if (strtolower($report['severity']) === 'critical') {
                    $alertClass = 'alert-danger'; // Red for critical
                } else if (strtolower($report['severity']) === 'emergent') {
                    $alertClass = 'alert-warning'; // Yellow for emergent
                }

                // Display the report
                echo '
                <div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                    <strong>' . $report['severity'] . ' Report:</strong><br>
                    A report (ID: ' . $report['report_id'] . ') from <strong>' . $report['sender_name'] . '</strong> (User ID: ' . $report['user_id'] . ') has been submitted with a severity level of <strong>' . $report['severity'] . '</strong>. 
                    The location is at Latitude: ' . $report['latitude'] . ', Longitude: ' . $report['longitude'] . ' on <strong>' . $formattedDate . ' at ' . $report['time'] . '</strong>.
                    <div class="mt-2">
                        <strong>Contact:</strong> 
                        <span id="phone-number-' . $report['report_id'] . '" class="phone-number">' . $report['phone'] . '</span>
                    </div>
                    <div class="action-container mt-3">
                        <button class="accept-btn" onclick="acceptReport(' . $report['report_id'] . ', \'' . $report['latitude'] . '\', \'' . $report['longitude'] . '\')">Accept</button>
                        <button class="copy-btn" onclick="copyPhoneNumber(\'phone-number-' . $report['report_id'] . '\')">Copy Phone Number</button>
                    </div>
                </div>';
            }
        } else {
            echo "<p>No new reports available.</p>";
        }
        ?>
    </section>
</main>

    <footer>
        <p>&copy; 2024 SwiftAid</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../responders/assets/js/new_reports.js"></script>
    <script>
        function copyPhoneNumber(elementId) {
            const phoneNumberElement = document.getElementById(elementId);

            if (phoneNumberElement) {
                const phoneNumber = phoneNumberElement.textContent || phoneNumberElement.innerText;

                // Use the Clipboard API to write the phone number to the clipboard
                navigator.clipboard.writeText(phoneNumber)
                    .then(() => {
                        alert("Phone number copied: " + phoneNumber);
                    })
                    .catch((err) => {
                        console.error('Failed to copy: ', err);
                        alert("Failed to copy phone number.");
                    });
            } else {
                alert("Phone number element not found.");
            }
        }

        function acceptReport(reportId, latitude, longitude) {
            fetch(`../responders/api/update_report.php?id=${reportId}&status=Accepted`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Report ID ${reportId} accepted.`);

                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(position => {
                                const currentLat = position.coords.latitude;
                                const currentLng = position.coords.longitude;

                                if (latitude && longitude && currentLat && currentLng) {
                                    const directionsUrl = `https://www.google.com/maps/dir/?api=1&origin=${currentLat},${currentLng}&destination=${latitude},${longitude}&travelmode=driving`;

                            // Open the Google Maps Directions in the browser
                                    window.location.href = directionsUrl; // This will ensure the page navigates to Google Maps
                                } else {
                                    alert("Invalid coordinates. Unable to open Google Maps.");
                                }
                            }, (error) => {
                                alert("Unable to retrieve your location. Please allow location access.");
                            });
                        } else {
                            alert("Geolocation is not supported by this browser.");
                        }
                    } else {
                        alert('Failed to accept the report.');
                    }
                })
                .catch(error => {
                    console.error('Error accepting report:', error);
                    alert('An error occurred while accepting the report.');
                });
        }
    </script>
</body>

</html>
