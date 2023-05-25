<?php
session_start();
require_once('../configuration/config.php');
require_once('../administrator/index.php');

// Check if the user is logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Otherwise, redirect the user to the admin login page
    header('Location: admin_login.php');
    exit();
}

// Check if the delete action is allowed based on admin role
if ($_SESSION['user']['role'] !== 'admin') {
    // If not allowed, redirect back to the admin dashboard
    header('Location: index.php');
    exit();
}

// Check if the type and ID parameters are present
if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];

    if ($type === 'url') {
        // Delete a shortened URL
        $deleted = deleteUrl($id);
        if ($deleted) {
            header('Location: index.php');
            exit();
        } else {
            $error = "Failed to delete the URL.";
        }
    } elseif ($type === 'user') {
        // Delete a user account
        $deleted = deleteUser($id);
        if ($deleted) {
            header('Location: index.php');
            exit();
        } else {
            $error = "Failed to delete the user.";
        }
    }
}

// If the type or ID parameters are not valid, redirect back to the admin dashboard
header('Location: index.php');
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Delete</title>
</head>
<body>
    <?php if (isset($error)) { ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php } ?>
</body>
</html>
