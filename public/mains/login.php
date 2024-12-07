<?php
require('../config/config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle login
    if (isset($_POST['login'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        if (!empty($email) && !empty($password)) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Successful login: Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];

                    // Redirect to home.php after login
                    header("Location: ../mains/home.php");
                    exit;
                } else {
                    $error_message = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = "Please fill in all required fields.";
        }
    }

    // Handle Guest Mode (Login Later)
    if (isset($_POST['guest'])) {
        // Set a guest session (user_id can be 0 or null to indicate a guest)
        $_SESSION['user_id'] = 0;  // Indicate guest user
        $_SESSION['user_name'] = 'Guest'; // Guest name

        // Redirect to home.php as a guest
        header("Location: ../mains/home.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Make the body fill the screen on mobile */
        body {
            background-color: #343a40;
            color: white;
        }
        .card {
            border-radius: 10px;
        }
        .alert-danger {
            text-align: center;
        }
        .card-body {
            padding: 2rem;
        }
        @media (max-width: 576px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-sm-8 col-12">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Login</h2>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                            <a href="guest_home.php" class="btn btn-secondary w-100 mt-3">Continue as Guest</a>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../mains/forgot_password.php" class="text-decoration-none">Forgot Password?</a><br>
                            <span>Don't have an account? <a href="../mains/signup.php" class="text-decoration-none">Sign Up</a></span><br>
                            <!-- Button for Guest Mode -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
