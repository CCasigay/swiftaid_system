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
