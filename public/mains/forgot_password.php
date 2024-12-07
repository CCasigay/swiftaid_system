<?php
// Include config file to establish DB connection
include_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Validate the email address
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the email exists in the database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generate a password reset link (for example, using a token)
            $reset_token = bin2hex(random_bytes(32));  // Token generation
            // Save the token in the database with an expiry time
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
            $stmt->bind_param("ss", $reset_token, $email);
            $stmt->execute();

            // Send the reset email with the link
            $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;
            mail($email, "Password Reset Request", "Click the following link to reset your password: " . $reset_link);

            echo "A password reset link has been sent to your email address.";
        } else {
            echo "No user found with that email address.";
        }
    } else {
        echo "Invalid email format.";
    }
}
?>

<!-- HTML form for entering email -->
<form method="post" action="forgot_password.php">
    <label for="email">Enter your email:</label>
    <input type="email" name="email" required>
    <button type="submit">Send Password Reset Link</button>
</form>
