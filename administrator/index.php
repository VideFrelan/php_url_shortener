<?php
session_start();
require_once('../configuration/config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Process the logout action
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header('Location: admin_login.php');
    exit();
}

// Get the list of shortened URLs from the database
$urls = getAllUrls();
$users = getAllUsers();

// Function to get all shortened URLs from the database
function getAllUrls()
{
    $conn = connectToDatabase();

    $sql = "SELECT * FROM url_mappings";
    $result = $conn->query($sql);

    $urls = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $urls[] = $row;
        }
    }

    $conn->close();

    return $urls;
}

// Function to get all users from the database
function getAllUsers()
{
    $conn = connectToDatabase();

    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $conn->close();

    return $users;
}

// Process the delete action
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

// Function to delete a user account
function deleteUser($id)
{
    $conn = connectToDatabase();

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $result;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Admin Dashboard</h2>
        <?php if (isset($error)) { ?>
            <p class="text-danger"><?php echo $error; ?></p>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <p>Welcome, admin! Here are the shortened URLs:</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Original URL</th>
                            <th>Short URL</th>
                            <th>Views</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urls as $url) { ?>
                            <tr>
                                <td><?php echo $url['id']; ?></td>
                                <td><?php echo $url['original_url']; ?></td>
                                <td><?php echo $url['short_url']; ?></td>
                                <td><?php echo $url['views']; ?></td>
                                <td><?php echo $url['created_at']; ?></td>
                                <td>
                                    <a href="admin_delete.php?type=url&id=<?php echo $url['id']; ?>" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <hr>
                <p>Users:</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <a href="admin_delete.php?type=user&id=<?php echo $user['id']; ?>" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <hr>
            </div>
        </div>
        <footer>
            <p><a href="../index.php">Go back</a> to the URL shortener page | <a href="../auth/logout.php">logout</a></p>
        </footer>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
