<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];

  
    $sql = "SELECT * FROM employee WHERE name = '$name' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['loggedin'] = true;
        $_SESSION['name'] = $name;
        header("Location: ../index.php"); 
        exit;
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 8Bit Inventory System</title>
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
            width: 200%;
            max-width: 500px;
        }
        .login-card h2 {
            color: #333;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .btn-login {
            background: #6a11cb;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: bold;
            width: 100%;
            color: white;
        }
        .btn-login:hover {
            background: #4CAF50;
        }
        .btn-secondary, .btn-primary {
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
            width: 100%;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 class="text-center">Welcome to Daddy's Nook</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Username</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
            <a href="forgot_password.php" class="btn btn-secondary">Forgot Password</a>
            <a href="signup.php" class="btn btn-primary">Sign Up</a>
        </form>
    </div>
</body>
</html>