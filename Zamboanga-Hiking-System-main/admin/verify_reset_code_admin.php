<?php
require_once "../includes/db.php";

session_start();

if (!isset($_SESSION['reset_email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: forgot_password_admin.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $email = $_SESSION['reset_email'];
    
    if (!empty($code)) {
        // Check if code is valid and not expired (15 minutes)
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND user_type = 'admin' AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $code]);
        $reset_request = $stmt->fetch();
        
        if ($reset_request) {
            $_SESSION['verified_reset'] = true;
            header("Location: reset_password_admin.php");
            exit();
        } else {
            $error = "Invalid or expired reset code. Please request a new one.";
        }
    } else {
        $error = "Please enter the reset code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code - Trail Admin</title>
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
        .verify-container {
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
        .email-display {
            background: #f0f7f1;
            padding: 12px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 14px;
            color: #2d5a3f;
            font-weight: 600;
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
        input[type="text"] {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 24px;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            background: white;
            color: #1e3a2c;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }
        input::placeholder {
            color: #b0bdb3;
            letter-spacing: 4px;
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
        .resend-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #5a7a5f;
        }
        .resend-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
        }
        .resend-link a:hover {
            text-decoration: underline;
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .back-link a:hover {
            color: #2d5a3f;
            gap: 10px;
        }
        .info-box {
            background: #f0f7f1;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 13px;
            color: #2d5a3f;
            line-height: 1.6;
        }
        @media (max-width: 480px) {
            .verify-container {
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
   
    <div class="verify-container">
        <div class="logo-section">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                </svg>
            </div>
            <h2>Verify Reset Code</h2>
            <p class="subtitle">Enter the 6-digit code sent to your email</p>
        </div>

        <div class="email-display">
            📧 <?php echo htmlspecialchars($_SESSION['reset_email']); ?>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label for="code">Reset Code</label>
                <input type="text" id="code" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
            </div>

            <button type="submit">Verify Code</button>
        </form>

        <div class="info-box">
            ⏱️ Code expires in 15 minutes. Check your spam folder if you don't see the email.
        </div>

        <div class="resend-link">
            Didn't receive the code? <a href="forgot_password_admin.php">Request new code</a>
        </div>

        <div class="back-link">
            <a href="login_admin.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>