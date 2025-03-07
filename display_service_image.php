<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_config.php';

// Check required parameters
if (!isset($_GET['service_id']) || !isset($_GET['col'])) {
    exit('Missing parameters.');
}

$service_id = intval($_GET['service_id']);
$col = intval($_GET['col']);

// Determine which column to fetch based on "col" parameter
switch ($col) {
    case 1:
        $column = 'gig_image';
        break;
    case 2:
        $column = 'review_image1';
        break;
    case 3:
        $column = 'review_image2';
        break;
    case 4:
        $column = 'review_image3';
        break;
    default:
        exit('Invalid image column.');
}

// Prepare query
$sql = "SELECT $column FROM service_posts WHERE service_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    exit("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $service_id);
if (!$stmt->execute()) {
    exit("Execute failed: " . $stmt->error);
}
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($imgData);
    $stmt->fetch();

    if ($imgData && strlen($imgData) > 0) {
        // Log the length of the image data for debugging
        error_log("Image data length: " . strlen($imgData) . " bytes for service_id $service_id, column $column");
        
        // Set the Content-Type header.
        header("Content-Type: image/jpeg");
        
        // Output the image data.
        echo $imgData;
    } else {
        error_log("Image data is empty for service_id $service_id, column $column");
        exit("Image data is empty for service ID $service_id, column $column.");
    }
} else {
    error_log("No rows returned for service_id $service_id");
    exit("No image found for service ID $service_id.");
}

$stmt->close();
$conn->close();
?>
