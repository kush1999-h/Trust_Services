<?php
session_start();
include 'db_config.php';

// Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    exit("Not logged in.");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($receiver_id <= 0 || empty($message)) {
    exit("Missing parameters.");
}

$sql = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    exit("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);
if ($stmt->execute()) {
    echo "Message sent";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>

