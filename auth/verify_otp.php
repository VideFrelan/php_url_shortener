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
        $hashedPassword = $registrationData['password'];

        $conn = connectToDatabase();
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
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
        <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Add Bootstrap CSS link -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your email (<?php echo $_SESSION['email']; ?>) to complete the registration process.</p>
        <?php if (isset($otpError)) { ?>
            <p>Invalid OTP. Please try again.</p>
        <?php } ?>
        <form action="verify_otp.php" method="post">
            <div class="form-group">
                <input type="text" name="otp" placeholder="OTP" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>