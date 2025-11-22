<?php
session_start();
require_once '../includes/db.php';

// Check if there's pending registration
if (!isset($_SESSION['pending_registration'])) {
    header("Location: register_user.php");
    exit();
}

$success = '';
$error = '';
$email = $_SESSION['pending_registration']['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = trim($_POST['verification_code']);
    
    if (!empty($entered_code)) {
        // Check verification code
        $stmt = $pdo->prepare("
            SELECT * FROM verification_codes 
            WHERE email = ? 
            AND code = ? 
            AND is_used = 0 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$email, $entered_code]);
        $verification = $stmt->fetch();
        
        if ($verification) {
            // Mark code as used
            $stmt = $pdo->prepare("UPDATE verification_codes SET is_used = 1 WHERE id = ?");
            $stmt->execute([$verification['id']]);
            
            // Create user account
            $reg_data = $_SESSION['pending_registration'];
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, username, email, password, is_verified) 
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $reg_data['first_name'],
                $reg_data['last_name'],
                $reg_data['username'],
                $reg_data['email'],
                $reg_data['password']
            ]);
            
            // Clear session
            unset($_SESSION['pending_registration']);
            
            // Redirect to login with success message
            $_SESSION['registration_success'] = "Account created successfully! You can now log in.";
            header("Location: login_user.php");
            exit();
        } else {
            $error = "Invalid or expired verification code. Please try again.";
        }
    } else {
        $error = "Please enter the verification code.";
    }
}

// Handle resend code
if (isset($_GET['resend'])) {
    // Delete old codes
    $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE email = ?");
    $stmt->execute([$email]);
    
    // Generate new code
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code) VALUES (?, ?)");
    $stmt->execute([$email, $verification_code]);
    
    // Send email
    require_once '../includes/email_config.php';
    if (sendVerificationEmail($email, $verification_code)) {
        $success = "A new verification code has been sent to your email.";
    } else {
        $error = "Failed to resend verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Zamboanga Hiking System</title>
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
        }
        
        .verify-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 45px 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
        
        .verify-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3d7021, #4a8c2a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(61, 112, 33, 0.3);
        }
        
        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        
        .verify-header h2 {
            color: #2d5016;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .verify-header p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .email-display {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #2d5016;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #28a745;
            animation: fadeIn 0.5s ease;
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
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
            font-size: 15px;
        }
        
        .code-input {
            width: 100%;
            padding: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #4a8c2a;
            background: white;
            box-shadow: 0 0 0 4px rgba(74, 140, 42, 0.1);
        }
        
        .verify-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3d7021, #4a8c2a);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 140, 42, 0.3);
        }
        
        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);
        }
        
        .resend-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
        
        .resend-link {
            color: #4a8c2a;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .resend-link:hover {
            color: #2d5016;
            text-decoration: underline;
        }
        
        .timer {
            font-size: 13px;
            color: #999;
            margin-top: 10px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #333;
        }
        
        .info-box ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        
        .info-box li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-header">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
            </div>
            <h2>Verify Your Email</h2>
            <p>We've sent a 6-digit verification code to:</p>
        </div>
        
        <div class="email-display">
            <?php echo htmlspecialchars($email); ?>
        </div>
        
        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>📧 Check your email:</strong>
            <ul>
                <li>Code expires in <strong>5 minutes</strong></li>
                <li>Check your spam/junk folder if not received</li>
                <li>Enter the 6-digit code below</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="verification_code">Verification Code</label>
                <input 
                    type="text" 
                    id="verification_code" 
                    name="verification_code" 
                    class="code-input" 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    placeholder="000000" 
                    required
                    autocomplete="off"
                >
            </div>
            
            <button type="submit" class="verify-button">Verify Email</button>
        </form>
        
        <div class="resend-section">
            <p>Didn't receive the code?</p>
            <a href="?resend=1" class="resend-link">Resend Verification Code</a>
            <div class="timer" id="timer"></div>
        </div>
    </div>
    
    <script>
        // Auto-format code input (add spaces for readability)
        const codeInput = document.getElementById('verification_code');
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Focus on input on page load
        window.addEventListener('load', function() {
            codeInput.focus();
        });
        
        // Countdown timer (5 minutes)
        let timeLeft = 300; // 5 minutes in seconds
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `Code expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                timerElement.textContent = 'Code expired! Please request a new one.';
                timerElement.style.color = '#c33';
                clearInterval(timerInterval);
            }
            timeLeft--;
        }
        
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    </script>
</body>
</html>