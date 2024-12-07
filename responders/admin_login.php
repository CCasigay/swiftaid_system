<?php
session_start(); // Start session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    require '../responders/config/config.php'; // Include secure database connection

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID and store user info in session
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../responders/welcome.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        die("Error logging in. Please contact support.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* General Body and Background */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, rgba(106, 27, 154, 0.8), rgba(0, 0, 0, 0.7));
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Login Form Container */
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        /* Form Heading */
        h2 {
            font-size: 28px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Error Message */
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            background-color: #fafafa;
        }

        input:focus {
            border-color: #6a1b9a;
            box-shadow: 0 0 5px rgba(106, 27, 154, 0.3);
            outline: none;
        }

        /* Submit Button */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #6a1b9a;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #4a148c;
        }

        /* Links */
        p a {
            color: #6a1b9a;
            text-decoration: none;
            font-weight: bold;
        }

        p a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            h2 {
                font-size: 24px;
            }

            button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Admin Logo" class="logo">
        </div>

        <h2>Admin Login</h2>

        <!-- Display error message if login fails -->
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="admin_login.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <!-- Links for Signup and Forgot Password -->
        <p>Don't have an account? <a href="admin_signup.php">Sign up here</a></p>
        <p><a href="forgot_password.php">Forgot your password?</a></p>
    </div>
</body>
</html>
