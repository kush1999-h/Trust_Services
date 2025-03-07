<?php
// register.php
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve the POSTed form data
    $user_type    = $conn->real_escape_string($_POST['user_type']);
    $name         = $conn->real_escape_string($_POST['name']);
    $age          = $conn->real_escape_string($_POST['age']);
    // Use "phone_no" as contact number
    $contact_no   = $conn->real_escape_string($_POST['phone_no']);
    $email        = $conn->real_escape_string($_POST['email']);
    $password     = $conn->real_escape_string($_POST['password']);
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Initialize optional fields
    $address          = '';
    $occupation       = '';
    $company_name     = '';
    $service_provided = '';

    // Set additional fields based on user type
    if ($user_type === 'resident') {
        $address    = $conn->real_escape_string($_POST['address']);
        $occupation = $conn->real_escape_string($_POST['occupation']);
    } else {
        $company_name     = $conn->real_escape_string($_POST['company_name']);
        // For service providers, retrieve the service provided detail
        $service_provided = $conn->real_escape_string($_POST['service_provided']);
    }

    // Insert the new user into the "users" table (note the correct column names)
    $sql = "INSERT INTO users 
           (user_type, name, age, contact_no, email, password, address, occupation, company_name, service_provided)
           VALUES 
           ('$user_type', '$name', '$age', '$contact_no', '$email', '$hashed_password', '$address', '$occupation', '$company_name', '$service_provided')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                  alert('Registration successful.');
                  window.location.href = 'index.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
