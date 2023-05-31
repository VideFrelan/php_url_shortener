<?php
// Import PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

// Email configuration
define('SMTP_HOST', 'mail.tenazpedia.com');
define('SMTP_PORT', '465');
define('SMTP_USERNAME', 'support@tenazpedia.com');
define('SMTP_PASSWORD', 'frelanGTA123');
define('EMAIL_FROM', 'support@tenazpedia.com');

// Send OTP to the provided email address using PHPMailer
function sendOTP($email, $otp)
{
    $mail = new PHPMailer(true);

    try {
        // Configure SMTP settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'ssl';

        // Set email content
        $mail->setFrom(EMAIL_FROM, 'URL Shortener');
        $mail->addAddress($email);
        $mail->Subject = 'OTP for Registration';
        $mail->Body = 'Your OTP for registration is: ' . $otp;

        // Send email
        $mail->send();
    } catch (Exception $e) {
        // Handle exception if an error occurs
        echo 'Email could not be sent. Error: ' . $mail->ErrorInfo;
    }
}
?>