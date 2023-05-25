<?php
session_start();
require_once('../configuration/config.php');

// Redirect to index.php if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = $_POST['email'];
    $password = htmlspecialchars($_POST['password']);
    $confirmPassword = htmlspecialchars($_POST['confirm_password']);

    // Check if email or username is already taken
    if (isEmailTaken($email)) {
        $error = "Email is already taken.";
    } elseif (isUsernameTaken($username)) {
        $error = "Username is already taken.";
    } elseif (strlen($username) < 5) {
        $error = "Username must be at least 5 characters long.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Password and confirm password do not match.";
    } else {
        $otp = generateOTP(); // Generate OTP
        sendOTP($email, $otp);

        // Store registration data in session for verification
        $_SESSION['registration_data'] = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'otp' => $otp
        ];

        // Redirect to verify_otp.php
        header('Location: verify_otp.php');
        exit();
    }
}

// Function to check if email is already taken
function isEmailTaken($email) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to check if username is already taken
function isUsernameTaken($username) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Register</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Register</h2>
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
