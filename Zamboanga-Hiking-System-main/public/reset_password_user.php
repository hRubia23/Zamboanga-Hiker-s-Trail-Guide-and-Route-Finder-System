<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['verified_reset']) || $_SESSION['user_type'] !== 'user') {
    header("Location: forgot_password_user.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $email = $_SESSION['reset_email'];
                
                // Update user password (hashed)
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);
                
                // Delete used reset codes
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND user_type = 'user'");
                $stmt->execute([$email]);
                
                // Clear session
                unset($_SESSION['reset_email']);
                unset($_SESSION['verified_reset']);
                unset($_SESSION['user_type']);
                
                $success = "Password reset successful! Redirecting to login...";
                header("refresh:2;url=login_user.php");
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
    <title>Reset Password - Zamboanga Hiking System</title>
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
       
        .reset-container {
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
       
        .reset-header {
            text-align: center;
            margin-bottom: 35px;
        }
       
        .reset-header h2 {
            color: #2d5016;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
       
        .reset-header p {
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

        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
       
        .reset-button {
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
       
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);
            background: linear-gradient(135deg, #4a8c2a, #3d7021);
        }
       
        .reset-button:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                </svg>
            </div>
            <h2>Reset Password</h2>
            <p>Create your new password</p>
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
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required autofocus>
                <div class="password-hint">Minimum 6 characters for security</div>
            </div>
           
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
            </div>
           
            <button type="submit" class="reset-button">Reset Password</button>
        </form>
    </div>
</body>
</html>