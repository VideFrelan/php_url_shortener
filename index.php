<?php
session_start();
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalURL = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $customURL = filter_input(INPUT_POST, 'custom_url', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];

    // Filtering insterted URL
    if (!filter_var($originalURL, FILTER_VALIDATE_URL)) {
        die("Invalid URL.");
    }

    // Create a short URL
    $shortURL = (empty($customURL)) ? generateShortURL() : $customURL;

    $conn = connectToDatabase();
    $stmt = $conn->prepare("INSERT INTO url_mappings (short_url, original_url, user_id) VALUES (?, ?, ?)");

    if (!$stmt) {
        die("Preparation of prepared statement failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $shortURL, $originalURL, $userId);

    if ($stmt->execute()) {
        $shortenedURL = BASE_URL . $shortURL;
    } else {
        die("Execution of prepared statement failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}

// Retrieve the shortened URL for the current user
$conn = connectToDatabase();
$stmt = $conn->prepare("SELECT id, short_url, original_url FROM url_mappings WHERE user_id = ?");

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .container {
            margin-top: 50px;
        }

        .shorten-form {
            margin-bottom: 30px;
        }

        .archived-urls {
            margin-top: 50px;
        }
    </style>
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
                    <div class="form-group">
                        <label for="url">URL:</label>
                        <input type="text" class="form-control" name="url" id="url" placeholder="Enter URL" required>
                    </div>
                    <div class="form-group">
                        <label for="custom_url">Custom URL (optional):</label>
                        <input type="text" class="form-control" name="custom_url" id="custom_url" placeholder="Enter custom URL">
                    </div>
                    <button type="submit" class="btn btn-primary">Shorten</button>
                </form>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedURLs as $archivedURL) { ?>
                            <tr>
                                <td><a href="<?php echo htmlspecialchars($archivedURL['short_url']); ?>"><?php echo htmlspecialchars($archivedURL['short_url']); ?></a></td>
                                <td><?php echo htmlspecialchars($archivedURL['original_url']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No URLs found.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>