<?php
error_reporting(E_ALL);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'tenazped_admin');
define('DB_PASSWORD', 'frelanGTA123');
define('DB_NAME', 'tenazped_url_shortener');

define('BASE_URL', 'https://frelan.tenazpedia.com/');

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
?>
