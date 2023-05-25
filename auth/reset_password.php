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

    // Validate the password
    if (validatePassword($password, $confirmPassword)) {
        // Update the password in the database
        if (updatePassword($email, $password)) {
            // Clear the session data
            unset($_SESSION['reset_token_data']);

            // Set the success message
            $successMessage = "Your password has been successfully updated.";
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
function updatePassword($email, $password) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $password, $email);
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
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Reset Password</h2>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } elseif (isset($successMessage)) { ?>
            <div class="success"><?php echo $successMessage; ?></div>
        <?php } ?>
        <?php if (!isset($successMessage)) { ?>
            <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="post">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
