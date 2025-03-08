<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user is an employee using prepared statements
    $stmt = $conn->prepare("SELECT * FROM employee WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if the password needs to be verified with password_verify (for new hashed passwords)
        // or directly (for old plain text passwords)
        $password_matches = false;
        
        if (password_verify($password, $user['password'])) {
            $password_matches = true;
        } elseif ($password === $user['password']) {
            // Legacy password check - consider updating to hashed version on successful login
            $password_matches = true;
            
            // Update to hashed password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE employee SET password = ? WHERE name = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        if ($password_matches) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role']; // 'admin' or 'employee'
            $_SESSION['user_type'] = 'employee'; // To distinguish between employee and user
            header("Location: ../index.php");
            exit;
        }
    }
    $stmt->close();

    // Check if the user is a regular user using prepared statements
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if the password needs to be verified with password_verify (for new hashed passwords)
        // or directly (for old plain text passwords)
        $password_matches = false;
        
        if (password_verify($password, $user['password'])) {
            $password_matches = true;
        } elseif ($password === $user['password']) {
            // Legacy password check - consider updating to hashed version on successful login
            $password_matches = true;
            
            // Update to hashed password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        if ($password_matches) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'user'; // To distinguish between employee and user
            header("Location: ../index.php");
            exit;
        }
    }
    $stmt->close();

    // If no match, show error
    echo "<script>alert('Invalid username or password');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Daddy's Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,rgb(132, 240, 135),hsl(122, 50.20%, 44.90%));
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 class="text-center mb-4">Daddy's Nook</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">Login</button>
                <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
                <a href="signup.php" class="btn btn-outline-primary">Sign Up</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>