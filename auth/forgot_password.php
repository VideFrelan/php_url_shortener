<?php
session_start();
require_once('../configuration/config.php');

// Process forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    if (isEmailValid($email)) {
        $token = generateToken(); // Generate a unique token
        $expiry = time() + 3600; // Set token expiry time to 1 hour

        // Store the token data in the session
        $_SESSION['reset_token_data'] = [
            'email' => $email,
            'token' => $token,
            'expiry' => $expiry
        ];

        sendResetPasswordEmail($email, $token); // Send the reset password email

        // Redirect to the same page with a success message
        header('Location: forgot_password.php?success=true');
        exit();
    } else {
        $error = "Email does not exist.";
    }
}

// Function to check if the email is valid
function isEmailValid($email) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    $isValid = $stmt->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $isValid;
}

// Function to generate a unique token
function generateToken() {
    // Generate a unique token using any desired method
    // Here, we generate a random string using md5 and timestamp
    return md5(uniqid() . time());
}

// Function to send the reset password email
function sendResetPasswordEmail($email, $token) {
    $to = $email;
    $subject = "Reset Your Password";
    $message = "To reset your password, please click the following link:\n\n";
    $message .= "https://frelan.tenazpedia.com/auth/reset_password.php?token=" . urlencode($token);

    // Add your email sending logic here, using PHPMailer or mail() function
    // Example using mail() function:
    mail($to, $subject, $message);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Forgot Password</title>
        <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Add Bootstrap CSS link -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Forgot Password</h2>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } elseif (isset($_GET['success']) && $_GET['success'] === 'true') { ?>
            <div class="success">An email with instructions to reset your password has been sent.</div>
        <?php } ?>
        <form action="forgot_password.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <p>Remember your password? <a href="login.php">Login here</a>.</p>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
