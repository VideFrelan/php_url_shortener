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
?>

<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedURLs as $archivedURL) { ?>
                            <tr>
                                <td><a href="<?php echo htmlspecialchars($archivedURL['short_url']); ?>"><?php echo htmlspecialchars($archivedURL['short_url']); ?></a></td>
                                <td><?php echo htmlspecialchars($archivedURL['original_url']); ?></td>
                                <td><?php echo htmlspecialchars($archivedURL['created_at']); ?></td>
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
