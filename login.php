<?php
// login.php
include 'db_config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize login inputs using email and password.
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Look up the user by email.
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         // Verify the provided password against the stored hash.
         if (password_verify($password, $row['password'])) {
             // Set session variables upon successful login.
             $_SESSION['user_id'] = $row['id'];
             $_SESSION['name']    = $row['name'];
             
             // Redirect to your home page.
             header("Location: profile.php");
             exit;
         } else {
             // Wrong password: show an alert and reload the login page.
             echo "<script>
                      alert('Invalid password.');
                      window.location.href = 'index.php';
                   </script>";
             exit;
         }
    } else {
         // No user found: show an alert and reload the login page.
         echo "<script>
                  alert('No user found with that email.');
                  window.location.href = 'index.php';
               </script>";
         exit;
    }
}
?>
