<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment']);

    // Basic validation: ensure comment is not empty
    if (!empty($comment)) {
        $comment = $conn->real_escape_string($comment);
        $sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ($post_id, $user_id, '$comment')";
        if ($conn->query($sql) === TRUE) {
            // Redirect back to the feed or the post page after successful comment submission.
            header("Location: feed.php");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // If comment is empty, redirect back
        header("Location: feed.php");
        exit;
    }
}
?>
