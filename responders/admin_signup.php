<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-Up</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-container" style="text-align: center; margin-bottom: 20px;">
            <img src="assets/images.png" alt="Admin Logo" class="logo" style="width: 120px; height: auto;"> <!-- Adjust the path and size -->
        </div>

        <h2>Admin Sign-Up</h2>
        <?php if (!empty($error)) : ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="admin_signup.php" method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- Personal Information -->
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" required autocomplete="off">
            </div>

            <!-- Account Credentials -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off">
            </div>

            <!-- Role and Permissions -->
            <div class="form-group">
                <label for="role">Role/Position</label>
                <select id="role" name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Manager">Manager</option>
                    <option value="Supervisor">Supervisor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="employee_id">Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" required autocomplete="off">
            </div>

            <!-- Security Question -->
            <div class="form-group">
                <label for="security_question">Security Question</label>
                <select id="security_question" name="security_question" required>
                    <option value="Your first pet's name?">Your first pet's name?</option>
                    <option value="Your mother's maiden name?">Your mother's maiden name?</option>
                    <option value="The name of your first school?">The name of your first school?</option>
                </select>
            </div>
            <div class="form-group">
                <label for="security_answer">Security Answer</label>
                <input type="text" id="security_answer" name="security_answer" required autocomplete="off">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn">Sign Up</button>
        </form>
        <p>Already have an account? <a href="admin_login.php">Login here</a></p>
    </div>
</body>
</html>
