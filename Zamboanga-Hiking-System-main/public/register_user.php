<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/emailconfig.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($first_name) && !empty($last_name) && !empty($username) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } 
        
        elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } 
        else {
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $existing = $stmt->fetch();

            if ($existing) {
                $error = "Username already taken.";
            } else {
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existing_email = $stmt->fetch();

                if ($existing_email) {
                    $error = "Email already registered.";
                } else {
                    
                    $verification_code = sprintf("%06d", mt_rand(1, 999999));
                    
                    
                    $stmt = $pdo->prepare("INSERT INTO verification_codes (email, code) VALUES (?, ?)");
                    $stmt->execute([$email, $verification_code]);
                    
                    
                    if (sendVerificationEmail($email, $verification_code)) {
                        
                        $_SESSION['pending_registration'] = [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'username' => $username,
                            'email' => $email,
                            'password' => password_hash($password, PASSWORD_DEFAULT)
                        ];
                        
                        
                        header("Location: verify_email.php");
                        exit();
                    } else {
                        $error = "Failed to send verification email. Please try again.";
                    }
                }
            }
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
    <title>User Registration - Zamboanga Hiking System</title>
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

        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http:
            background-size: cover;
            opacity: 0.5;
            pointer-events: none;
        }
        
        .register-container {
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
        
        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .register-header h2 {
            color: #2d5016;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .register-header p {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
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
            line-height: 1.4;
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
        
        .register-button {
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
        
        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 140, 42, 0.4);
            background: linear-gradient(135deg, #4a8c2a, #3d7021);
        }
        
        .register-button:active {
            transform: translateY(0);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #4a8c2a;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            color: #2d5016;
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
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
        
        .features {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .features h3 {
            font-size: 14px;
            color: #2d5016;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .features ul {
            list-style: none;
            padding: 0;
        }
        
        .features li {
            font-size: 13px;
            color: #666;
            padding: 6px 0;
            padding-left: 24px;
            position: relative;
        }
        
        .features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4a8c2a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="icon-wrapper">
                <svg xmlns="http:
                    <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
            <h2>Create Account</h2>
            <p>Join us to explore amazing trails</p>
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
        
        <form method="POST" autocomplete="off">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="First name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required minlength="3">
                <div class="password-hint">Minimum 3 characters</div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                <div class="password-hint">We'll send a verification code here</div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required minlength="6">
                <div class="password-hint">Minimum 6 characters for security</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required minlength="6">
            </div>
            
            <button type="submit" class="register-button">Send Verification Code</button>
        </form>
        
        <div class="features">
            <h3>What you'll get:</h3>
            <ul>
                <li>Access to 500+ hiking trails</li>
                <li>Save your favorite routes</li>
                <li>Track your hiking progress</li>
                <li>Join the hiking community</li>
            </ul>
        </div>
        
        <div class="login-link">
            Already have an account? <a href="login_user.php">Login here</a>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Back to Homepage</a>
        </div>
    </div>
</body>
</html>