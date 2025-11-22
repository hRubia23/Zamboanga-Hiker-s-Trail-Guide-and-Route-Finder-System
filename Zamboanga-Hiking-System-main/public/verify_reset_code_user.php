<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['reset_email']) || $_SESSION['user_type'] !== 'user') {
    header("Location: forgot_password_user.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $email = $_SESSION['reset_email'];
    
    if (!empty($code)) {
        // Check if code is valid and not expired (15 minutes)
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND user_type = 'user' AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $code]);
        $reset_request = $stmt->fetch();
        
        if ($reset_request) {
            $_SESSION['verified_reset'] = true;
            header("Location: reset_password_user.php");
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
    <title>Verify Reset Code - Zamboanga Hiking System</title>
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
       
        .verify-container {
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
       
        .verify-header {
            text-align: center;
            margin-bottom: 35px;
        }
       
        .verify-header h2 {
            color: #2d5016;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
       
        .verify-header p {
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

        .email-display {
            background: #f0f7f1;
            padding: 12px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 14px;
            color: #2d5016;
            font-weight: 600;
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
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 24px;
            transition: all 0.3s ease;
            background: #fafafa;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 600;
            color: #2d5016;
        }
       
        .form-group input:focus {
            outline: none;
            border-color: #4a8c2a;
            background: white;
            box-shadow: 0 0 0 4px rgba(74, 140, 42, 0.1);
        }

        .form-group input::placeholder {
            letter-spacing: 4px;
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
       
        .verify-button {
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
       
        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);
            background: linear-gradient(135deg, #4a8c2a, #3d7021);
        }
       
        .verify-button:active {
            transform: translateY(0);
        }

        .resend-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .resend-link a {
            color: #4a8c2a;
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
    <div class="verify-container">
        <div class="verify-header">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                </svg>
            </div>
            <h2>Verify Reset Code</h2>
            <p>Enter the 6-digit code sent to your email</p>
        </div>

        <div class="email-display">
            📧 <?php echo htmlspecialchars($_SESSION['reset_email']); ?>
        </div>
       
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
       
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="code">Reset Code</label>
                <input type="text" id="code" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
            </div>
           
            <button type="submit" class="verify-button">Verify Code</button>
        </form>

        <div class="info-box">
            ⏱️ Code expires in 15 minutes. Check your spam folder if you don't see the email.
        </div>

        <div class="resend-link">
            Didn't receive the code? <a href="forgot_password_user.php">Request new code</a>
        </div>
       
        <div class="back-link">
            <a href="login_user.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>