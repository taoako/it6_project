<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../dbcon/db_connection.php';

// Ensure the `role`, `email`, and `phone` columns exist in the `users` table
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) NOT NULL");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) NOT NULL");
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(15) NOT NULL");

// Ensure the `role` column exists in the `employee` table
$conn->query("ALTER TABLE employee ADD COLUMN IF NOT EXISTS role VARCHAR(50) NOT NULL");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Role selected by the user

    // Check if username already exists in the users table
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "Username already exists. Please choose another one.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $hashed_password, $first_name, $last_name, $email, $phone, $role);
        
        if ($stmt->execute()) {
            // Get the last inserted user ID
            $user_id = $stmt->insert_id;

            // Insert into employee table
            $employee_stmt = $conn->prepare("INSERT INTO employee (name, first_name, last_name, role) 
                    VALUES (?, ?, ?, ?)");
            $employee_stmt->bind_param("ssss", $username, $first_name, $last_name, $role);
            
            if ($employee_stmt->execute()) {
                echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
                exit;
            } else {
                $error_message = "Error adding employee: " . $conn->error;
            }
            $employee_stmt->close();
        } else {
            $error_message = "Error: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Daddy's Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1b8a3f; /* Matching login page */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .signup-card {
            background: rgba(255, 255, 255, 1);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 15px;
        }
        .btn-success {
            background-color: #1b8a3f;
            border-color: #1b8a3f;
        }
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
        }
        .action-btn {
            background-color: #8b93a7; /* Gray-purple color from login page */
            border: none;
            border-radius: 50px;
            padding: 12px 0;
            margin-bottom: 15px;
            font-size: 1.1rem;
            width: 100%;
            color: white;
            transition: all 0.3s;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="signup-card">
        <h2 class="text-center mb-4">Sign Up for Daddy's Nook</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message text-center"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" id="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" id="last_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" required>
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
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn action-btn" id="submit-btn">Sign Up</button>
                <a href="forgot_password.php" class="btn action-btn" style="background-color: #6c757d;">Forgot Password?</a>
                <a href="login.php" class="btn action-btn" style="background-color: #6c757d;">Back to Login</a>
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
            const submitButton = document.getElementById('submit-btn');
            
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