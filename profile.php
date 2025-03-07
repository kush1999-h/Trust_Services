<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the logged-in user's information
$user_sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$user_result = $conn->query($user_sql);
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_type = $user['user_type'];
} else {
    die("User not found.");
}

// Determine profile picture (use default if none provided)
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://cdn-icons-png.flaticon.com/512/9203/9203764.png';

// Run a different query based on user type:
if ($user_type == 'resident') {
    // For residents, fetch their blog posts.
    $post_sql = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC";
} else {
    // For service providers, fetch their service gigs.
    // Also join the users table to get provider details (e.g., name, profile_pic).
    $post_sql = "SELECT sp.*, u.name AS provider_name, u.profile_pic AS provider_pic 
                 FROM service_posts sp 
                 LEFT JOIN users u ON sp.user_id = u.id 
                 WHERE sp.user_id = $user_id 
                 ORDER BY sp.created_at DESC";
}
$post_result = $conn->query($post_sql);
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trust Services - Profile</title>
  <style>
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
    /* Navbar styling */
    .navbar-custom { background-color: #333; }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav > li > a { color: #fff; }
    .navbar-custom .navbar-toggle { border-color: rgba(255,255,255,0.1); }
    .navbar-custom .navbar-toggle .icon-bar { background-color: #fff; }
    /* Profile Container */
    .profile-container {
      display: flex;
      justify-content: space-between;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      background-color: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }
    .profile-container img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      flex: 1;
      margin-right: 60px;
    }
    .profile-info {
      flex: 10;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      margin-right: 20px;
    }
    .profile-info h3 { margin: 0; font-size: 24px; font-weight: bold; }
    .profile-info p { font-size: 16px; color: #555; margin: 5px 0; }
    .profile-buttons {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: flex-start;
    }
    .btn-edit-profile, .btn-logout {
      background-color: #333;
      color: white;
      border: none;
      padding: 10px 20px;
      cursor: pointer;
      border-radius: 5px;
      margin-top: 20px;
      width: 100%;
    }
    .btn-edit-profile:hover, .btn-logout:hover { background-color: #555; }
    /* Blog/Post Section */
    .blog-section { width: 100%; max-width: 1200px; margin: 2rem auto; }
    .post-container {
      background: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      padding: 20px;
    }
    .post-header { display: flex; align-items: center; margin-bottom: 1rem; }
    .post-author-pic { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 1rem; }
    .post-author-info h5 { margin: 0; font-size: 1rem; }
    .post-author-info small { color: #888; }
    .post-body { margin-bottom: 1rem; }
    .post-text { margin-bottom: 1rem; font-size: 1rem; }
    .post-images { display: flex; gap: 1rem; }
    .post-images img { width: 48%; border-radius: 4px; object-fit: cover; }
    /* Responsive Styling */
    @media (max-width: 768px) {
      .profile-container { flex-direction: column; text-align: center; }
      .profile-container img { margin-right: 0; margin-bottom: 10px; }
      .profile-info, .profile-buttons { align-items: center; }
    }
  </style>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
  <!-- Header / Navbar -->
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
  
  <!-- Main Content -->
  <main class="main">
  <div class="container">
    <!-- Profile Section -->
    <section class="profile-section">
      <div class="profile-container">
        <!-- Profile Image -->
        <img src="<?php echo $profile_pic; ?>" alt="Profile Picture">
        <!-- Profile Info -->
        <div class="profile-info">
          <?php if ($user['user_type'] == 'resident'): ?>
            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <h4><strong>Address</strong></h4>
            <p><?php echo htmlspecialchars($user['address']); ?></p>
            <p><strong>Occupation:</strong> <?php echo htmlspecialchars($user['occupation']); ?></p>
          <?php else: ?>
            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <h4><strong>Company Information</strong></h4>
            <p><strong>Company Name:</strong> <?php echo htmlspecialchars($user['company_name']); ?></p>
            <p><strong>Service Provided:</strong> <?php echo htmlspecialchars($user['service_provided']); ?></p>
          <?php endif; ?>
        </div>
        <!-- Profile Buttons -->
        <div class="profile-buttons">
          <button class="btn-edit-profile" data-toggle="modal" data-target="#editProfileModal">Edit Profile</button>
          <button class="btn-logout" onclick="window.location.href='logout.php'">Logout</button>
        </div>
      </div>
    </section>

    <!-- Posts/Service Gigs Section -->
    <?php if ($user['user_type'] == 'resident'): ?>
      <!-- Blog/Post Section for Residents -->
      <section class="blog-section">
        <?php if ($post_result && $post_result->num_rows > 0): ?>
          <?php while ($post = $post_result->fetch_assoc()): ?>
            <div class="post-container">
              <!-- Post Header -->
              <div class="post-header">
                <img class="post-author-pic" src="<?php echo $profile_pic; ?>" alt="User Pic" />
                <div class="post-author-info">
                  <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                  <small><?php echo date("M d, Y H:i", strtotime($post['created_at'])); ?></small>
                </div>
              </div>
              <!-- Post Body -->
              <div class="post-body">
                <p class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php 
                // Display images if available.
                if (!is_null($post['blog_image1']) || !is_null($post['blog_image2']) || !is_null($post['blog_image3']) || !is_null($post['blog_image4'])): ?>
                  <div class="post-images">
                    <?php if (!is_null($post['blog_image1'])): ?>
                      <img src="display_image.php?post_id=<?php echo $post['post_id']; ?>&col=1" alt="Post Image 1" />
                    <?php endif; ?>
                    <?php if (!is_null($post['blog_image2'])): ?>
                      <img src="display_image.php?post_id=<?php echo $post['post_id']; ?>&col=2" alt="Post Image 2" />
                    <?php endif; ?>
                    <?php if (!is_null($post['blog_image3'])): ?>
                      <img src="display_image.php?post_id=<?php echo $post['post_id']; ?>&col=3" alt="Post Image 3" />
                    <?php endif; ?>
                    <?php if (!is_null($post['blog_image4'])): ?>
                      <img src="display_image.php?post_id=<?php echo $post['post_id']; ?>&col=4" alt="Post Image 4" />
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
              <!-- (Optional: Comments Section can be added here) -->
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center">There are no posts yet.</p>
        <?php endif; ?>
      </section>
    <?php else: ?>
      <!-- Service Gig Section for Service Providers -->
      <?php
        // For service providers, the $post_result query (from service_posts) should already be set.
        // You might have a different query for service posts.
      ?>
      <section class="blog-section">
        <?php if ($post_result && $post_result->num_rows > 0): ?>
          <?php while ($service = $post_result->fetch_assoc()): ?>
            <div class="post-container">
              <!-- Service Gig Header: Provider Info & Timestamp -->
              <div class="post-header">
                <?php
                  $provider_pic = !empty($service['provider_pic']) ? $service['provider_pic'] : $profile_pic;
                ?>
                <img class="post-author-pic" src="<?php echo htmlspecialchars($provider_pic); ?>" alt="Provider Pic" />
                <div class="post-author-info">
                  <h5><?php echo htmlspecialchars($service['provider_name']); ?></h5>
                  <small><?php echo date("M d, Y H:i", strtotime($service['created_at'])); ?></small>
                </div>
              </div>
              <!-- Service Gig Body: Title, Description, Price & Images -->
              <div class="post-body">
                <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                <p class="post-text"><strong>Price: &#2547; <?php echo htmlspecialchars($service['price']); ?></strong></p>
                <!-- Post Images -->
                <div class="post-images">
                  <!-- Primary Gig Image (col=1) -->
                  <img src="display_service_image.php?service_id=<?php echo $service['service_id']; ?>&col=1" alt="Gig Image" />
                  <!-- Additional Review Images -->
                  <?php if (!empty($service['review_image1'])): ?>
                    <img src="display_service_image.php?service_id=<?php echo $service['service_id']; ?>&col=2" alt="Review Image 1" />
                  <?php endif; ?>
                  <?php if (!empty($service['review_image2'])): ?>
                    <img src="display_service_image.php?service_id=<?php echo $service['service_id']; ?>&col=3" alt="Review Image 2" />
                  <?php endif; ?>
                  <?php if (!empty($service['review_image3'])): ?>
                    <img src="display_service_image.php?service_id=<?php echo $service['service_id']; ?>&col=4" alt="Review Image 3" />
                  <?php endif; ?>
                </div>
              </div>
              <!-- (Optional: Additional service gig details can be added here) -->
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center">You have not posted any service gigs yet.</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </div>
</main>

  
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
  
  <!-- Scroll Up Button -->
  <div class="scroll-up">
    <a href="#totop"><i class="fa fa-angle-double-up"></i></a>
  </div>
  
  <!-- Scripts: Load only one version of jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
</body>
</html>
