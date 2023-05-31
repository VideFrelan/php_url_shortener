<?php
session_start();
require_once('../configuration/config.php');

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
        // Delete a user account and associated URLs
        $deleted = deleteUserAndUrls($id);
    
        if ($deleted) {
            header('Location: index.php');
            exit();
        } else {
            $error = "Failed to delete the user.";
        }
    }
}

// Function to get user details
function getUser($id)
{
    $conn = connectToDatabase();

    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $result;
}

// Function to delete a shortened URL
function deleteUrl($id)
{
    $conn = connectToDatabase();

    $sql = "DELETE FROM url_mappings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $result;
}

// Function to delete user and associated URLs
function deleteUserAndUrls($userId)
{
    $conn = connectToDatabase();

    // Check if the user has any associated URLs
    $sqlCheckUrls = "SELECT COUNT(*) as total FROM url_mappings WHERE user_id = ?";
    $stmtCheckUrls = $conn->prepare($sqlCheckUrls);
    $stmtCheckUrls->bind_param("i", $userId);
    $stmtCheckUrls->execute();
    $result = $stmtCheckUrls->get_result();
    $row = $result->fetch_assoc();
    $totalUrls = $row['total'];

    // Delete URLs associated with the user, if any
    if ($totalUrls > 0) {
        $sqlUrls = "DELETE FROM url_mappings WHERE user_id = ?";
        $stmtUrls = $conn->prepare($sqlUrls);
        $stmtUrls->bind_param("i", $userId);
        $deletedUrls = $stmtUrls->execute();
        $stmtUrls->close();
    } else {
        $deletedUrls = true;
    }

    // Delete the user
    $sqlUser = "DELETE FROM users WHERE id = ?";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bind_param("i", $userId);
    $deletedUser = $stmtUser->execute();

    $stmtCheckUrls->close();
    $stmtUser->close();
    $conn->close();

    return $deletedUrls && $deletedUser;
}


// Redirect back to the admin dashboard if the type or ID parameters are not valid
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