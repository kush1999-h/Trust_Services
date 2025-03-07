<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve posted data
$service_title = isset($_POST['service_title']) ? trim($_POST['service_title']) : '';
$description   = isset($_POST['description']) ? trim($_POST['description']) : '';
$price         = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

// We'll now read uploaded images directly into variables (as binary data)
// Initialize an array to hold the image binary data
$imageData = [
    'gig_image'      => null,  // first image (detailed gig image)
    'review_image1'  => null,  // second image (review/sample)
    'review_image2'  => null,  // third image (review/sample)
    'review_image3'  => null   // fourth image (review/sample)
];

// Check if images were uploaded
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $maxFiles = 4;
    $files = $_FILES['images'];
    for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
        // Check for file upload error
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            // Optionally, log error code here.
            continue;
        }
        // Read the file content as binary
        $fileContent = file_get_contents($files['tmp_name'][$i]);
        if ($fileContent === false) {
            continue;
        }
        // Map the first file to gig_image; subsequent files to review_image1, review_image2, etc.
        if ($i == 0) {
            $imageData['gig_image'] = $fileContent;
        } else {
            $index = $i; // i=1 maps to review_image1, etc.
            $imageData["review_image" . $index] = $fileContent;
        }
    }
}

// Prepare the SQL insert statement for the service_posts table.
// We'll use "s" (string) for the blob columns, which works if the images aren't huge.
$sql = "INSERT INTO service_posts (
            user_id, service_title, description, price, 
            gig_image, review_image1, review_image2, review_image3
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters: user_id (i), service_title (s), description (s), price (d)
// then blobs (as strings) for the images.
$stmt->bind_param(
    "issdssss", 
    $user_id, 
    $service_title, 
    $description, 
    $price, 
    $imageData['gig_image'], 
    $imageData['review_image1'], 
    $imageData['review_image2'], 
    $imageData['review_image3']
);

if ($stmt->execute()) {
    header("Location: feed.php");
    exit;
} else {
    echo "Error while saving service post: " . $stmt->error;
}
?>
