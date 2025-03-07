<?php
session_start();
include 'db_config.php';

// Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    exit("Not logged in.");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : 0;

if ($receiver_id <= 0) {
    exit("Missing parameters.");
}

$sql = "SELECT * FROM chat_messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    exit("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = array();
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();
header("Content-Type: application/json");
echo json_encode($messages);
?>

