<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/emailconfig.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Check if email exists in users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Check for recent reset request (1 minute cooldown)
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE) ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);
            $recent_request = $stmt->fetch();
            
            if ($recent_request) {
                $error = "Please wait 1 minute before requesting another reset code.";
            } else {
                // Generate 6-digit reset code
                $reset_code = sprintf("%06d", mt_rand(1, 999999));
                
                // Store reset code in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, code, user_type) VALUES (?, ?, 'user')");
                $stmt->execute([$email, $reset_code]);
                
                // Send reset code via email
                if (sendPasswordResetEmail($email, $reset_code, 'User')) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['user_type'] = 'user';
                    header("Location: verify_reset_code_user.php");
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
    <title>Forgot Password - Zamboanga Hiking System</title>
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
       
        .forgot-container {
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
       
        .forgot-header {
            text-align: center;
            margin-bottom: 35px;
        }
       
        .forgot-header h2 {
            color: #2d5016;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
       
        .forgot-header p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
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

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #28a745;
        }
       
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
       
        .forgot-button {
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
       
        .forgot-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);
            background: linear-gradient(135deg, #4a8c2a, #3d7021);
        }
       
        .forgot-button:active {
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

        .info-box {
            background: #f0f7f1;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 13px;
            color: #2d5016;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
                </svg>
            </div>
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a reset code</p>
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
       
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your registered email" required autofocus>
            </div>
           
            <button type="submit" class="forgot-button">Send Reset Code</button>
        </form>

        <div class="info-box">
            ℹ️ You can request a new code after 1 minute if you don't receive it.
        </div>
       
        <div class="back-link">
            <a href="login_user.php">← Back to Login</a>
        </div>
    </div>

    <script>
// Check if there's a rate limit error and handle countdown
<?php if ($error && strpos($error, 'wait') !== false && strpos($error, 'minute') !== false): ?>
(function() {
    const errorDiv = document.querySelector('.error-message');
    const submitButton = document.querySelector('.forgot-button');
    const emailInput = document.querySelector('#email');
    
    if (errorDiv) {
        // Store the timestamp when error was shown
        const errorTimestamp = Date.now();
        const cooldownDuration = 60000; // 60 seconds in milliseconds
        
        // Disable form submission during cooldown
        submitButton.disabled = true;
        submitButton.style.opacity = '0.6';
        submitButton.style.cursor = 'not-allowed';
        emailInput.disabled = true;
        
        // Update countdown every second
        const countdownInterval = setInterval(function() {
            const elapsed = Date.now() - errorTimestamp;
            const remaining = cooldownDuration - elapsed;
            
            if (remaining <= 0) {
                // Cooldown finished - remove error and enable form
                errorDiv.style.transition = 'all 0.3s ease';
                errorDiv.style.opacity = '0';
                setTimeout(function() {
                    errorDiv.remove();
                }, 300);
                
                submitButton.disabled = false;
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
                emailInput.disabled = false;
                
                clearInterval(countdownInterval);
            } else {
                // Update the error message with countdown
                const secondsLeft = Math.ceil(remaining / 1000);
                errorDiv.textContent = `Please wait ${secondsLeft} second${secondsLeft !== 1 ? 's' : ''} before requesting another reset code.`;
            }
        }, 1000);
    }
})();
<?php endif; ?>
</script>

</body>
</html>