<?php
include '../dbcon/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $new_password = $_POST['new_password'];
    
    // Hash the new password for security
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // First verify the username exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM employee WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Username exists, update the password using prepared statement
        $stmt->close();
        $update_stmt = $conn->prepare("UPDATE employee SET password = ? WHERE name = ?");
        $update_stmt->bind_param("ss", $hashed_password, $name);
        
        if ($update_stmt->execute()) {
            echo "<script>alert('Password updated successfully!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error updating password: " . $conn->error . "');</script>";
        }
        $update_stmt->close();
    } else {
        echo "<script>alert('Username not found. Please check and try again.');</script>";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Daddy's Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,rgb(132, 240, 135),hsl(122, 50.20%, 44.90%));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-card {
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
    <div class="password-card">
        <h2 class="text-center mb-4">Reset Your Password</h2>
        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="mb-3">
                <label for="name" class="form-label">Username</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" required>
                <div id="password-feedback" class="form-text text-danger d-none">
                    Passwords do not match.
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg" id="submit-btn">Reset Password</button>
                <a href="login.php" class="btn btn-secondary">Back to Login</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation check
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordField = document.querySelector('input[name="new_password"]');
            const confirmPasswordField = document.getElementById('confirm_password');
            const feedbackElement = document.getElementById('password-feedback');
            const submitButton = document.getElementById('submit-btn');
            
            function checkPasswords() {
                if (newPasswordField.value !== confirmPasswordField.value) {
                    feedbackElement.classList.remove('d-none');
                    submitButton.disabled = true;
                    return false;
                } else {
                    feedbackElement.classList.add('d-none');
                    submitButton.disabled = false;
                    return true;
                }
            }
            
            newPasswordField.addEventListener('input', checkPasswords);
            confirmPasswordField.addEventListener('input', checkPasswords);
            
            // Add form validation
            window.validateForm = function() {
                return checkPasswords();
            };
        });
    </script>
</body>
</html>