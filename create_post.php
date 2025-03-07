<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize post content
    $content = $conn->real_escape_string($_POST['content']);

    // Insert the post into the posts table without images first
    $sql = "INSERT INTO posts (user_id, content) VALUES ($user_id, '$content')";
    if ($conn->query($sql) === TRUE) {
        $post_id = $conn->insert_id;
        
        // Prepare an array to hold image data (maximum 4 images)
        $images = array();
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $total_files = count($_FILES['images']['name']);
            // Limit to 4 images
            $total = min($total_files, 4);
            // Allowed MIME types for PNG, JPG, JPEG
            $allowed_types = array('image/jpeg', 'image/png');
            for ($i = 0; $i < $total; $i++) {
                $tmpFilePath = $_FILES['images']['tmp_name'][$i];
                $fileType = $_FILES['images']['type'][$i];
                // Validate file type
                if ($tmpFilePath != "" && in_array($fileType, $allowed_types)) {
                    // Read the file content as binary data
                    $images[$i] = file_get_contents($tmpFilePath);
                }
            }
        }
        
        // Set variables for each image column; if not provided, use NULL
        $img1 = isset($images[0]) ? $images[0] : null;
        $img2 = isset($images[1]) ? $images[1] : null;
        $img3 = isset($images[2]) ? $images[2] : null;
        $img4 = isset($images[3]) ? $images[3] : null;
        
        // If any image was uploaded, update the post row with the image data
        if ($img1 !== null || $img2 !== null || $img3 !== null || $img4 !== null) {
            $stmt = $conn->prepare("UPDATE posts SET blog_image1 = ?, blog_image2 = ?, blog_image3 = ?, blog_image4 = ? WHERE post_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            // Bind as strings ("s") for moderate-sized blobs, final parameter as integer.
            $stmt->bind_param("ssssi", $img1, $img2, $img3, $img4, $post_id);
            if (!$stmt->execute()) {
                die("Execute failed: " . $stmt->error);
            }
            $stmt->close();
        }
        
        header("Location: feed.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
