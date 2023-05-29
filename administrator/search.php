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

// Get the keyword from the GET data
if (isset($_GET['keyword'])) {
    $keyword = sanitizeInput($_GET['keyword']);

    // Search for URLs
    $urls = searchUrls($keyword);

    // Search for users
    $users = searchUsers($keyword);
}

// Function to sanitize input data
function sanitizeInput($data)
{
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Function to search URLs
function searchUrls($keyword)
{
    $conn = connectToDatabase();

    $keyword = '%' . $keyword . '%';
    $sql = "SELECT url_mappings.*, users.username
            FROM url_mappings
            LEFT JOIN users ON url_mappings.user_id = users.id
            WHERE url_mappings.original_url LIKE ? OR url_mappings.short_url LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();

    $urls = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $urls[] = $row;
        }
    }

    $conn->close();

    return $urls;
}

// Function to search users
function searchUsers($keyword)
{
    $conn = connectToDatabase();

    $keyword = '%' . $keyword . '%';
    $sql = "SELECT users.*, url_mappings.id AS url_id, url_mappings.original_url, url_mappings.short_url, url_mappings.views, url_mappings.created_at
            FROM users
            LEFT JOIN url_mappings ON users.id = url_mappings.user_id
            WHERE users.username LIKE ? OR users.email LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $userId = $row['id'];
            if (!isset($users[$userId])) {
                $users[$userId] = $row;
                $users[$userId]['urls'] = [];
            }

            if (!empty($row['url_id']) && !empty($row['original_url']) && !empty($row['short_url'])) {
                $users[$userId]['urls'][] = [
                    'id' => $row['url_id'],
                    'original_url' => $row['original_url'],
                    'short_url' => $row['short_url'],
                    'views' => $row['views'],
                    'created_at' => $row['created_at'],
                ];
            }
        }
    }

    $conn->close();

    return $users;
}

// Function to delete URL
function deleteUrl($id)
{
    $conn = connectToDatabase();

    $sql = "DELETE FROM url_mappings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $result = $stmt->execute();

    $conn->close();

    return $result;
}

// Function to delete user
function deleteUser($id)
{
    $conn = connectToDatabase();

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $result = $stmt->execute();

    $conn->close();

    return $result;
}

// Handle the delete action if delete request is sent
if (isset($_GET['type']) && isset($_GET['id'])) {
    // Verify CSRF token
    if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $type = $_GET['type'];
    $id = $_GET['id'];

    if ($type === 'url') {
        deleteUrl($id);
    } elseif ($type === 'user') {
        deleteUser($id);
    }

    header("Location: search.php?keyword=$keyword");
    exit();
}

// Generate and store CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results for "<?php echo $keyword; ?>"</title>
    <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Search Results for "<?php echo $keyword; ?>"</h2>
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($urls) && count($urls) > 0) { ?>
                    <h4>Matching URLs:</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Original URL</th>
                                <th>Short URL</th>
                                <th>User</th>
                                <th>Views</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urls as $url) { ?>
                                <tr>
                                    <td><?php echo $url['id']; ?></td>
                                    <td><?php echo $url['original_url']; ?></td>
                                    <td><?php echo $url['short_url']; ?></td>
                                    <td><?php echo $url['username']; ?></td>
                                    <td><?php echo $url['views']; ?></td>
                                    <td><?php echo $url['created_at']; ?></td>
                                    <td>
                                        <form method="POST" action="search.php?type=url&id=<?php echo $url['id']; ?>&keyword=<?php echo $keyword; ?>" onSubmit="return confirm('Are you sure you want to delete this URL?')">
                                            <input type="hidden" name="token" value="<?php echo $csrfToken; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>

                <?php if (isset($users) && count($users) > 0) { ?>
                    <h4>Matching Users:</h4>
                    <?php foreach ($users as $user) { ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><?php echo $user['created_at']; ?></td>
                                    <td>
                                        <form method="POST" action="search.php?type=user&id=<?php echo $user['id']; ?>&keyword=<?php echo $keyword; ?>" onSubmit="return confirm('Are you sure you want to delete this user?')">
                                            <input type="hidden" name="token" value="<?php echo $csrfToken; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if (!empty($user['urls'])) { ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Original URL</th>
                                        <th>Short URL</th>
                                        <th>Views</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user['urls'] as $url) { ?>
                                        <tr>
                                            <td><?php echo $url['id']; ?></td>
                                            <td><?php echo $url['original_url']; ?></td>
                                            <td><?php echo $url['short_url']; ?></td>
                                            <td><?php echo $url['views']; ?></td>
                                            <td><?php echo $url['created_at']; ?></td>
                                            <td>
                                                <form method="POST" action="search.php?type=url&id=<?php echo $url['id']; ?>&keyword=<?php echo $keyword; ?>" onSubmit="return confirm('Are you sure you want to delete this URL?')">
                                                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <?php if ((!isset($urls) || count($urls) === 0) && (!isset($users) || count($users) === 0)) { ?>
                    <div class="alert alert-info mt-4">No matching results found.</div>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- Add Bootstrap JS link -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>