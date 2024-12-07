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
    <style>
        body {
            font-family: 'Merriweather', sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, #007BFF, #00C6FF);
        }

        .nav-link {
            padding: 0.5rem 1rem;
            transition: color 0.3s, background-color 0.3s;
            border-radius: 8px;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffc107;
        }

        .nav-link.active {
            color: #ffc107;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.1);
        }

        footer {
            background: linear-gradient(90deg, #007BFF, #00C6FF);
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .logo-img {
            border: 2px solid white;
            border-radius: 50px;
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .table thead th {
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
            padding: 0.75rem;
        }

        .btn-sm {
            font-size: 0.875rem;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .table-striped tbody tr:nth-of-type(even) {
            background-color: #ffffff;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../responders/assets/images/logo.png" alt="SwiftAid Logo" class="logo-img me-2">
                SwiftAid
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="../responders/recorded_reports.php">Incident Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="../responders/new_reports.php">Urgent Alerts</a></li>
                    <li class="nav-item"><a class="nav-link" href="../responders/dsboard.php">Dashboard</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-primary">All Reports</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Report ID</th>
                        <th>User ID</th>
                        <th>Sender Name</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Severity</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Responded Date & Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="allReportsTableBody"></tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 SwiftAid</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function fetchAllReports() {
            try {
                const response = await fetch('../responders/api/fetch_recorded-reports.php');
                const reports = await response.json();
                const allReportsTableBody = document.getElementById('allReportsTableBody');
                allReportsTableBody.innerHTML = "";

                reports.forEach(report => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td>${report.id}</td>
                        <td>${report.user_id}</td>
                        <td>${report.sender_name}</td>
                        <td>${parseFloat(report.latitude).toFixed(6)}</td>
                        <td>${parseFloat(report.longitude).toFixed(6)}</td>
                        <td>${report.severity}</td>
                        <td>${report.date}</td>
                        <td>${report.time}</td>
                        <td>${report.status}</td>
                        <td>${report.response_at || 'N/A'}</td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="getDirections(${report.latitude}, ${report.longitude})">
                                Get Directions
                            </button>
                        </td>
                    `;
                    allReportsTableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Error fetching reports:', error);
            }
        }

        function getDirections(lat, lng) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    const directionsUrl = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${lat},${lng}&travelmode=driving`;
                    window.open(directionsUrl, '_blank');
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }

        fetchAllReports();
    </script>
</body>

</html>
