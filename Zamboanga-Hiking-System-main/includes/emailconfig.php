<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // If using Composer
// OR if manual installation:
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

// =====================
// SMTP CONFIG CONSTANTS
// =====================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'heidilynnrubia09@gmail.com');
define('SMTP_PASSWORD', 'kozv jrdi zcjy fnte');
define('SMTP_PORT', 587);

// =====================
// EMAIL SENDER DETAILS
// =====================
define('SMTP_FROM_EMAIL', 'heidilynnrubia09@gmail.com');
define('SMTP_FROM_NAME', 'Zamboanga Hiking System');



function sendVerificationEmail($to_email, $code) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'heidilynnrubia09@gmail.com'; 
        $mail->Password = 'kozv jrdi zcjy fnte'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email Content
        $mail->setFrom('heidilynnrubia09@gmail.com', 'Zamboanga Hiking ');
        $mail->addAddress($to_email);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code - Zamboanga Hiking System';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4;'>
                <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>
                    <h2 style='color: #2d5016;'>Verify Your Email</h2>
                    <p>Thank you for registering with Zamboanga Hiking System!</p>
                    <p>Your verification code is:</p>
                    <div style='background: #f0f0f0; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #2d5016; border-radius: 5px;'>
                        {$code}
                    </div>
                    <p style='color: #666; margin-top: 20px;'>This code will expire in 5 minutes.</p>
                    <p style='color: #666;'>If you didn't request this code, please ignore this email.</p>
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                    <p style='color: #999; font-size: 12px;'>Zamboanga Hiking System</p>
                </div>
            </div>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}



// EXISTING DEVELOPER PASSWORD RESET FUNCTION
function sendPasswordResetEmail($to_email, $verification_code) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Zamboanga Hiking System';
        $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #3d7021, #4a8c2a); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .code-box { background: white; border: 2px dashed #4a8c2a; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                    .code { font-size: 32px; font-weight: bold; color: #4a8c2a; letter-spacing: 5px; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔒 Password Reset Request</h1>
                    </div>
                    <div class='content'>
                        <p>Hello,</p>
                        <p>We received a request to reset your password for your Zamboanga Hiking System account.</p>
                        <p>Your verification code is:</p>
                        <div class='code-box'>
                            <div class='code'>{$verification_code}</div>
                        </div>
                        <p>Enter this code on the password reset page to continue.</p>
                        <div class='warning'>
                            <strong>⚠️ Security Notice:</strong>
                            <ul style='margin: 10px 0; padding-left: 20px;'>
                                <li>This code expires in 15 minutes</li>
                                <li>If you didn't request this, please ignore this email</li>
                                <li>Never share this code with anyone</li>
                            </ul>
                        </div>
                        <p>Best regards,<br>Zamboanga Hiking System Team</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message. Please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        $mail->AltBody = "Your password reset verification code is: {$verification_code}. This code expires in 15 minutes. If you didn't request this, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password reset email failed: {$mail->ErrorInfo}");
        return false;
    }
} 





// NEW FUNCTION FROM DEVELOPER (RENAMED SAFELY)
function sendPasswordResetEmail_New($email, $code, $userType = 'User') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Zamboanga Hiking System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code - Zamboanga Hiking System';
        $mail->Body    = "
        <html>
        <head>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 40px auto;
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                }
                .email-header {
                    background: linear-gradient(135deg, #3d7021, #4a8c2a);
                    color: white;
                    padding: 40px 30px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .email-header p {
                    margin: 10px 0 0 0;
                    font-size: 14px;
                    opacity: 0.9;
                }
                .email-body {
                    padding: 40px 30px;
                }
                .email-body h2 {
                    color: #2d5016;
                    font-size: 22px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    color: #666;
                    font-size: 15px;
                    line-height: 1.6;
                    margin-bottom: 15px;
                }
                .code-box {
                    background: #f8f9fa;
                    border: 2px dashed #4a8c2a;
                    border-radius: 8px;
                    padding: 25px;
                    text-align: center;
                    margin: 30px 0;
                }
                .reset-code {
                    font-size: 42px;
                    font-weight: bold;
                    color: #2d5016;
                    letter-spacing: 8px;
                    font-family: 'Courier New', monospace;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>🔒 Password Reset Request</h1>
                    <p>Zamboanga Hiking System</p>
                </div>
                
                <div class='email-body'>
                    <h2>Hello {$userType},</h2>
                    <p>We received a request to reset your password. Use the code below to proceed with resetting your password:</p>
                    
                    <div class='code-box'>
                        <p>Your Password Reset Code:</p>
                        <div class='reset-code'>{$code}</div>
                    </div>

                    <p>If you didn’t request this, please ignore this email.</p>

                    <p style='margin-top: 30px;'>
                        Best regards,<br>
                        <strong>Zamboanga Hiking System Team</strong>
                    </p>
                </div>

            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Your password reset code is: {$code}\n\nThis code will expire in 15 minutes.\n\nIf you didn't request this, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password reset email failed: {$mail->ErrorInfo}");
        return false;
    }
}

?>
