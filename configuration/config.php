<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'YOUR_DB_USERNAME');
define('DB_PASSWORD', 'YOUR_DB_PASSWORD');
define('DB_NAME', 'YOUR_DB_NAME');

define('BASE_URL', 'https://YOUR-DOMAIN.COM/');

// Email configuration
define('SMTP_HOST', 'YOUR-SMTP-HOST');
define('SMTP_PORT', 'ENTER_PORT_HERE');
define('SMTP_USERNAME', 'YOUR-SMTP-USERNAME');
define('SMTP_PASSWORD', 'YOUR-SMTP-PASSWORD');
define('EMAIL_FROM', 'YOUR-EMAIL');

// Database connection function
function connectToDatabase()
{
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Generate a random OTP
function generateOTP()
{
    $characters = '0123456789';
    $otp = '';

    for ($i = 0; $i < 6; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $otp;
}

// Send OTP to the provided email address
function sendOTP($email, $otp)
{
    $to = $email;
    $subject = 'OTP for Registration';
    $message = 'Your OTP for registration is: ' . $otp;
    $headers = "From: " . EMAIL_FROM . "\r\n";
    $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";

    mail($to, $subject, $message, $headers);
}
?>