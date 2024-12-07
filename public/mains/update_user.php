<?php
session_start();
require('../config/config.php');

// Check if the guest_session_id is available from the session or request
$guest_session_id = isset($_SESSION['guest_session_id']) ? $_SESSION['guest_session_id'] : null;
$error_message = '';

// If guest_session_id exists, attempt to fetch and update user data
if ($guest_session_id) {
    try {
        // Fetch the user data where name is "Unknown" and contact matches phone in new_reports
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.email, u.address, u.contact, nr.phone
            FROM users u
            JOIN new_reports nr ON nr.phone = u.contact
            WHERE u.name = 'Unknown' AND nr.guest_session_id = :guest_session_id
        ");
        $stmt->execute([':guest_session_id' => $guest_session_id]);

        // If a match is found, populate the form with the fetched data
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            // Update the user's data if needed (this assumes the user data should be updated if it's "Unknown")
            if ($userData['name'] === 'Unknown') {
                // Prepare the update statement
                $updateStmt = $pdo->prepare("
                    UPDATE users
                    SET name = :name, email = :email, address = :address, contact = :contact
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':name' => 'Known User',  // You can set the name to a known value instead of "Unknown"
                    ':email' => $userData['email'],
                    ':address' => $userData['address'],
                    ':contact' => $userData['contact'],
                    ':id' => $userData['id']
                ]);
            }

            // Store the user data in session or use it directly in the form
            $_SESSION['user_data'] = $userData;
        } else {
            $error_message = "No matching data found for this session.";
        }
    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Database Error: " . $e->getMessage());
        $error_message = "An unexpected error occurred. Please try again later.";
    }
} else {
    $error_message = "Session data is missing. Please ensure you're logged in.";
}

// Now, in the form HTML section, use the session data to populate the form if available

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Sign Up</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Sign Up</h3>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <form action="signup.php" method="POST" onsubmit="return validateSignUpForm()">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" 
                                    value="<?php echo isset($_SESSION['user_data']['name']) ? htmlspecialchars($_SESSION['user_data']['name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" 
                                    value="<?php echo isset($_SESSION['user_data']['email']) ? htmlspecialchars($_SESSION['user_data']['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" id="address" class="form-control" placeholder="Enter Address" 
                                    value="<?php echo isset($_SESSION['user_data']['address']) ? htmlspecialchars($_SESSION['user_data']['address']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="text" name="contact" id="contact" class="form-control" placeholder="Enter contact number" 
                                    value="<?php echo isset($_SESSION['user_data']['contact']) ? htmlspecialchars($_SESSION['user_data']['contact']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Create Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Re-enter Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter your password" required minlength="8">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign Up</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login Here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateSignUpForm() {
        const name = document.querySelector('input[name="name"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const address = document.querySelector('input[name="address"]').value.trim();
        const contact = document.querySelector('input[name="contact"]').value.trim();
        const password = document.querySelector('input[name="password"]').value.trim();
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value.trim();

        // Check if all fields are filled out
        if (!name || !email || !address || !contact || !password || !confirmPassword) {
            alert("Please fill out all fields.");
            return false;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        // Validate contact number format (optional - customize as needed)
        const contactRegex = /^\d{10,15}$/; // Ensures 10-15 numeric digits
        if (!contactRegex.test(contact)) {
            alert("Please enter a valid contact number (10-15 numeric digits).");
            return false;
        }

        // Validate password length
        if (password.length < 8) {
            alert("Password must be at least 8 characters long.");
            return false;
        }

        // Check if passwords match
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }

        return true;
    }
    </script>
</body>
</html>
