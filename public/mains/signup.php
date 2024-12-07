<?php
session_start();
require('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $error_message = '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($address) || empty($contact)) {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate email format
        $error_message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) { // Check password match
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) { // Validate password length
        $error_message = "Password must be at least 8 characters long.";
    } else {
        // Attempt to insert the user into the database
        try {
            // Check for duplicate email
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $checkStmt->execute([':email' => $email]);
            $emailExists = $checkStmt->fetchColumn();

            if ($emailExists) {
                $error_message = "The email address is already registered.";
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, address, contact, status, created_at) 
                    VALUES (:name, :email, :password, :address, :contact, 'active', NOW())
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':address' => $address,
                    ':contact' => $contact
                ]);

                // Redirect to login page with email pre-filled
                $_SESSION['signup_email'] = $email;
                header("Location: login.php?signup_success=true");
                exit;
            }
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Database Error: " . $e->getMessage());

            // User-friendly error message
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    }

    // If an error occurred, store it in the session for display
    if (!empty($error_message)) {
        $_SESSION['error_message'] = $error_message;
        header("Location: signup.php");
        exit;
    }
}
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
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        <form action="signup.php" method="POST" onsubmit="return validateSignUpForm()">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="address" name="address" id="address" class="form-control" placeholder="Enter Address" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact Number</label>
                                <input type="contact" name="contact" id="contact" class="form-control" placeholder="Enter contact number" required>
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
