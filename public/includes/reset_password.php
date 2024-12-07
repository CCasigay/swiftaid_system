<?php
// Include config file to establish DB connection
include_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_GET['token'];
    $new_password = $_POST['new_password'];
    
    // Validate token and new password
    if (!empty($token) && strlen($new_password) >= 6) {
        // Check if the token is valid and not expired
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Reset password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);
            $stmt->execute();

            echo "Your password has been successfully reset.";
        } else {
            echo "Invalid or expired reset token.";
        }
    } else {
        echo "Invalid input.";
    }
}
?>

<!-- HTML form for entering new password -->
<form method="post" action="reset_password.php?token=<?php echo $_GET['token']; ?>">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" required>
    <button type="submit">Reset Password</button>
</form>
