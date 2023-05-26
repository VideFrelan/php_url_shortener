<?php
require_once('configuration/config.php');

if (isset($_GET['redirecturl'])) {
    $shortURL = $_GET['redirecturl'];
    $conn = connectToDatabase();
    $stmt = $conn->prepare("SELECT original_url FROM url_mappings WHERE short_url = ?");

    if (!$stmt) {
        die("Preparation of prepared statement failed: " . $conn->error);
    }

    $stmt->bind_param("s", $shortURL);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $originalURL = $row['original_url'];
            header("Location: $originalURL");
            exit();
        } else {
            die("Short URL not found.");
        }
    } else {
        die("Execution of prepared statement failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    die("Invalid URL.");
}
?>
