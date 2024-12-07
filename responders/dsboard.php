<?php
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

} catch (PDOException $e) {
    // Log database errors securely
    error_log("Database error: " . $e->getMessage());
    die("Error fetching admin details. Please contact support.");
}

$admin_details['full_name'] = $admin_details['full_name'] ?? 'Admin';
$admin_details['email'] = $admin_details['email'] ?? 'Not Available';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftAid Emergency Response Center</title>

    <!-- CSS and Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;500;700&display=swap" rel="stylesheet">
    <link href=" ../responders/css/dsboard.css" rel="stylesheet">
    
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
                    <a class="nav-link active" href="#">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../responders/recorded_reports.php">Incident Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../responders/new_reports.php">Urgent Alerts</a>
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
<main class="container">
    <div class="row justify-content-center text-center">
        <div class="col-md-5 mb-4">
            <div class="stat-card stat-card1">
                <h3>All Reports Recorded</h3>
                <p id="totalReports">0</p>
                <button class="btn btn-primary" onclick="window.location.href='../responders/recorded_reports.php'">View All Reports</button>
            </div>
        </div>
        <div class="col-md-5 mb-4">
            <div class="stat-card stat-card2">
                <h3>New Reports</h3>
                <p id="activeNotifications">0</p>
                <button class="btn btn-primary" onclick="window.location.href='../responders/new_reports.php'">View Detailed Reports</button>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-10">
            <div class="graph-container">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
</main>


    <!-- Modal for New Reports -->
    <div class="modal fade" id="newReportModal" tabindex="-1" aria-labelledby="newReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newReportModalLabel">🚨 New Urgent Report!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>A new urgent report has been received. Please check the "New Reports" section for more details.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='../responders/new_reports.php'" data-bs-dismiss="modal">Go to Reports</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 SwiftAid. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Fetch graph data and render chart
    fetch('../responders/api/graph.php')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.date);
            const reportCounts = data.map(item => item.count);

            const chartData = {
                labels: labels,
                datasets: [{
                    label: 'Accidents Reported',
                    data: reportCounts,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            };

            new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    // Alarm setup: Load the emergency alarm sound
    const emergencySound = new Audio('../responders/assets/sounds/alarm.mp3');

    // Function to check for new reports
    function checkForNewReports() {
        fetch('../responders/api/check_new_reports.php') // API endpoint to check for new reports
            .then(response => response.json()) // Parse the JSON response
            .then(data => {
                // Check if there are new reports
                if (data.new_reports) {
                    emergencySound.play(); // Play the emergency alarm sound
                    var myModal = new bootstrap.Modal(document.getElementById('newReportModal'));
                    myModal.show(); // Show the modal
                }
            })
            .catch(error => console.error('Error checking new reports:', error)); // Handle any errors in fetching the data
    }

    // Check for new reports every 10 seconds
    setInterval(checkForNewReports, 10000);

    // Fetch dashboard stats and update the UI
    async function fetchDashboardStats() {
        try {
            const response = await fetch('../responders/api/fetch_dashboard_stats.php');
            if (!response.ok) throw new Error("Network response was not ok");

            const data = await response.json();

            document.getElementById('totalReports').textContent = data.recorded_reports;
            document.getElementById('activeNotifications').textContent = data.new_reports;

            // If there are new reports, refresh the page every minute to stay updated
            if (data.new_reports > 0) {
                setTimeout(() => {
                    window.location.reload();  // Reload the page to update stats
                }, 60000);  // Refresh after 1 minute (60000 milliseconds)
            }
        } catch (error) {
            console.error("Error fetching dashboard stats:", error);
        }
    }

    // Call the function to fetch stats when the page loads
    fetchDashboardStats();

    // Set an interval to check for updates every 30 seconds
    setInterval(fetchDashboardStats, 20000);  // Poll the server every 30 seconds
</script>
</body>

</html>
