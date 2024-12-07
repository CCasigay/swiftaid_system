<?php
// Database Configuration
$host = "localhost";
$dbname = "swiftbase";
$username = "root";
$password = "";

// Start the session
session_start();

// Connect to the Database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $full_name = sanitizeInput($_POST["full_name"]);
    $username = sanitizeInput($_POST["username"]);
    $email = filter_var(sanitizeInput($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $phone_number = sanitizeInput($_POST["phone_number"]);
    $password = sanitizeInput($_POST["password"]);
    $confirm_password = sanitizeInput($_POST["confirm_password"]);
    $admin_role = sanitizeInput($_POST["admin_role"]);
    $organization_name = sanitizeInput($_POST["organization_name"]);
    $department_position = sanitizeInput($_POST["department_position"]);
    $security_question = sanitizeInput($_POST["security_question"]);
    $security_answer = sanitizeInput($_POST["security_answer"]);
    $terms = isset($_POST["terms"]) ? true : false;

    // Check if required fields are filled
    if (!$email || empty($password) || empty($confirm_password)) {
        die("Required fields are missing or invalid!");
    }

    // Check password match
    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Check if terms are accepted
    if (!$terms) {
        die("You must accept the terms and conditions!");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Insert data into the database
        $stmt = $conn->prepare("
            INSERT INTO admin_users (full_name, username, email, phone_number, password, admin_role, 
                               organization_name, department_position, security_question, security_answer) 
            VALUES (:full_name, :username, :email, :phone_number, :password, :admin_role, 
                    :organization_name, :department_position, :security_question, :security_answer)
        ");
        $stmt->execute([
            ':full_name' => $full_name,
            ':username' => $username,
            ':email' => $email,
            ':phone_number' => $phone_number,
            ':password' => $hashed_password,
            ':admin_role' => $admin_role,
            ':organization_name' => $organization_name,
            ':department_position' => $department_position,
            ':security_question' => $security_question,
            ':security_answer' => $security_answer,
        ]);

        // Store user info in the session
        $_SESSION["user_id"] = $conn->lastInsertId(); // Get the last inserted ID
        $_SESSION["full_name"] = $full_name;

        header("Location: ../responders/log_in.html");
        exit();
    } catch (PDOException $e) {
        // Handle duplicate entries (e.g., username or email already exists)
        if ($e->errorInfo[1] == 1062) {
            die("Duplicate entry detected: " . $e->getMessage());
        } else {
            die("Error occurred: " . $e->getMessage());
        }
    }
} else {
    die("Invalid request method!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-center bg-primary text-white py-4">
                        <h2 class="mb-0">Sign up</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="../responders/api/process_signup.php" method="POST">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="John Doe" required>
                            </div>
                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Choose a unique username" required>
                            </div>
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="example@domain.com" required>
                            </div>
                            <!-- Phone Number -->
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="+639XXXXXXXXX">
                            </div>
                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter a strong password" required>
                                <small class="text-muted">Must be at least 8 characters long.</small>
                            </div>
                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                            </div>
                            <!-- Admin Role -->
                            <div class="mb-3">
                                <label for="admin_role" class="form-label">Admin Role</label>
                                <select class="form-select" id="admin_role" name="admin_role">
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="Editor">Editor</option>
                                    <option value="Viewer" selected>Viewer</option>
                                </select>
                            </div>
                            <!-- Organization Name -->
                            <div class="mb-3">
                                <label for="organization_name" class="form-label">Organization Name</label>
                                <input type="text" class="form-control" id="organization_name" name="organization_name" placeholder="Enter your organization name">
                            </div>
                            <!-- Department/Position -->
                            <div class="mb-3">
                                <label for="department_position" class="form-label">Department/Position</label>
                                <select class="form-select" id="department_position" name="department_position">
                                    <option value="" disabled selected>Select Department/Position</option>
                                    <!-- Leadership Positions -->
                                    <option value="Chief, PNP">Chief, PNP</option>
                                    <option value="Deputy Chief for Administration">Deputy Chief for Administration</option>
                                    <option value="Deputy Chief for Operations">Deputy Chief for Operations</option>
                                    <option value="Chief of the Directorial Staff">Chief of the Directorial Staff</option>
                                    
                                    <!-- Directorial Staff -->
                                    <option value="Directorate for Personnel and Records Management">Directorate for Personnel and Records Management</option>
                                    <option value="Directorate for Intelligence">Directorate for Intelligence</option>
                                    <option value="Directorate for Operations">Directorate for Operations</option>
                                    <option value="Directorate for Investigation and Detective Management">Directorate for Investigation and Detective Management</option>
                                    <option value="Directorate for Logistics">Directorate for Logistics</option>
                                    <option value="Directorate for Comptrollership">Directorate for Comptrollership</option>
                                    <option value="Directorate for Plans">Directorate for Plans</option>
                                    <option value="Directorate for ICT Management">Directorate for ICT Management</option>
                                    <option value="Directorate for Police Community Relations">Directorate for Police Community Relations</option>
                                    <option value="Directorate for Human Resource and Doctrine Development">Directorate for Human Resource and Doctrine Development</option>
                                    <option value="Directorate for Research and Development">Directorate for Research and Development</option>
                                    <option value="Directorate for Integrated Police Operations">Directorate for Integrated Police Operations</option>
                                    
                                    <!-- Specialized Units -->
                                    <option value="Special Action Force">Special Action Force</option>
                                    <option value="Criminal Investigation and Detection Group">Criminal Investigation and Detection Group</option>
                                    <option value="Anti-Cybercrime Group">Anti-Cybercrime Group</option>
                                    <option value="Highway Patrol Group">Highway Patrol Group</option>
                                    <option value="Maritime Group">Maritime Group</option>
                                    <option value="Aviation Security Group">Aviation Security Group</option>
                                    <option value="Police Security and Protection Group">Police Security and Protection Group</option>
                                    <option value="Explosives and Ordnance Disposal Unit">Explosives and Ordnance Disposal Unit</option>
                                    <option value="Women and Children Protection Center">Women and Children Protection Center</option>
                                    <option value="Drug Enforcement Group">Drug Enforcement Group</option>
                                    
                                    <!-- Administrative Units -->
                                    <option value="Finance Service">Finance Service</option>
                                    <option value="Health Service">Health Service</option>
                                    <option value="Chaplain Service">Chaplain Service</option>
                                    <option value="Legal Service">Legal Service</option>
                                    <option value="Internal Affairs Service">Internal Affairs Service</option>
                                    <option value="OTHERS">OTHERS</option>
                                </select>
                            </div>
                            
                            <!-- Security Question -->
                            <div class="mb-3">
                                <label for="security_question" class="form-label">Security Question</label>
                                <select class="form-select" id="security_question" name="security_question" required>
                                    <option value="" disabled selected>Select a Security Question</option>
                                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                    <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                    <option value="What was your first car?">What was your first car?</option>
                                    <option value="What elementary school did you attend?">What elementary school did you attend?</option>
                                    <option value="What is the name of the town where you were born?">What is the name of the town where you were born?</option>
                                    <option value="What is your favorite food?">What is your favorite food?</option>
                                    <option value="Who was your childhood hero?">Who was your childhood hero?</option>
                                    <option value="What is the name of your best friend?">What is the name of your best friend?</option>
                                    <option value="What is your favorite movie?">What is your favorite movie?</option>
                                    <option value="What was the make of your first mobile phone?">What was the make of your first mobile phone?</option>
                                </select>
                            </div>
                            <!-- Security Answer -->
                            <div class="mb-3">
                                <label for="security_answer" class="form-label">Security Answer</label>
                                <input type="password" class="form-control" id="security_answer" name="security_answer" placeholder="Answer to your selected security question" required>
                            </div>
                            <!-- Terms and Conditions -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I accept the <a href="#" class="text-primary text-decoration-none">terms and conditions</a>
                                </label>
                            </div>
                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Sign Up</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <small class="text-muted">Already have an account? <a href="../responders/log_in.html" class="text-primary">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
