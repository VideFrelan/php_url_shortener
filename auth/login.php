<?php
session_start();
require_once('../configuration/config.php');

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists and password is correct
    $user = getUserByEmail($email);

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../index.php');
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}

// Function to get user by email from the database
function getUserByEmail($email) {
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Login</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <?php if (isset($error)) { ?>
                <div class="error"><?php echo $error; ?></div>
            <?php } ?>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>. | <a href="forgot_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>
