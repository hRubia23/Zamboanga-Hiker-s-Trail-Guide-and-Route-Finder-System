<?php

session_start();

require_once '../includes/db.php'; // adjust path if needed

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);

    $password = $_POST['password'];

   

    if (!empty($username) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");

        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];

            $_SESSION['user_username'] = $user['username'];

           

            header("Location: index.php");

            exit;

        } else {

            $error = "Invalid username or password.";

        }

    } else {

        $error = "Please fill in all fields.";

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Login - Zamboanga Hiking System</title>

    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

        }

       

        body {

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

            background: linear-gradient(135deg, #2d5016 0%, #3d7021 50%, #4a8c2a 100%);

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

            padding: 20px;

            position: relative;

            overflow: hidden;

        }

       

        body::before {

            content: '';

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            bottom: 0;

            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><path d="M0,300 Q300,200 600,300 T1200,300 L1200,600 L0,600 Z" fill="rgba(255,255,255,0.03)"/></svg>') repeat-x bottom;

            background-size: cover;

            opacity: 0.5;

            pointer-events: none;

        }

       

        .login-container {

            background: rgba(255, 255, 255, 0.98);

            backdrop-filter: blur(10px);

            padding: 45px 40px;

            border-radius: 20px;

            width: 100%;

            max-width: 420px;

            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),

                        0 0 0 1px rgba(255, 255, 255, 0.2) inset;

            position: relative;

            z-index: 1;

            animation: slideIn 0.5s ease-out;

        }

       

        @keyframes slideIn {

            from {

                opacity: 0;

                transform: translateY(-30px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }

       

        .login-header {

            text-align: center;

            margin-bottom: 35px;

        }

       

        .login-header h2 {

            color: #2d5016;

            font-size: 28px;

            font-weight: 600;

            margin-bottom: 8px;

        }

       

        .login-header p {

            color: #666;

            font-size: 14px;

        }

       

        .icon-wrapper {

            width: 70px;

            height: 70px;

            background: linear-gradient(135deg, #3d7021, #4a8c2a);

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 20px;

            box-shadow: 0 8px 20px rgba(61, 112, 33, 0.3);

        }

       

        .icon-wrapper svg {

            width: 35px;

            height: 35px;

            fill: white;

        }

       

        .form-group {

            margin-bottom: 20px;

            position: relative;

        }

       

        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #333;

            font-weight: 500;

            font-size: 14px;

        }

       

        .form-group input {

            width: 100%;

            padding: 14px 16px;

            border: 2px solid #e0e0e0;

            border-radius: 10px;

            font-size: 15px;

            transition: all 0.3s ease;

            background: #fafafa;

        }

       

        .form-group input:focus {

            outline: none;

            border-color: #4a8c2a;

            background: white;

            box-shadow: 0 0 0 4px rgba(74, 140, 42, 0.1);

        }

       

        .error-message {

            background: #fee;

            color: #c33;

            padding: 12px 16px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            border-left: 4px solid #c33;

            animation: shake 0.5s ease;

        }

       

        @keyframes shake {

            0%, 100% { transform: translateX(0); }

            25% { transform: translateX(-10px); }

            75% { transform: translateX(10px); }

        }

       

        .forgot-password {

            text-align: right;

            margin-top: -10px;

            margin-bottom: 10px;

        }

        

        .forgot-password a {

            color: #4a8c2a;

            text-decoration: none;

            font-size: 13px;

            font-weight: 500;

            transition: all 0.3s ease;

        }

        

        .forgot-password a:hover {

            color: #2d5016;

            text-decoration: underline;

        }

       

        .login-button {

            width: 100%;

            padding: 14px;

            background: linear-gradient(135deg, #3d7021, #4a8c2a);

            color: white;

            border: none;

            border-radius: 10px;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s ease;

            box-shadow: 0 4px 15px rgba(74, 140, 42, 0.3);

            margin-top: 10px;

        }

       

        .login-button:hover {

            transform: translateY(-2px);

            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);

            background: linear-gradient(135deg, #4a8c2a, #3d7021);

        }

       

        .login-button:active {

            transform: translateY(0);

        }

       

        .back-link {

            text-align: center;

            margin-top: 25px;

            padding-top: 20px;

            border-top: 1px solid #e0e0e0;

        }

       

        .back-link a {

            color: #4a8c2a;

            text-decoration: none;

            font-weight: 500;

            font-size: 14px;

            transition: all 0.3s ease;

            display: inline-flex;

            align-items: center;

            gap: 6px;

        }

       

        .back-link a:hover {

            color: #2d5016;

            gap: 10px;

        }

       

        .register-link {

            text-align: center;

            margin-top: 15px;

            font-size: 14px;

            color: #666;

        }

       

        .register-link a {

            color: #4a8c2a;

            text-decoration: none;

            font-weight: 600;

        }

       

        .register-link a:hover {

            text-decoration: underline;

        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-header">

            <div class="icon-wrapper">

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">

                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>

                </svg>

            </div>

            <h2>Welcome Back</h2>

            <p>Login to explore amazing trails</p>

        </div>

       

        <?php if ($error): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

       

        <form method="POST" autocomplete="off">

            <div class="form-group">

                <label for="username">Username</label>

                <input type="text" id="username" name="username" placeholder="Enter your username" required>

            </div>

           

            <div class="form-group">

                <label for="password">Password</label>

                <input type="password" id="password" name="password" placeholder="Enter your password" required>

            </div>

           

            <div class="forgot-password">

                <a href="forgot_password_user.php">Forgot Password?</a>

            </div>

           

            <button type="submit" class="login-button">Login</button>

        </form>

       

        <div class="register-link">

            Don't have an account? <a href="register_user.php">Register here</a>

        </div>

       

        <div class="back-link">

            <a href="index.php">← Back to Homepage</a>

        </div>

    </div>

</body>

</html>