<?php
require_once "../includes/db.php";
require_once '../includes/emailconfig.php';

session_start();

$success = '';
$error = '';
$remaining_seconds = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Check if email exists in admins table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Check for recent reset request (1 minute cooldown)
            $stmt = $pdo->prepare("SELECT *, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago FROM password_resets WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);
            $recent_request = $stmt->fetch();
            
            if ($recent_request) {
                $remaining_seconds = 60 - $recent_request['seconds_ago'];
                if ($remaining_seconds < 0) $remaining_seconds = 0;
                $error = "Please wait before requesting another reset code.";
            } else {
                // Generate 6-digit reset code
                $reset_code = sprintf("%06d", mt_rand(1, 999999));
                
                // Store reset code in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, code, user_type) VALUES (?, ?, 'admin')");
                $stmt->execute([$email, $reset_code]);
                
                // Send reset code via email
                if (sendPasswordResetEmail($email, $reset_code, 'Admin')) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['user_type'] = 'admin';
                    header("Location: verify_reset_code_admin.php");
                    exit();
                } else {
                    $error = "Failed to send reset code. Please try again.";
                }
            }
        } else {
            // Don't reveal if email exists or not for security
            $error = "If this email exists, a reset code will be sent.";
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Trail Admin</title>
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
        .forgot-container {
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
        input[type="email"] {
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
        input[type="email"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
        }
        input[type="email"]:focus ~ .input-icon {
            fill: #4CAF50;
            transform: scale(1.1);
        }
        input::placeholder {
            color: #b0bdb3;
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
        button[type="submit"]:disabled {
            background: linear-gradient(135deg, #9e9e9e, #bdbdbd);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        .countdown-timer {
            font-weight: 700;
            color: #c62828;
        }
        @media (max-width: 480px) {
            .forgot-container {
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
   
    <div class="forgot-container">
        <div class="decorative-leaves leaf-1"></div>
        <div class="decorative-leaves leaf-2"></div>
       
        <div class="logo-section">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 6l-3.75 5 2.85 3.8-1.6 1.2C9.81 13.75 7 10 7 10l-6 8h22L14 6z"/>
                </svg>
            </div>
            <h2>Forgot Password</h2>
            <p class="subtitle">Enter your email to receive a reset code</p>
        </div>

        <?php if ($error && $remaining_seconds > 0): ?>
            <div class="error-message" id="countdown-error">
                Please wait <span class="countdown-timer" id="countdown"><?php echo $remaining_seconds; ?></span> seconds before requesting another reset code.
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="reset-form">
            <div class="input-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="Enter your admin email" required autofocus>
                    <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                </div>
            </div>

            <button type="submit" id="submit-btn" <?php echo ($remaining_seconds > 0) ? 'disabled' : ''; ?>>
                <?php echo ($remaining_seconds > 0) ? 'Please Wait...' : 'Send Reset Code'; ?>
            </button>
        </form>

        <div class="info-box">
            ℹ️ You can request a new code after 1 minute if you don't receive it.
        </div>

        <div class="back-link">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>

    <?php if ($remaining_seconds > 0): ?>
    <script>
        let remainingTime = <?php echo $remaining_seconds; ?>;
        const countdownEl = document.getElementById('countdown');
        const submitBtn = document.getElementById('submit-btn');
        const countdownError = document.getElementById('countdown-error');

        const countdownInterval = setInterval(function() {
            remainingTime--;
            if (countdownEl) {
                countdownEl.textContent = remainingTime;
            }
            
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Code';
                if (countdownError) {
                    countdownError.style.display = 'none';
                }
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>