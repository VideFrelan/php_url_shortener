<?php
session_start();
error_reporting(E_ALL);
require_once('configuration/config.php');

// Function to shorten URLs randomly
function generateShortURL()
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $shortURL = '';

    for ($i = 0; $i < 6; $i++) {
        $shortURL .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $shortURL;
}

// Check if user is not logged in yet, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Handle the URL shortening process
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalURL = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $customURL = filter_input(INPUT_POST, 'custom_url', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];

    // Filtering inserted URL
    if (!filter_var($originalURL, FILTER_VALIDATE_URL)) {
        $error = "Invalid URL.";
    } else {
        // Check if the custom URL or randomly generated short URL already exists in the database
        $conn = connectToDatabase();

        if (!empty($customURL)) {
            $stmt = $conn->prepare("SELECT short_url FROM url_mappings WHERE short_url = ? LIMIT 1");
            if ($conn->error) {
                $error = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $customURL);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Custom URL already exists. Please use a different one.";
                }

                $stmt->close();
            }
        }

        if (empty($error)) {
            // Create a short URL
            $shortURL = (empty($customURL)) ? generateShortURL() : $customURL;

            $stmt = $conn->prepare("INSERT INTO url_mappings (short_url, original_url, user_id, created_at) VALUES (?, ?, ?, NOW())");

            if (!$stmt) {
                $error = "Preparation of prepared statement failed: " . $conn->error;
            } else {
                $stmt->bind_param("sss", $shortURL, $originalURL, $userId);

                if ($stmt->execute()) {
                    $shortenedURL = BASE_URL . $shortURL;
                } else {
                    $error = "Execution of prepared statement failed: " . $stmt->error;
                }

                $stmt->close();
            }
        }

        $conn->close();
    }
}

// Retrieve the shortened URL for the current user
$conn = connectToDatabase();

$stmt = $conn->prepare("SELECT id, short_url, original_url, created_at FROM url_mappings WHERE user_id = ?");

if (!$stmt) {
    die("Preparation of prepared statement failed: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $archivedURLs = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Execution of prepared statement failed: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Handle URL deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $deleteId = $_GET['id'];
    $conn = connectToDatabase();
    $stmt = $conn->prepare("DELETE FROM url_mappings WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        $error = "Preparation of prepared statement failed: " . $conn->error;
    } else {
        $stmt->bind_param("ii", $deleteId, $_SESSION['user_id']);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Execution of prepared statement failed: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener</title>
    <!-- Used to control the appearance of web pages to fit the screen width of the user's device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Bootstrap CSS link -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Specifies the character encoding of the web page (usually using UTF-8). -->
    <meta charset="utf-8">
    <!-- Provides a brief description of the content of a web page for search engine purposes -->
    <meta name="description" content="URL Shortener is a tool to shorten long URLs and make them more manageable.">
    <!-- Determines keywords related to web pages for search engine purposes -->
    <meta name="keywords" content="URL shortener, short URLs, link shortener, web tools">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">URL Shortener</h1>
        <p class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="auth/logout.php">Logout</a></p>

        <div class="card shorten-form">
            <div class="card-header">
                <h2>Shorten a URL</h2>
            </div>
            <div class="card-body">
                <form action="index.php" method="post">
                    <div class="mb-3">
                        <label for="url" class="form-label">URL:</label>
                        <input type="text" class="form-control" name="url" id="url" placeholder="Enter URL" required>
                    </div>
                    <div class="mb-3">
                        <label for="custom_url" class="form-label">Custom URL (optional):</label>
                        <input type="text" class="form-control" name="custom_url" id="custom_url" placeholder="Enter custom URL">
                    </div>
                    <button type="submit" class="btn btn-primary">Shorten</button>
                </form>
                <?php if (!empty($error)) { ?>
                    <div class="alert alert-danger mt-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php if (isset($shortenedURL)) { ?>
            <div class="alert alert-success mt-4">
                Your shortened URL:
                <a href="<?php echo htmlspecialchars($shortenedURL); ?>"><?php echo htmlspecialchars($shortenedURL); ?></a>
            </div>
        <?php } ?>

        <div class="archived-urls">
            <h2 class="mt-5">Archived URLs</h2>
            <?php if (count($archivedURLs) > 0) { ?>
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Short URL</th>
                            <th>Original URL</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedURLs as $archivedURL) { ?>
                            <tr>
                                <td><a href="<?php echo htmlspecialchars($archivedURL['short_url']); ?>"><?php echo htmlspecialchars($archivedURL['short_url']); ?></a></td>
                                <td><?php echo htmlspecialchars($archivedURL['original_url']); ?></td>
                                <td><?php echo htmlspecialchars($archivedURL['created_at']); ?></td>
                                <td>
                                    <a href="index.php?delete=true&id=<?php echo htmlspecialchars($archivedURL['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this URL?')">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No URLs found.</p>
            <?php } ?>
        </div>
    </div>
    <!-- Add Bootstrap JS scripts (jQuery and Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
</body>
</html>