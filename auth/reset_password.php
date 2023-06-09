<?php
session_start();
require_once('../configuration/config.php');

// Check if the email and token data are stored in the session
if (!isset($_SESSION['reset_token_data'])) {
    die("Invalid request.");
}

$resetTokenData = $_SESSION['reset_token_data'];
$email = $resetTokenData['email'];
$token = $resetTokenData['token'];
$expiry = $resetTokenData['expiry'];

// Validate token and expiry
if (!isTokenValid($token, $expiry)) {
    die("Invalid token.");
}

// Process reset password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Validate the password
    if (validatePassword($password, $confirmPassword)) {
        // Update the password in the database
        if (updatePassword($email, $hashedPassword)) {
            // Clear the session data
            unset($_SESSION['reset_token_data']);

            // Set the success message
            $successMessage = "Your password has been successfully updated. You will be redirected to the login page shortly.";
        } else {
            $error = "Failed to update password.";
        }
    } else {
        $error = "Invalid password or passwords do not match.";
    }
}

// Function to validate the token and expiry
function isTokenValid($token, $expiry) {
    return isset($token) && isset($expiry) && $token === $_GET['token'] && time() <= $expiry;
}

// Function to validate the password
function validatePassword($password, $confirmPassword) {
    // Add your password validation logic here
    return strlen($password) >= 6 && $password === $confirmPassword;
}

// Function to update the password in the database
function updatePassword($email, $hashedPassword) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    $result = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $result;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Reset Password</title>
        <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Add Bootstrap CSS link -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <script>
        // Function to start the countdown
        function startCountdown(seconds) {
            var countdownElement = document.getElementById("countdown");
            countdownElement.textContent = seconds;

            var countdownInterval = setInterval(function() {
                seconds--;
                countdownElement.textContent = seconds;

                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    redirectToLogin();
                }
            }, 1000);
        }

        // Function to redirect to login page
        function redirectToLogin() {
            window.location.href = "logout.php";
        }
    </script>
</head>
<body onload="startCountdown(3)">
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Reset Password</h2>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } elseif (isset($successMessage)) { ?>
            <div class="success"><?php echo $successMessage; ?></div>
            <p>Please wait <span id="countdown"></span> seconds...</p>
        <?php } ?>
        <?php if (!isset($successMessage)) { ?>
            <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="post">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" name="password" id="password" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php } ?>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>