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
            $users[$row['id']] = $row;
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

// Function to sanitize input
function sanitizeInput($input)
{
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
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
                <p>Welcome, admin! Here are the shortened URLs and users:</p>
                <form method="GET" action="search.php">
                    <div class="form-group">
                        <input type="text" name="keyword" placeholder="Search URLs or Users here..." class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="" class="btn btn-primary">Search</button>
                    </div>
                </form>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Original URL</th>
                            <th>Short URL</th>
                            <th>User</th>
                            <th>Views</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urls as $url) { ?>
                            <tr>
                                <td><?php echo $url['id']; ?></td>
                                <td><?php echo sanitizeInput($url['original_url']); ?></td>
                                <td><?php echo sanitizeInput($url['short_url']); ?></td>
                                <td><?php echo sanitizeInput($users[$url['user_id']]['username']); ?></td>
                                <td><?php echo $url['views']; ?></td>
                                <td><?php echo sanitizeInput($url['created_at']); ?></td>
                                <td>
                                    <a href="admin_delete.php?type=url&id=<?php echo $url['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this URL?')">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo sanitizeInput($user['username']); ?></td>
                                <td><?php echo sanitizeInput($user['email']); ?></td>
                                <td><?php echo sanitizeInput($user['role']); ?></td>
                                <td><?php echo sanitizeInput($user['created_at']); ?></td>
                                <td>
                                    <a href="admin_delete.php?type=user&id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <a href="index.php?logout=true" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>