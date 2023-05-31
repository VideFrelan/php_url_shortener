<?php
session_start();
require_once('../configuration/config.php');

// Check if the user is already logged in
if (isset($_SESSION['user'])) {
    // Redirect the user to the appropriate dashboard based on the role
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        header('Location: index.php');
        exit();
    }
}

// Process the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Perform login authentication
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check the credentials in the database
    $user = getUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        // Authentication successful
        $_SESSION['user'] = $user;

        // Redirect the user to the appropriate dashboard based on the role
        $role = $user['role'];
        if ($role === 'admin') {
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = "You're not Admin!";
        }
    } else {
        // Invalid credentials
        $error = "Invalid email or password.";
    }
}

// Function to get a user by email from the database
function getUserByEmail($email) {
    $conn = connectToDatabase();

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $user;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
        <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Add Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
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
        <h2 class="mt-5">Admin Login</h2>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <?php if (isset($errorMessage)) { ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="text" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
    <!-- Add Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
