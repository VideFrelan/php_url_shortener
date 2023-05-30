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

// Function to generate QR code using API
function generateQRCode($url)
{
    $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
    $data = [
        'data' => $url,
        'size' => '200x200',
        'margin' => 0
    ];
    $queryString = http_build_query($data);
    $imageUrl = $apiUrl . '?' . $queryString;

    return $imageUrl;
}

// Handle the URL shortening process
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalURL = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $customURL = filter_input(INPUT_POST, 'custom_url', FILTER_SANITIZE_STRING);
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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

            // Generate QR code for the short URL
            $qrCodeImage = generateQRCode(BASE_URL . $shortURL);

            $stmt = $conn->prepare("INSERT INTO url_mappings (short_url, original_url, user_id, created_at, qr_code_image) VALUES (?, ?, ?, NOW(), ?)");

            if (!$stmt) {
                $error = "Preparation of prepared statement failed: " . $conn->error;
            } else {
                $stmt->bind_param("ssss", $shortURL, $originalURL, $userId, $qrCodeImage);

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

// Retrieve the shortened URL for the current user (if logged in)
$archivedURLs = array();
if (isset($_SESSION['user_id'])) {
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT id, short_url, original_url, views, created_at, qr_code_image FROM url_mappings WHERE user_id = ?");

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
}

// Handle URL deletion
if (isset($_GET['delete']) && $_GET['delete'] === 'true' && isset($_GET['id'])) {
    $urlId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($urlId) {
        $conn = connectToDatabase();

        $stmt = $conn->prepare("DELETE FROM url_mappings WHERE id = ? AND user_id = ?");

        if (!$stmt) {
            $error = "Preparation of prepared statement failed: " . $conn->error;
        } else {
            $stmt->bind_param("ii", $urlId, $_SESSION['user_id']);

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
    <style>
        .modal-container {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            visibility: hidden;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            max-width: 400px;
        }

        .modal-container.show {
            visibility: visible;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">URL Shortener</h1>
        <?php if (isset($_SESSION['user_id'])) { ?>
            <p class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="auth/logout.php">Logout</a></p>
        <?php } ?>
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
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <div class="mb-3">
                            <label for="custom_url" class="form-label">Custom URL (optional):</label>
                            <input type="text" class="form-control" name="custom_url" id="custom_url" placeholder="Enter custom URL">
                        </div>
                    <?php } ?>
                    <button type="submit" class="btn btn-primary">Shorten</button>
                </form>
                <?php if (!empty($error)) { ?>
                    <div class="alert alert-danger mt-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php } ?>
                <?php if (!isset($_SESSION['user_id'])) { ?>
                    <p class="mt-4">[!] You can <a href="auth/login.php">login</a> to access exclusive features such as:</p>
                    <ul>
                        <li>View the short URLs that you have created</li>
                        <li>Manage short URLs that you have created</li>
                        <li>Able to customize short URLs as you want</li>
                        <li>And more...</li>
                    </ul>
                <?php } ?>
            </div>
        </div>

        <?php if (isset($shortenedURL)) { ?>
            <div class="alert alert-success mt-4">
                Your shortened URL:
                <a href="<?php echo htmlspecialchars($shortenedURL); ?>"><?php echo htmlspecialchars($shortenedURL); ?></a>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['user_id'])) { ?>
            <div class="archived-urls">
                <h2 class="mt-5">Archived URLs</h2>
                <?php if (count($archivedURLs) > 0) { ?>
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Short URL</th>
                                <th>Original URL</th>
                                <th>Views</th>
                                <th>Created At</th>
                                <th>Action</th>
                                <th>QR Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivedURLs as $archivedURL) { ?>
                                <tr>
                                <td><a href="<?php echo htmlspecialchars(BASE_URL . $archivedURL['short_url']); ?>"><?php echo htmlspecialchars(BASE_URL . $archivedURL['short_url']); ?></a></td>
                                    <td><?php echo htmlspecialchars($archivedURL['original_url']); ?></td>
                                    <td><?php echo htmlspecialchars($archivedURL['views']); ?></td>
                                    <td><?php echo htmlspecialchars($archivedURL['created_at']); ?></td>
                                    <td>
                                        <a href="index.php?delete=true&id=<?php echo htmlspecialchars($archivedURL['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this URL?')">Delete</a>
                                    </td>
                                    <td>
                                        <?php if (!empty($archivedURL['qr_code_image'])) { ?>
                                            <button class="btn btn-primary" onclick="showQRCode('<?php echo htmlspecialchars($archivedURL['qr_code_image']); ?>')">Show QR Code</button>
                                        <?php } else { ?>
                                            <span>No QR Code</span>
                                            <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="mt-3">No archived URLs found.</p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <!-- QR Code Modal -->
    <div class="modal-container" id="qrCodeModal">
        <div class="modal-content">
            <h2>QR Code</h2>
            <div id="qrCodeImageContainer"></div>
            <button class="btn btn-primary mt-3" onclick="downloadQRCode()">Download QR Code</button>
            <button class="btn btn-secondary mt-3" onclick="closeQRCodeModal()">Close</button>
        </div>
    </div>

    <!-- Add Bootstrap JS link -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- QR Code Modal script -->
    <script>
        function showQRCode(qrCodeImage) {
            var qrCodeModal = document.getElementById('qrCodeModal');
            var qrCodeImageContainer = document.getElementById('qrCodeImageContainer');

            qrCodeImageContainer.innerHTML = '<img src="' + qrCodeImage + '">';

            qrCodeModal.classList.add('show');
        }

        function closeQRCodeModal() {
            var qrCodeModal = document.getElementById('qrCodeModal');

            qrCodeModal.classList.remove('show');
        }

        function downloadQRCode() {
            var qrCodeImage = document.getElementById('qrCodeImageContainer').getElementsByTagName('img')[0].src;

            var downloadLink = document.createElement('a');
            downloadLink.href = qrCodeImage;
            downloadLink.download = 'qr_code.png';
            downloadLink.click();
        }
    </script>
</body>
</html>