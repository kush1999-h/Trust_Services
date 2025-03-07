<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve and sanitize inputs
$name       = $conn->real_escape_string($_POST['name']);
$age        = $conn->real_escape_string($_POST['age']);
$contact_no = $conn->real_escape_string($_POST['contact_no']);
$email      = $conn->real_escape_string($_POST['email']);

// Determine fields based on user type (assuming user type does not change)
$user_type = ''; // Optional: You might fetch this from session or database.
$user_sql = "SELECT user_type FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($user_sql);
if ($result && $row = $result->fetch_assoc()) {
    $user_type = $row['user_type'];
}

if ($user_type == 'resident') {
    $address    = $conn->real_escape_string($_POST['address']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
} else {
    $company_name     = $conn->real_escape_string($_POST['company_name']);
    $service_provided = $conn->real_escape_string($_POST['service_provided']);
}

// Handling file upload for profile picture (if a new file is provided)
$profile_pic_sql = "";
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    // You can adjust your file saving logic here.
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        $profile_pic_sql = ", profile_pic = '" . $conn->real_escape_string($target_file) . "'";
    }
}

// Build the update query based on user type
if ($user_type == 'resident') {
    $update_sql = "UPDATE users SET name='$name', age='$age', contact_no='$contact_no', email='$email', address='$address', occupation='$occupation' $profile_pic_sql WHERE id=$user_id";
} else {
    $update_sql = "UPDATE users SET name='$name', age='$age', contact_no='$contact_no', email='$email', company_name='$company_name', service_provided='$service_provided' $profile_pic_sql WHERE id=$user_id";
}

if ($conn->query($update_sql) === TRUE) {
    header("Location: profile.php");
    exit;
} else {
    echo "Error updating profile: " . $conn->error;
}

$conn->close();
?>
