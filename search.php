<?php
session_start();
include 'db_config.php';

// Check if a search query is provided
if (!isset($_GET['x']) || empty(trim($_GET['x']))) {
    echo "No search query provided.";
    exit;
}

// Retrieve search query and category parameter
$searchQuery = trim($_GET['x']);
$searchParam = isset($_GET['search_param']) ? $_GET['search_param'] : 'all';

// Define a mapping for service provider search values to database values.
$serviceMapping = array(
    'Plumbers'     => 'plumbing',
    'Electricians' => 'electrical',
    'Painters'     => 'cleaning',
    'Contractors'  => 'gardening'
);

// Build the base SQL query to search the users table by name.
$sql = "SELECT id, name, profile_pic, user_type, service_provided 
        FROM users 
        WHERE name LIKE ?";
$params = array("%" . $searchQuery . "%");

// If the category is not "All Categories", add filtering conditions.
if (strtolower($searchParam) !== 'all categories' && strtolower($searchParam) !== 'all') {
    if (strtolower($searchParam) === 'residents') {
        // Filter for residents
        $sql .= " AND user_type = 'resident'";
    } else {
        // For service provider categories, look up the mapping.
        if (isset($serviceMapping[$searchParam])) {
            $mappedValue = $serviceMapping[$searchParam];
            $sql .= " AND LOWER(service_provided) = ?";
            $params[] = strtolower($mappedValue);
        }
    }
}

$sql .= " ORDER BY name ASC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Dynamically bind parameters based on the number of parameters
$bindTypes = str_repeat("s", count($params));
$stmt->bind_param($bindTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results - Trust Services</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      padding: 20px;
    }
    .result-item {
      display: flex;
      align-items: center;
      padding: 10px;
      border-bottom: 1px solid #ddd;
      text-decoration: none;
      color: inherit;
    }
    .result-item:hover {
      background-color: #f0f0f0;
    }
    .result-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 15px;
    }
    .result-details h4 {
      margin: 0;
      font-size: 1.1rem;
    }
    /* General Body Styling */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
    }
    body { font-family: 'Arial', sans-serif; }
    .main {
      flex: 1;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .text-center { text-align: center; margin-bottom: 20px; }
    .font-alt { font-size: 24px; font-weight: bold; color: #333; }
    .navbar-custom { background-color: #333; }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav > li > a { color: #fff; }
    .navbar-custom .navbar-toggle { border-color: rgba(255,255,255,0.1); }
    .navbar-custom .navbar-toggle .icon-bar { background-color: #fff; }
    footer.site-footer {
      position: relative;
      bottom: 0;
      width: 100%;
      background-color: #333;
      color: white;
      text-align: center;
      padding: 10px;
      margin-top: auto;
    }
    footer.site-footer .container { max-width: 1200px; margin: 0 auto; }
    .footer p { margin: 0; text-align: left; }
    .footer .footer-social-links { text-align: right; }
    .footer .footer-social-links a { display: inline-block; padding: 0 6px; }
    .starter-template {
      padding: 40px 15px;
      text-align: center;
    }
  </style>
</head>
<br>

<header>
    <nav class="navbar navbar-custom navbar-fixed-top navbar-transparent" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#custom-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="home.php" style="width: auto;">Trust Services</a>
        </div>
        <div class="collapse navbar-collapse" id="custom-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="home.php">Home</a></li>
            <li><a href="feed.php">Feed</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li><a href="profile.php">Profile</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
  <br data-spy="scroll" data-target=".onpage-navigation" data-offset="60">
  </br>
  <div class="container">
    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
    <?php if ($result->num_rows > 0): ?>
      <?php while ($user = $result->fetch_assoc()): ?>
        <a class="result-item" href="chat.php?chat_with=<?php echo $user['id']; ?>">
          <img class="result-avatar" src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'https://cdn-icons-png.flaticon.com/512/9203/9203764.png'; ?>" alt="Avatar">
          <div class="result-details">
            <h4><?php echo htmlspecialchars($user['name']); ?></h4>
          </div>
        </a>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No profiles found matching your search.</p>
    <?php endif; ?>
  </div>
  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-left">
          <p style="margin: 0;">
            Copyright &copy; <?php echo date("Y"); ?> All Rights Reserved by 
            <a href="https://www.facebook.com/profile.php?id=100076790015696">MIST</a>.
          </p>
        </div>
      </div>
    </div>
  </footer>
  
  <!-- Scroll Up Button (optional) -->
  <div class="scroll-up">
    <a href="#totop"><i class="fa fa-angle-double-up"></i></a>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
