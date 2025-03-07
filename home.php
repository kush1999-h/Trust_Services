<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user's information from the database so we can use it in the page/modal.
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user = $user_result->fetch_assoc()) {
    $user_type = $user['user_type'];
} else {
    $user_type = "resident";
    $user = array();
}

// Determine a default profile picture.
$default_pic = 'https://cdn-icons-png.flaticon.com/512/9203/9203764.png';
$profile_pic = (isset($user['profile_pic']) && !empty($user['profile_pic'])) ? $user['profile_pic'] : $default_pic;

// Fetch all posts sorted by newest first.
$post_sql = "SELECT p.*, u.name, u.profile_pic FROM posts p 
             LEFT JOIN users u ON p.user_id = u.id 
             ORDER BY p.created_at DESC";
$post_result = $conn->query($post_sql);
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trust Services</title>
  
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
    button.btn.btn-default.dropdown-toggle {
      border-right: none;
    }
    input.form-control {
      outline: none;
      box-shadow: none;
    }
    input.form-control:focus {
      border-color: #333333;
      outline: none;
      box-shadow: none;
    }
    .btn-default-1 {
      background-color: #333333;
      border-color: #333333;
      color: white;
    }
    .btn-default-1:hover {
      background-color: #333333;
      border-color: #333333;
    }
    .glyphicon-search {
      color: white;
    }
    .dropdown-submenu .dropdown-menu {
      display: none;
      position: absolute;
      left: 100%;
      top: 0;
    }
    .dropdown-submenu:hover > .dropdown-menu {
      display: block;
    }
    .typing-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 10vh;
      text-align: center;
    }
    .typing-animation {
      display: inline-block;
      width: 29ch;
      border-right: 3px solid;
      animation: blink 1s step-end infinite, type 2.5s steps(29) forwards;
      overflow: hidden;
      white-space: nowrap;
      margin: 0 auto;
    }
    @keyframes blink { 50% { border-color: transparent; } }
    @keyframes type { 0% { width: 0; } 100% { width: 29ch; } }
    .textarea-container { position: relative; }
    .attachment-btn {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      z-index: 2;
      border-radius: 50%;
      background-color: #333333 !important;
      color: white !important;
      border: none !important;
      padding: 8px 12px;
      cursor: pointer;
    }
    .post-btn-container button {
      background-color: #333333 !important;
      color: white !important;
      border: none !important;
      padding: 10px 20px;
      cursor: pointer;
    }
    .post-btn-container { margin-top: 20px; text-align: center; }
    #fileInput { display: none; }
    textarea.form-control { padding-right: 40px; margin-bottom: 15px; }
    /* Blog/Post Section */
    .blog-section { width: 100%; max-width: 1200px; margin: 2rem auto; }
    .post-container {
      border-radius: 10px;
      background: #f9f9f9;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      padding: 20px;
    }
    .post-header { display: flex; align-items: center; margin-bottom: 1rem; }
    .post-author-pic {
      width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 1rem;
    }
    .post-author-info h5 { margin: 0; font-size: 1rem; }
    .post-author-info small { color: #888; }
    .post-body { margin-bottom: 1rem; }
    .post-text { margin-bottom: 1rem; font-size: 1rem; }
    .post-images { display: flex; gap: 1rem; }
    .post-images img { width: 48%; border-radius: 4px; object-fit: cover; }
    .post-comment-box input[type="text"] {
      width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
    }
    /* Service Section Styles */
    .service-section { margin: 2rem 0; }
    .service-item {
      margin-bottom: 20px;
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
    }
    .service-item img { width: 100%; height: auto; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
    /* Ensure each service item has a consistent size */
.service-item {
  height: 500px; /* Fixed height for each service item */
  width: 100%;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 20px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Force the image to a fixed height and use object-fit to cover */
.service-item .gig-img {
  width: 100%;
  height: 350px; /* Adjust as needed */
  object-fit: cover;
  border-bottom: 1px solid #ddd;
  margin-bottom: 10px;
}

/* Service content takes the remaining space */
.service-item .service-content {
  flex-grow: 1;
  overflow: hidden;
}

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
    <!-- Hero Section (if any) -->
    <section id="home" class="home-section home-fade home-full-height"></section>
    
    <section class="search-section">
  <div class="container">
    <div class="typing-container">
      <h2 class="typing-animation">Looking for something specific?</h2>
    </div>
    <div class="row">
      <div class="col-xs-8 col-xs-offset-2">
        <!-- Wrap the search inputs in a form -->
        <form action="search.php" method="GET">
          <div class="input-group">
            <div class="input-group-btn search-panel">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <span id="search_concept">All Categories</span> <span class="caret yellow"></span>
              </button>
              <ul class="dropdown-menu" role="menu">
                <li><a href="#" class="dropdown-item" data-value="Residents">Residents</a></li>
                <li class="dropdown-submenu">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Service Providers</a>
                  <ul class="dropdown-menu">
                    <li><a href="#" class="dropdown-item" data-value="Plumbers">Plumbers</a></li>
                    <li><a href="#" class="dropdown-item" data-value="Electricians">Electricians</a></li>
                    <li><a href="#" class="dropdown-item" data-value="Painters">Painters</a></li>
                    <li><a href="#" class="dropdown-item" data-value="Contractors">Contractors</a></li>
                  </ul>
                </li>
                <li class="divider"></li>
                <li><a href="#" class="dropdown-item" data-value="All Categories">All Categories</a></li>
              </ul>
            </div>
            <!-- Hidden field to store selected category -->
            <input type="hidden" name="search_param" value="all" id="search_param">
            <!-- Search query input -->
            <input type="text" class="form-control" name="x" placeholder="Search">
            <span class="input-group-btn">
              <button class="btn btn-default-1" type="submit">
                <span class="glyphicon glyphicon-search"></span>
              </button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
    <br><br>
    
    <!-- Post Section -->
    <section class="post-section">
      <div class="typing-container">
        <?php if ($user_type == 'resident'): ?>
          <h2 class="typing-animation">Want to Post something specific?</h2>
        <?php elseif ($user_type == 'service_provider'): ?>
          <h2 class="typing-animation">Want to Post your Service GIG?</h2>
        <?php endif; ?>
      </div>
      <div class="row">
        <?php if ($user_type == 'resident'): ?>
          <div class="col-xs-8 col-xs-offset-2">
            <!-- Resident Post Creation Form -->
            <form action="create_post.php" method="POST" enctype="multipart/form-data">
              <div class="form-group">
                <div class="textarea-container">
                  <textarea name="content" class="form-control" rows="4" placeholder="Write your post here..."></textarea>
                  <button class="btn btn-info attachment-btn" type="button" onclick="document.getElementById('fileInput').click();">
                    <span class="glyphicon glyphicon-paperclip"></span>
                  </button>
                  <input type="file" name="images[]" id="fileInput" style="display:none;" multiple />
                </div>
                <p style="font-size:12px; color:#777;">Maximum 4 images allowed.</p>
              </div>
              <div class="post-btn-container">
                <button class="btn btn-primary" type="submit">Post</button>
              </div>
            </form>
          </div>
        <?php elseif ($user_type == 'service_provider'): ?>
          <div class="col-xs-10 col-xs-offset-1">
            <!-- Service Provider Post Creation Trigger -->
            <div class="post-btn-container">
              <button class="btn btn-primary" data-toggle="modal" data-target="#postServices">
                Post About Services
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <br><br><br>
    
<!-- Service Gigs Section (visible only for residents) -->
<?php if ($user_type == 'resident'): ?>
    <section class="service-section">
      <div class="container">
        <h2 class="text-center font-alt">Service Gigs</h2>
        
        <!-- Filter Dropdown (centered) -->
        <div class="row" style="margin-bottom:20px;">
          <div class="col-md-6 col-md-offset-3">
            <form method="GET" id="serviceFilterForm">
              <!-- Preserve any other GET parameters if needed -->
              <input type="hidden" name="page" value="service_gigs">
              <div class="form-group">
                <label for="service_filter" style="text-align: center; display:block;">Filter by Service:</label>
                <select name="service_filter" id="service_filter" class="form-control">
                  <option value="" <?php if(!isset($_GET['service_filter']) || $_GET['service_filter'] == '') echo 'selected'; ?>>All Categories</option>
                  <option value="plumbing" <?php if(isset($_GET['service_filter']) && $_GET['service_filter'] == 'plumbing') echo 'selected'; ?>>Plumbing</option>
                  <option value="electrical" <?php if(isset($_GET['service_filter']) && $_GET['service_filter'] == 'electrical') echo 'selected'; ?>>Electrical</option>
                  <option value="cleaning" <?php if(isset($_GET['service_filter']) && $_GET['service_filter'] == 'cleaning') echo 'selected'; ?>>Cleaning</option>
                  <option value="gardening" <?php if(isset($_GET['service_filter']) && $_GET['service_filter'] == 'gardening') echo 'selected'; ?>>Gardening</option>
                </select>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-primary">Search</button>
              </div>
            </form>
          </div>
        </div>
        
        <div class="row">
          <?php
            // If a service filter is set, modify the query.
            if (isset($_GET['service_filter']) && !empty($_GET['service_filter'])) {
                $filter = strtolower($_GET['service_filter']);
                $service_sql = "SELECT sp.*, u.name AS provider_name, u.profile_pic AS provider_pic, u.contact_no AS provider_mobile
                                FROM service_posts sp 
                                LEFT JOIN users u ON sp.user_id = u.id 
                                WHERE LOWER(u.service_provided) = ?
                                ORDER BY sp.created_at DESC";
                $stmt = $conn->prepare($service_sql);
                $stmt->bind_param("s", $filter);
                $stmt->execute();
                $service_result = $stmt->get_result();
            } else {
                $service_sql = "SELECT sp.*, u.name AS provider_name, u.profile_pic AS provider_pic, u.contact_no AS provider_mobile
                                FROM service_posts sp 
                                LEFT JOIN users u ON sp.user_id = u.id 
                                ORDER BY sp.created_at DESC";
                $service_result = $conn->query($service_sql);
            }
            
            if ($service_result && $service_result->num_rows > 0):
              while ($service = $service_result->fetch_assoc()):
                // Set provider details
                $provider_name = !empty($service['provider_name']) ? $service['provider_name'] : "Unknown Provider";
                $provider_pic = !empty($service['provider_pic']) ? $service['provider_pic'] : $default_pic;
                $provider_mobile = !empty($service['provider_mobile']) ? $service['provider_mobile'] : "N/A";
          ?>
          <div class="col-md-4 col-sm-6">
            <div class="service-item"
                 data-serviceid="<?php echo $service['service_id']; ?>"
                 data-title="<?php echo htmlspecialchars($service['service_title']); ?>"
                 data-description="<?php echo htmlspecialchars($service['description']); ?>"
                 data-price="<?php echo htmlspecialchars($service['price']); ?>">
              <!-- Hidden container for provider info -->
              <span class="provider-info-hidden" style="display:none;"
                    data-provider-id="<?php echo htmlspecialchars($service['user_id']); ?>"
                    data-provider-name="<?php echo htmlspecialchars($provider_name); ?>"
                    data-provider-pic="<?php echo htmlspecialchars($provider_pic); ?>"
                    data-provider-mobile="<?php echo htmlspecialchars($provider_mobile); ?>">
              </span>
              <!-- Primary gig image loaded via display_service_image.php -->
              <img class="gig-img" src="display_service_image.php?service_id=<?php echo $service['service_id']; ?>&col=1" alt="<?php echo htmlspecialchars($service['service_title']); ?>">
              <div class="service-content">
                <h4><?php echo htmlspecialchars($service['service_title']); ?></h4>
                <p><strong>Price: &#2547; <?php echo htmlspecialchars($service['price']); ?></strong></p>
              </div>
            </div>
          </div>
          <?php endwhile; else: ?>
            <p class="text-center">No service gigs available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
<?php endif; ?>




    
    <br>
    
    <!-- Video Section -->
    <section class="video-section module module-video bg-dark-30" data-background="">
      <div class="container">
        <div class="row">
          <div class="col-sm-8 col-sm-offset-3">
            <h2 class="module-title font-alt mb-0">Your home, our expertise. Your Trust is Our Priority.</h2>
          </div>
        </div>
      </div>
      <div class="video-player" data-property="{videoURL:'https://www.youtube.com/watch?v=IqmcSpQqenY', containment:'.module-video', startAt:0, mute:true, autoPlay:true, loop:true, opacity:1, showControls:false, showYTLogo:false, vol:25}"></div>
    </section>
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
  
  <!-- Scroll Up Button (optional) -->
  <div class="scroll-up">
    <a href="#totop"><i class="fa fa-angle-double-up"></i></a>
  </div>
  
  <!-- jQuery and Bootstrap Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
  <script>
    // Use event delegation to handle dropdown item clicks
    $(document).on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      var selectedText = $(this).text();
      $('#search_concept').text(selectedText);
      $('#search_param').val($(this).data('value'));
    });
    
    // Limit the number of file selections for the resident form
    if(document.getElementById('fileInput')){
      document.getElementById('fileInput').addEventListener('change', function(){
        if (this.files.length > 4) {
          alert("You can only upload a maximum of 4 images.");
          this.value = "";
        }
      });
    }
    
    $(document).ready(function(){
  $('.service-item').click(function(){
    var providerId = $(this).find('.provider-info-hidden').data('provider-id');
    var title = $(this).data('title');
    var description = $(this).data('description');
    var price = $(this).data('price');
    var providerName = $(this).find('.provider-info-hidden').data('provider-name');
    var providerPic = $(this).find('.provider-info-hidden').data('provider-pic');
    var providerMobile = $(this).find('.provider-info-hidden').data('provider-mobile');
    
    // Populate modal fields accordingly
    $('#serviceModalLabel').text(title);
    $('#modalDescription').text(description);
    $('#modalPrice').text(price);
    $('#serviceModal .provider-info')
        .attr('data-provider-id', providerId)
        .find('#providerName').text(providerName);
    $('#providerPic').attr('src', providerPic);
    $('#providerMobile').text("Contact: " + providerMobile);
    
    // Show modal
    $('#serviceModal').modal('show');
  });
});

  </script>
  <script>
$(document).ready(function() {
  $('.dropdown-item').click(function(e) {
    e.preventDefault();
    var selectedText = $(this).text();
    // Update the displayed category
    $('#search_concept').text(selectedText);
    // Update the hidden input value to send to the server
    $('#search_param').val($(this).data('value'));
  });
});
</script>
  
  <!-- Modal for Service Provider Post -->
  <div class="modal fade" id="postServices" tabindex="-1" role="dialog" aria-labelledby="postServices">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="service_post.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="postServices">Service Post Details</h4>
          </div>
          <div class="modal-body">
            <!-- Instruction Block -->
            <div class="alert alert-info">
              <p><strong>Service Gig Instructions:</strong></p>
              <p>
                Please provide a clear service title and detailed description. Include your pricing information, and upload up to 4 images.<br>
                <strong>Note:</strong> The <em>first image</em> must be a detailed gig image (like on Fiverr), while images <em>2, 3, and 4</em> can be used for reviews or samples of your work.
              </p>
            </div>
            <!-- Service Title -->
            <div class="form-group">
              <label for="service_title">Service Title:</label>
              <input type="text" name="service_title" class="form-control" required>
            </div>
            <!-- Description -->
            <div class="form-group">
              <label for="description">Description:</label>
              <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <!-- Price -->
            <div class="form-group">
              <label for="price">Price (&#2547;):</label>
              <input type="number" name="price" class="form-control" required>
            </div>
            <!-- Image Uploader -->
            <div class="form-group">
              <label for="service_images">Upload Images:</label>
              <input type="file" name="images[]" id="service_images" class="form-control" multiple>
              <small>
                Maximum 4 images allowed. The <strong>first image</strong> must be a detailed gig image (like on Fiverr). 
                Images <strong>2, 3, and 4</strong> can be review images or work samples.
              </small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Post Service Update</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
<!-- Modal for Service Gig Details -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="service_post.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="serviceModalLabel">Service Gig Details</h4>
        </div>
        <div class="modal-body">
          <!-- Provider Info: Data attribute for provider id will be set dynamically -->
          <div class="provider-info" style="display:flex; align-items:center; margin-bottom:10px;" data-provider-id="">
            <img id="providerPic" src="" alt="Provider Pic" style="width:50px; height:50px; border-radius:50%; margin-right:10px;">
            <div>
              <span id="providerName"></span><br>
              <span id="providerMobile" style="font-size:14px; color:#555;"></span>
            </div>
          </div>
          <!-- Service Images: Bootstrap Carousel -->
          <div class="service-images">
            <div id="serviceCarousel" class="carousel slide" data-ride="carousel">
              <!-- Indicators -->
              <ol class="carousel-indicators" id="carouselIndicators">
                <li data-target="#serviceCarousel" data-slide-to="0" class="active"></li>
                <li data-target="#serviceCarousel" data-slide-to="1"></li>
                <li data-target="#serviceCarousel" data-slide-to="2"></li>
                <li data-target="#serviceCarousel" data-slide-to="3"></li>
              </ol>
              <!-- Carousel Items -->
              <div class="carousel-inner" role="listbox">
                <div class="item active">
                  <img id="carouselImage1" src="display_service_image.php?service_id=1&col=1" alt="Gig Image" style="width:100%; height:auto; max-height:600px; object-fit:contain;">
                </div>
                <div class="item">
                  <img id="carouselImage2" src="display_service_image.php?service_id=1&col=2" alt="Review Image 1" style="width:100%; height:auto; max-height:600px; object-fit:contain;">
                </div>
                <div class="item">
                  <img id="carouselImage3" src="display_service_image.php?service_id=1&col=3" alt="Review Image 2" style="width:100%; height:auto; max-height:600px; object-fit:contain;">
                </div>
                <div class="item">
                  <img id="carouselImage4" src="display_service_image.php?service_id=1&col=4" alt="Review Image 3" style="width:100%; height:auto; max-height:600px; object-fit:contain;">
                </div>
              </div>
              <!-- Controls -->
              <a class="left carousel-control" href="#serviceCarousel" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
              </a>
              <a class="right carousel-control" href="#serviceCarousel" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
              </a>
            </div>
          </div>
          <p id="modalDescription"></p>
          <p><strong>Price: &#2547; <span id="modalPrice"></span></strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <!-- Contact Button -->
          <button type="button" class="btn btn-primary" id="contactBtn" onclick="startChat()">Contact</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JavaScript: Set provider ID and other modal fields when service item is clicked -->
<script>
  $(document).ready(function(){
    $('.service-item').click(function(){
      var providerId = $(this).data('provider-id'); // Must be set on the service item
      var title = $(this).data('title');
      var description = $(this).data('description');
      var price = $(this).data('price');
      var providerName = $(this).data('provider-name');
      var providerPic = $(this).data('provider-pic');
      var providerMobile = $(this).data('provider-mobile');
      
      // Populate modal fields
      $('#serviceModalLabel').text(title);
      $('#modalDescription').text(description);
      $('#modalPrice').text(price);
      $('#serviceModal .provider-info').attr('data-provider-id', providerId)
          .find('#providerName').text(providerName);
      $('#providerPic').attr('src', providerPic);
      $('#providerMobile').text("Contact: " + providerMobile);
      
      // (Optionally update carousel images here based on service_id)
      
      // Show modal
      $('#serviceModal').modal('show');
    });
  });
  
  // Function to start chat
  function startChat() {
    var providerId = $('#serviceModal .provider-info').attr('data-provider-id');
    if (providerId) {
      window.location.href = 'chat.php?chat_with=' + providerId;
    } else {
      alert("Provider ID is not available.");
    }
  }
</script>

</body>
</html>
