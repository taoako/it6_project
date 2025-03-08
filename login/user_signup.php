<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connection.php';

// Check if users table exists and has employee_id column
$tableExists = $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0;
if ($tableExists) {
    // Check if employee_id column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'employee_id'");
    if ($result->num_rows == 0) {
        // Add employee_id column if it doesn't exist
        $alter_table = "ALTER TABLE users ADD COLUMN employee_id INT DEFAULT NULL";
        if (!$conn->query($alter_table)) {
            echo "<script>alert('Error adding employee_id column: " . $conn->error . "');</script>";
        }
    }
} else {
    // Create the users table if it doesn't exist
    $create_table = "CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        password VARCHAR(100) NOT NULL,
        first_name VARCHAR(50) DEFAULT NULL,
        last_name VARCHAR(50) DEFAULT NULL,
        email VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(15) DEFAULT NULL,
        employee_id INT DEFAULT NULL
    )";
    
    if (!$conn->query($create_table)) {
        echo "<script>alert('Error creating users table: " . $conn->error . "');</script>";
    }
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $employee_id = !empty($_POST['employee_id']) ? $_POST['employee_id'] : NULL;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password
    
    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone, employee_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $username, $password, $first_name, $last_name, $email, $phone, $employee_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Signup successful! Please login.'); window.location.href='login.php';</script>";
        exit; // Stop further execution
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up User - Daddy's Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, rgb(132, 240, 135), hsl(122, 50.20%, 44.90%));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .signup-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <h2 class="text-center mb-4">Sign Up User</h2>
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee ID</label>
                <select class="form-select" name="employee_id" required>
                    <option value="">Select Employee ID</option>
                    <?php
                    $sql_employees = "SELECT employee_id, first_name, last_name FROM employee";
                    $result_employees = $conn->query($sql_employees);
                    if ($result_employees && $result_employees->num_rows > 0) {
                        while ($row = $result_employees->fetch_assoc()) {
                            echo "<option value='{$row['employee_id']}'>{$row['employee_id']} - {$row['first_name']} {$row['last_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" required>
                <div id="password-feedback" class="form-text text-danger d-none">
                    Passwords do not match.
                </div>
            </div>
            <!-- Updated Button Section -->
            <div class="d-grid gap-2">
                <button type="submit" name="signup" class="btn btn-success btn-lg">Sign Up</button>
                <a href="login.php" class="btn btn-secondary">Back to Login</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation check
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const feedbackElement = document.getElementById('password-feedback');
            const submitButton = document.querySelector('button[type="submit"]');
            
            function checkPasswords() {
                if (passwordField.value !== confirmPasswordField.value) {
                    feedbackElement.classList.remove('d-none');
                    submitButton.disabled = true;
                    return false;
                } else {
                    feedbackElement.classList.add('d-none');
                    submitButton.disabled = false;
                    return true;
                }
            }
            
            passwordField.addEventListener('input', checkPasswords);
            confirmPasswordField.addEventListener('input', checkPasswords);
            
            // Add form validation
            window.validateForm = function() {
                return checkPasswords();
            };
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>