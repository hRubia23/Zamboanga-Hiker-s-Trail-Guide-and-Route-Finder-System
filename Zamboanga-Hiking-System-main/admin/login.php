<?php

require_once "../includes/db.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];

    $password = $_POST['password'];

    // Get admin by username

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");

    $stmt->execute([$username]);

    $admin = $stmt->fetch();

    // Compare plain text password

    if ($admin && $admin['password'] === $password) {

        $_SESSION['admin_id'] = $admin['id'];

        $_SESSION['admin_username'] = $admin['username'];

        header("Location: dashboard.php");

        exit();

    } else {

        $error = "Invalid username or password!";

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Trail Admin - Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Merriweather:wght@300;400&display=swap" rel="stylesheet">

    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

        }

        body {

            font-family: 'Montserrat', sans-serif;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            background: linear-gradient(135deg, #1e3a2c 0%, #2d5a3f 50%, #4a7c59 100%);

            position: relative;

            overflow: hidden;

        }

        body::before {

            content: '';

            position: absolute;

            width: 100%;

            height: 100%;

            background-image:

                radial-gradient(circle at 20% 80%, rgba(139, 195, 74, 0.1) 0%, transparent 50%),

                radial-gradient(circle at 80% 20%, rgba(76, 175, 80, 0.15) 0%, transparent 50%);

            pointer-events: none;

        }

        .mountain-bg {

            position: absolute;

            bottom: 0;

            left: 0;

            width: 100%;

            height: 300px;

            background: linear-gradient(to top, rgba(30, 58, 44, 0.8), transparent);

            clip-path: polygon(0 100%, 0 60%, 15% 45%, 25% 55%, 35% 40%, 50% 50%, 65% 35%, 80% 45%, 100% 30%, 100% 100%);

            z-index: 1;

        }

        .login-container {

            position: relative;

            z-index: 10;

            background: rgba(255, 255, 255, 0.98);

            backdrop-filter: blur(20px);

            border-radius: 24px;

            box-shadow:

                0 20px 60px rgba(0, 0, 0, 0.3),

                0 0 100px rgba(139, 195, 74, 0.1);

            width: 90%;

            max-width: 440px;

            padding: 50px 45px;

            animation: slideUp 0.6s ease-out;

        }

        @keyframes slideUp {

            from {

                opacity: 0;

                transform: translateY(30px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }

        .logo-section {

            text-align: center;

            margin-bottom: 35px;

        }

        .logo-icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 20px;

            background: linear-gradient(135deg, #4CAF50, #8BC34A);

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);

        }

        .logo-icon svg {

            width: 38px;

            height: 38px;

            fill: white;

        }

        h2 {

            font-family: 'Merriweather', serif;

            font-size: 28px;

            color: #1e3a2c;

            font-weight: 700;

            margin-bottom: 8px;

        }

        .subtitle {

            color: #5a7a5f;

            font-size: 14px;

            font-weight: 400;

        }

        .error-message {

            background: linear-gradient(135deg, #ffebee, #ffcdd2);

            color: #c62828;

            padding: 14px 18px;

            border-radius: 12px;

            margin-bottom: 25px;

            font-size: 14px;

            display: flex;

            align-items: center;

            gap: 10px;

            border-left: 4px solid #e53935;

            animation: shake 0.4s ease-in-out;

        }

        @keyframes shake {

            0%, 100% { transform: translateX(0); }

            25% { transform: translateX(-8px); }

            75% { transform: translateX(8px); }

        }

        .error-message::before {

            content: '⚠';

            font-size: 18px;

        }

        form {

            display: flex;

            flex-direction: column;

            gap: 20px;

        }

        .input-group {

            position: relative;

        }

        .input-group label {

            display: block;

            margin-bottom: 8px;

            color: #2d5a3f;

            font-size: 13px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 0.5px;

        }

        .input-wrapper {

            position: relative;

            display: flex;

            align-items: center;

        }

        .input-icon {

            position: absolute;

            left: 16px;

            width: 20px;

            height: 20px;

            fill: #7a9d7e;

            transition: all 0.3s ease;

            pointer-events: none;

            z-index: 1;

        }

        input[type="text"],

        input[type="password"] {

            width: 100%;

            padding: 16px 16px 16px 48px;

            border: 2px solid #e0e0e0;

            border-radius: 12px;

            font-size: 15px;

            font-family: 'Montserrat', sans-serif;

            transition: all 0.3s ease;

            background: white;

            color: #1e3a2c;

        }

        input[type="text"]:focus,

        input[type="password"]:focus {

            outline: none;

            border-color: #4CAF50;

            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);

        }

        input[type="text"]:focus ~ .input-icon,

        input[type="password"]:focus ~ .input-icon {

            fill: #4CAF50;

            transform: scale(1.1);

        }

        input::placeholder {

            color: #b0bdb3;

        }

        .forgot-password {

            text-align: right;

            margin-top: -10px;

        }

        .forgot-password a {

            color: #4CAF50;

            text-decoration: none;

            font-size: 13px;

            font-weight: 500;

            transition: all 0.3s ease;

        }

        .forgot-password a:hover {

            color: #2d5a3f;

            text-decoration: underline;

        }

        button[type="submit"] {

            background: linear-gradient(135deg, #4CAF50, #66BB6A);

            color: white;

            padding: 16px;

            border: none;

            border-radius: 12px;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s ease;

            margin-top: 10px;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);

            position: relative;

            overflow: hidden;

        }

        button[type="submit"]::before {

            content: '';

            position: absolute;

            top: 50%;

            left: 50%;

            width: 0;

            height: 0;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.2);

            transform: translate(-50%, -50%);

            transition: width 0.6s, height 0.6s;

        }

        button[type="submit"]:hover::before {

            width: 300px;

            height: 300px;

        }

        button[type="submit"]:hover {

            transform: translateY(-2px);

            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);

        }

        button[type="submit"]:active {

            transform: translateY(0);

        }

        .footer-text {

            text-align: center;

            margin-top: 30px;

            color: #7a9d7e;

            font-size: 13px;

        }

        .decorative-leaves {

            position: absolute;

            opacity: 0.1;

            pointer-events: none;

        }

        .leaf-1 {

            top: 20px;

            right: 20px;

            width: 60px;

            height: 60px;

            background: #4CAF50;

            border-radius: 50% 0;

            transform: rotate(45deg);

        }

        .leaf-2 {

            bottom: 20px;

            left: 20px;

            width: 80px;

            height: 80px;

            background: #66BB6A;

            border-radius: 50% 0;

            transform: rotate(-135deg);

        }

        @media (max-width: 480px) {

            .login-container {

                padding: 40px 30px;

            }

            h2 {

                font-size: 24px;

            }

        }

    </style>

</head>

<body>

    <div class="mountain-bg"></div>

   

    <div class="login-container">

        <div class="decorative-leaves leaf-1"></div>

        <div class="decorative-leaves leaf-2"></div>

       

        <div class="logo-section">

            <div class="logo-icon">

                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">

                    <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>

                </svg>

            </div>

            <h2>Trail Admin</h2>

            <p class="subtitle">Welcome back to the wilderness</p>

        </div>

        <?php if (isset($error)): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="input-group">

                <label for="username">Username</label>

                <div class="input-wrapper">

                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>

                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">

                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>

                    </svg>

                </div>

            </div>

            <div class="input-group">

                <label for="password">Password</label>

                <div class="input-wrapper">

                    <input type="password" id="password" name="password" placeholder="Enter your password" required>

                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">

                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>

                    </svg>

                </div>

            </div>

            <div class="forgot-password">

                <a href="forgot_password_admin.php">Forgot Password?</a>

            </div>

            <button type="submit">Access Dashboard</button>

        </form>

        <p class="footer-text">🌲 Explore. Manage. Protect. 🏔️</p>

    </div>

</body>

</html>