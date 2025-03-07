<?php
// db_config.php

$servername = "localhost";
$username = "root";
$password = "";  // default XAMPP password is empty
$dbname = "dohsservices";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and handle any connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
