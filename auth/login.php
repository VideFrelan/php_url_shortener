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
        <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Add Bootstrap CSS link -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <!-- Specifies the character encoding of the web page (usually using UTF-8). -->
        <meta charset="utf-8">
        <!-- Provides a brief description of the content of a web page for search engine purposes -->
        <meta name="description" content="URL Shortener is a tool to shorten long URLs and make them more manageable.">
        <!-- Determines keywords related to web pages for search engine purposes -->
        <meta name="keywords" content="URL shortener, short URLs, link shortener, web tools">
</head>
<body>
    <div class="container">
        <h1>URL Shortener</h1>
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="form-control">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required class="form-control">
            </div>
            <?php if (isset($error)) { ?>
                <div class="error"><?php echo $error; ?></div>
            <?php } ?>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>. | <a href="forgot_password.php">Forgot Password?</a></p>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>