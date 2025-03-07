<?php
session_start();
include 'db_config.php';

if (isset($_GET['post_id']) && isset($_GET['col'])) {
    $post_id = intval($_GET['post_id']);
    $col = intval($_GET['col']);
    if ($col < 1 || $col > 4) {
        exit("Invalid image column.");
    }
    $colName = "blog_image" . $col;
    $sql = "SELECT $colName FROM posts WHERE post_id = $post_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageData = $row[$colName];
        if ($imageData) {
            // For demonstration, we assume JPEG images.
            header("Content-Type: image/jpeg");
            echo $imageData;
        }
    }
}
?>
