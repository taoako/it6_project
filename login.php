<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../dbcon/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password matches, log the user in
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['user_id']; // Store user ID for later use
            $_SESSION['role'] = $user['role']; // Store user role for access control

            // Redirect based on role
            if ($user['role'] == 'admin') {
                // Admin goes to stock-in page
                header("Location: ../stock-in/index.php");
                exit;
            } else {
                // Employee goes to POS page
                header("Location: ../pos/pos.php");
                exit;
            }
        } else {
            // Password does not match
            $error_message = "Invalid username or password";
        }
    } else {
        // User not found
        $error_message = "Invalid username or password";
    }
    $stmt->close();
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
            background-color: #1b8a3f; /* Darker green matching the image */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 50px;
            width: 95%;
            max-width: 1000px; /* Further increased from 900px */
            height: auto;
            min-height: 500px; /* Increased minimum height */
        }
        .container-row {
            display: flex;
            height: 100%;
            align-items: center; /* Center vertically */
        }
        .logo-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-right: 40px; /* Increased padding */
            text-align: center; /* Ensure all text is centered */
        }
        .login-section {
            flex: 1;
            padding-left: 40px; /* Increased padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center; /* Center horizontally */
            text-align: center; /* Ensure all text is centered */
        }
        .login-form {
            width: 100%;
            max-width: 300px; /* Limit form width for better aesthetics */
        }
        .brand-name {
            font-size: 2.8rem; /* Further increased font size */
            font-weight: bold;
            color: #1b8a3f;
            margin-bottom: 5px;
            text-align: center;
        }
        .brand-tagline {
            color: #6c757d;
            font-size: 1.1rem; /* Increased font size */
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 25px;
        }
        .welcome-text {
            font-size: 2.5rem; /* Further increased font size */
            color: #1b8a3f;
            margin-bottom: 40px; /* Increased margin */
            text-align: center;
        }
        .input-field {
            border-radius: 50px;
            padding: 12px 20px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            text-align: center;
            width: 100%;
        }
        .action-btn {
            background-color: #8b93a7; /* Gray-purple color from the image */
            border: none;
            border-radius: 50px;
            padding: 15px 0; /* Increased padding */
            margin-bottom: 25px; /* Increased margin */
            font-size: 1.3rem; /* Increased font size */
            width: 100%;
            color: white;
            transition: all 0.3s;
            display: block;
        }
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .divider {
            width: 2px; /* Made divider thicker */
            background-color: #dee2e6;
            margin: 0 30px; /* Increased margin */
            align-self: stretch; /* Make divider full height */
        }
        .logo-img {
            max-width: 100%;
            height: auto;
            margin-bottom: 25px;
        }
        .shopping-cart-icon {
            color: #1b8a3f;
            margin-bottom: 20px;
            transform: scale(1.4); /* Made icon even larger */
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .container-row {
                flex-direction: column;
            }
            .logo-section, .login-section {
                padding: 0;
                margin-bottom: 40px;
                width: 100%;
            }
            .divider {
                width: 80%;
                height: 2px;
                margin: 30px 0;
            }
            .login-container {
                padding: 40px;
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container-row">
            <div class="logo-section">
                <!-- Replace with your actual logo image path -->
                <div class="shopping-cart-icon">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="#1b8a3f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="#1b8a3f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="#1b8a3f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="brand-name">DADDY'S NOOK</h1>
                <p class="brand-tagline">ALL IN ONE ORDERS</p>
            </div>
            
            <div class="divider"></div>
            
            <div class="login-section">
                <h2 class="welcome-text">Welcome!</h2>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="login-form">
                    <input type="text" name="username" class="input-field" placeholder="Username" required>
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                    <button type="submit" class="btn action-btn mb-4">Login</button>
                    <a href="signup.php" class="btn action-btn mb-4">Sign Up</a>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>