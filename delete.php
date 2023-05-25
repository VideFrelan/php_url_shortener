<?php
require_once 'configuration/config.php';

// Check if user is logged in and is an admin
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: auth/login.php');
    exit();
}

// Check if URL ID is provided
if (!isset($_GET['url_id'])) {
    header('Location: index.php');
    exit();
}

$urlId = $_GET['url_id'];

$conn = connectToDatabase();

// Delete the URL from the database
$stmt = $conn->prepare("DELETE FROM urls WHERE id = ?");
$stmt->bind_param("i", $urlId);
$stmt->execute();
$stmt->close();

$conn->close();

header('Location: index.php');
exit();
?>
