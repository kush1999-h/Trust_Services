<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user's information from the database so we can use it in the modal.
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user = $user_result->fetch_assoc()) {
    $user_type = $user['user_type'];
} else {
    $user_type = "resident";
    $user = array(); // to avoid undefined index warnings
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
    /* Main container */
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
    .navbar-custom .navbar-toggle { border-color: rgba(255, 255, 255, 0.1); }
    .navbar-custom .navbar-toggle .icon-bar { background-color: #fff; }
    /* Footer Styling */
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
    /* Blog/Post Section Container */
    .blog-section {
      width: 100%;
      max-width: 1200px;
      margin: 2rem auto;
    }
    .chat-btn {
  background-color: #333;
  border: none;
  color: #fff;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}
.chat-btn:hover {
  background-color: #005bb5;
}

    /* Individual Post Container */
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
    .post-footer { font-size: 0.9rem; color: #555; margin-bottom: 1rem; }
    .post-footer hr { margin: 0.5rem 0; }
    .post-comment-box input[type="text"] {
      width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
    }
    .textarea-container { position: relative; }
    .attachment-btn {
      position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
      z-index: 2; border-radius: 50%; background-color: #333333 !important;
      color: white !important; border: none !important; padding: 8px 12px; cursor: pointer;
    }
    .post-btn-container button {
      background-color: #333333 !important; color: white !important;
      border: none !important; padding: 10px 20px; cursor: pointer;
    }
    .post-btn-container { margin-top: 20px; text-align: center; }
    #fileInput { display: none; }
    textarea.form-control { padding-right: 40px; margin-bottom: 15px; }
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

      <!-- Blog/Post Section -->
      <section class="blog-section">
        <?php if ($post_result && $post_result->num_rows > 0): ?>
          <?php while ($post = $post_result->fetch_assoc()): ?>
            <div class="post-container">
              <!-- Post Header: User Info & Timestamp -->
              <!-- Post Header: User Info, Timestamp, and Chat Button -->
<div class="post-header" style="display: flex; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center;">
    <?php
      // Determine the author's profile picture (fallback to default if not available)
      $post_profile_pic = !empty($post['profile_pic']) ? $post['profile_pic'] : $default_pic;
    ?>
    <img class="post-author-pic" src="<?php echo htmlspecialchars($post_profile_pic); ?>" alt="User Pic" style="margin-right: 10px;" />
    <div class="post-author-info">
      <h5 style="margin: 0;"><?php echo htmlspecialchars($post['name']); ?></h5>
      <small><?php echo date("M d, Y H:i", strtotime($post['created_at'])); ?></small>
    </div>
  </div>
  <button class="chat-btn" onclick="startChat(<?php echo $post['user_id']; ?>)">Chat</button>
</div>


              <!-- Post Content: Text & Images -->
              <div class="post-body">
                <p class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php if (!is_null($post['blog_image1']) || !is_null($post['blog_image2']) || !is_null($post['blog_image3']) || !is_null($post['blog_image4'])): ?>
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

              <!-- Comments Section -->
              <div class="comments-section">
                <?php 
                  $comments_sql = "SELECT c.*, u.name AS commenter_name, u.profile_pic AS commenter_pic 
                                   FROM comments c 
                                   JOIN users u ON c.user_id = u.id 
                                   WHERE c.post_id = " . $post['post_id'] . " 
                                   ORDER BY c.created_at DESC";
                  $comments_result = $conn->query($comments_sql);
                  if ($comments_result && $comments_result->num_rows > 0):
                    while ($comment = $comments_result->fetch_assoc()):
                      $commenter_pic = !empty($comment['commenter_pic']) ? $comment['commenter_pic'] : $default_pic;
                ?>
                  <div class="comment" style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <img class="commenter-pic" src="<?php echo htmlspecialchars($commenter_pic); ?>" alt="Commenter Pic" style="width:30px; height:30px; border-radius:50%; object-fit:cover; margin-right:10px;">
                    <div class="comment-details">
                      <span class="commenter-name" style="font-weight:bold;"><?php echo htmlspecialchars($comment['commenter_name']); ?></span>
                      <span class="comment-time" style="font-size: 12px; color:#888; margin-left:5px;"><?php echo date("M d, Y H:i", strtotime($comment['created_at'])); ?></span>
                      <p class="comment-text" style="margin: 5px 0 0;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                  </div>
                <?php endwhile; else: ?>
                  <p class="no-comments" style="font-size: 14px; color: #888;">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
              </div>

              <!-- Comment Input -->
              <div class="post-comment-box">
                <form action="create_comment.php" method="POST">
                  <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>" />
                  <input type="text" name="comment" placeholder="Write a comment..." required />
                  <button type="submit">Post</button>
                </form>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center">There are no posts yet.</p>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="row">
        <div class="col-md-12 text-left">
          <p style="margin: 0;">
            Copyright &copy; <?php echo date("Y"); ?> All Rights Reserved
            by <a href="https://www.facebook.com/profile.php?id=100076790015696">MIST</a>.
          </p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll Up Button (optional) -->
  <div class="scroll-up">
    <a href="#totop"><i class="fa fa-angle-double-up"></i></a>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
  <script>
    // Limit the number of file selections to 4 for the resident form
    if(document.getElementById('fileInput')){
      document.getElementById('fileInput').addEventListener('change', function(){
        if (this.files.length > 4) {
          alert("You can only upload a maximum of 4 images.");
          this.value = "";
        }
      });
    }
  </script>
  <script>
  function startChat(partnerId) {
    if (partnerId) {
      window.location.href = 'chat.php?chat_with=' + partnerId;
    } else {
      alert("Chat partner not available.");
    }
  }
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
                Please provide a clear service title and detailed description. Include your pricing information,
                and upload up to 2 images.<br>
                <strong>Note:</strong> The <em>first image</em> must be a detailed gig image (like on Fiverr), while images
                <em>2</em> can be used for reviews or samples of your work.
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
                Maximum 2 images allowed. The <strong>first image</strong> must be a detailed gig image (like on Fiverr). 
                Images <strong>2</strong> can be review images or work samples.
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

</body>
</html>
