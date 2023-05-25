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

    if ($user && $password === $user['password']) {
        // Authentication successful
        $_SESSION['user'] = $user;

        // Redirect the user to the appropriate dashboard based on the role
        $role = $user['role'];
        if ($role === 'admin') {
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = "Anda bukan admin!";
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
</head>
<body>
    <h2>Admin Login</h2>
    <?php if (isset($error)) { ?>
        <p><?php echo $error; ?></p>
    <?php } ?>
    <?php if (isset($errorMessage)) { ?>
        <div><?php echo $errorMessage; ?></div>
    <?php } ?>
    <form method="POST" action="">
        <div>
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
</body>
</html>
