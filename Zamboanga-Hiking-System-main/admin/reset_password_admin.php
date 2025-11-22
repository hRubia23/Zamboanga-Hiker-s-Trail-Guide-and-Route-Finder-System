<?php
require_once "../includes/db.php";

session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['verified_reset']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: forgot_password_admin.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $email = $_SESSION['reset_email'];
                
                
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
                $stmt->execute([$new_password, $email]);
                
                
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND user_type = 'admin'");
                $stmt->execute([$email]);
                
                
                unset($_SESSION['reset_email']);
                unset($_SESSION['verified_reset']);
                unset($_SESSION['user_type']);
                
                $success = "Password reset successful! Redirecting to login...";
                header("refresh:2;url=login_admin.php");
            } else {
                $error = "Password must be at least 6 characters long.";
            }
        } else {
            $error = "Passwords do not match.";
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
    <title>Reset Password - Trail Admin</title>
    <link href="https:
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
        .reset-container {
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
            line-height: 1.5;
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
        .success-message {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: #2e7d32;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #43a047;
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
        .success-message::before {
            content: '✓';
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
        input[type="password"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }
        input[type="password"]:focus ~ .input-icon {
            fill: #4CAF50;
            transform: scale(1.1);
        }
        input::placeholder {
            color: #b0bdb3;
        }
        .password-hint {
            font-size: 12px;
            color: #5a7a5f;
            margin-top: 5px;
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
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
        }
        button[type="submit"]:active {
            transform: translateY(0);
        }
        @media (max-width: 480px) {
            .reset-container {
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
   
    <div class="reset-container">
        <div class="logo-section">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http:
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
            </div>
            <h2>Reset Password</h2>
            <p class="subtitle">Create your new password</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="new_password">New Password</label>
                <div class="input-wrapper">
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required autofocus>
                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http:
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                </div>
                <div class="password-hint">Minimum 6 characters</div>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http:
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                </div>
            </div>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>