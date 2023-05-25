<?php
session_start();
require_once '../configuration/config.php';

// Redirect to index.php if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Redirect to register.php if registration data is not found in session
if (!isset($_SESSION['registration_data'])) {
    header('Location: register.php');
    exit();
}

// Process OTP verification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInputOTP = htmlspecialchars($_POST['otp']); // Sanitize the OTP input
    $registrationData = $_SESSION['registration_data'];
    $otp = $registrationData['otp'];

    if ($userInputOTP === $otp) {
        $username = $registrationData['username'];
        $email = $registrationData['email'];
        $password = $registrationData['password'];

        $conn = connectToDatabase();
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        $_SESSION['user_id'] = $email;
        $_SESSION['username'] = $username;

        // Clear registration data from session
        unset($_SESSION['registration_data']);

        header('Location: redirecting.php');
        exit();
    } else {
        $otpError = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Verify OTP</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your email to complete the registration process.</p>
        <?php if (isset($otpError)) { ?>
            <p>Invalid OTP. Please try again.</p>
        <?php } ?>
        <form action="verify_otp.php" method="post">
            <input type="text" name="otp" placeholder="OTP" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
