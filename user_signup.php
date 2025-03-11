<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../dbcon/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password
    $role = $_POST['role']; // Role selected by the user

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $password, $first_name, $last_name, $email, $phone, $role);
    
    if ($stmt->execute()) {
        // Get the last inserted user ID
        $user_id = $stmt->insert_id;

        // Insert into employee table
        $employee_stmt = $conn->prepare("INSERT INTO employee (name, first_name, last_name, role) 
                VALUES (?, ?, ?, ?)");
        $employee_stmt->bind_param("ssss", $username, $first_name, $last_name, $role);
        
        if ($employee_stmt->execute()) {
            echo "<script>alert('Signup successful! Please login.'); window.location.href='login.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error adding employee: " . $conn->error . "');</script>";
        }
        $employee_stmt->close();
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
                <button type="submit" name="signup" class="btn btn-success btn-lg">Sign Up</button>
                <a href="forgot_password.php" class="btn btn-secondary">Forgot Password?</a>
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