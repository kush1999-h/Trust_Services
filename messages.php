<?php
session_start();
include 'db_config.php';

// Ensure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$currentUser = intval($_SESSION['user_id']);

// Query to get distinct chat partners and the most recent message time
$partners_sql = "
  SELECT 
    CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id,
    MAX(created_at) AS last_time
  FROM chat_messages
  WHERE sender_id = ? OR receiver_id = ?
  GROUP BY partner_id
  ORDER BY last_time DESC
";
$stmt = $conn->prepare($partners_sql);
$stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
$stmt->execute();
$partners_result = $stmt->get_result();

$chatList = array();
while ($row = $partners_result->fetch_assoc()) {
    $chatList[] = $row; // Contains 'partner_id' and 'last_time'
}
$stmt->close();

// For each chat partner, fetch user details and the last message snippet.
$chatItems = array();
foreach ($chatList as $item) {
    $partnerId = intval($item['partner_id']);
    
    // Fetch partner's details.
    $user_sql = "SELECT name, profile_pic FROM users WHERE id = ? LIMIT 1";
    $u_stmt = $conn->prepare($user_sql);
    $u_stmt->bind_param("i", $partnerId);
    $u_stmt->execute();
    $u_result = $u_stmt->get_result();
    $userData = $u_result->fetch_assoc();
    $u_stmt->close();
    
    // Fetch the latest message snippet between the current user and the partner.
    $snippet_sql = "
      SELECT message, created_at 
      FROM chat_messages 
      WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
      ORDER BY created_at DESC
      LIMIT 1
    ";
    $s_stmt = $conn->prepare($snippet_sql);
    $s_stmt->bind_param("iiii", $currentUser, $partnerId, $partnerId, $currentUser);
    $s_stmt->execute();
    $s_result = $s_stmt->get_result();
    $snippetData = $s_result->fetch_assoc();
    $s_stmt->close();
    
    // Use default profile picture if not available.
    $default_pic = "https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcSjIfXwry7VJUFPcOYMF6GcShOoDm9EvGhbRbZXoNZhEvDFFY9CTENVO19a3maFYqsep71RNpu9LFz90zaiVQT3IkJ3OQOc1Uu72KUSypU";
    $partnerPic = ($userData && !empty($userData['profile_pic'])) ? $userData['profile_pic'] : $default_pic;
    $partnerName = $userData ? $userData['name'] : "User $partnerId";
    $messageSnippet = $snippetData ? $snippetData['message'] : "No messages yet.";
    $timeLabel = $snippetData ? date("M d, Y H:i", strtotime($snippetData['created_at'])) : date("M d, Y H:i", strtotime($item['last_time']));
    
    $chatItems[] = array(
      'partner_id'    => $partnerId,
      'partner_name'  => $partnerName,
      'partner_pic'   => $partnerPic,
      'message_snippet' => $messageSnippet,
      'time'          => $timeLabel
    );
}
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
    body {
      font-family: 'Arial', sans-serif;
    }
    .main {
  flex: 1;
  margin: 0;      /* Remove centering */
  padding: 20px;  /* Keep padding if desired */
  max-width: none; /* Let the content fill full width */
}

    .text-center {
      text-align: center;
      margin-bottom: 20px; /* Space below the heading */
    }
    .font-alt {
      font-size: 24px; /* Adjust size as needed */
      font-weight: bold;
      color: #333;
    }
    .navbar-custom {
      background-color: #333;
      max-width: auto;
      margin: 0;
    }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav > li > a {
      color: #fff;
    }
    .navbar-custom .navbar-toggle {
      border-color: rgba(255, 255, 255, 0.1);
    }
    .navbar-custom .navbar-toggle .icon-bar {
      background-color: #fff;
    }
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
    .footer p {
  margin: 0;        /* Removes auto-centering */
  text-align: left; /* Aligns text to the left */
}
    footer.site-footer .container {
      max-width: 1200px;
      margin: 0 auto;
    }
    .footer .copyright {
      margin: 0;
    }
    .footer .footer-social-links {
      text-align: right;
    }
    .footer .footer-social-links a {
      display: inline-block;
      padding: 0 6px;
    }
    /* Make the entire chat section span full width */
    .chat-section {
  width: 100%;
  background-color: #f0f2f5;
  min-height: calc(100vh - 60px);
  padding: 0;
}


/* The left “sidebar” that contains the search bar and chat list */
.messenger-sidebar {
    width: 100%;
  max-width: none; /* Let it fill all available horizontal space */
  margin: 0;

  background-color: #fff; /* White background for the chat list */
  min-height: 100vh; /* Make it tall enough to fill screen height */
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
}

/* Search Box at the top */
.search-box {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}
.search-input {
  width: 100%;
  padding: 8px 12px;
  border-radius: 20px;
  border: 1px solid #ccc;
  outline: none;
}

/* Tabs (Inbox / Communities) */
.menu-tabs {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 10px 15px;
  border-bottom: 1px solid #ddd;
  font-weight: 500;
  color: #555;
}
.menu-tabs span {
  cursor: pointer;
}
.menu-tabs .active {
  color: #0078fe; /* Highlight active tab in blue */
}

/* Chat List container */
.chat-list {
  flex: 1; /* Grow to fill remaining vertical space */
  overflow-y: auto; /* Make the list scrollable if it exceeds container height */
  padding: 10px;
}

/* Individual chat item */
.chat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
  cursor: pointer;
}
.chat-item:hover {
  background-color: #f2f2f2;
}

/* User avatar */
.chat-item-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 10px;
}

.chat-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
  cursor: pointer;
}
.chat-item:hover {
  background-color: #f2f2f2;
}
.chat-item-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 10px;
}
.chat-item-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  position: relative;
}
.chat-item-details h5 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #050505;
}
.chat-item-details p {
  margin: 0;
  font-size: 0.85rem;
  color: #65676b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.chat-item-details .time {
  display: block;
  margin-top: 5px;
  font-size: 0.75rem;
  color: #999;
}


    
    @media (max-width: 768px) {
      .shop-item {
        margin-bottom: 20px;
      }
    }
  </style>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<br data-spy="scroll" data-target=".onpage-navigation" data-offset="60">
  <!-- Header with Navbar -->
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
</br>
  <!-- Main Content -->
  <main class="main">
    <!-- Chat Section -->
    <section class="chat-section">
      <div class="container">
        <div class="messenger-sidebar">
          <!-- Search Box -->
          <div class="search-box">
            <input type="text" class="search-input" placeholder="Search Messenger" />
          </div>
          <!-- Tabs -->
          <!-- Chat List -->
          <div class="chat-list">
            <?php
            // Fetch distinct chat partners for the current user
            $partners_sql = "
              SELECT 
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id,
                MAX(created_at) AS last_time
              FROM chat_messages
              WHERE sender_id = ? OR receiver_id = ?
              GROUP BY partner_id
              ORDER BY last_time DESC
            ";
            $stmt = $conn->prepare($partners_sql);
            $stmt->bind_param("iii", $currentUser, $currentUser, $currentUser);
            $stmt->execute();
            $partners_result = $stmt->get_result();
            
            $chatList = array();
            while ($row = $partners_result->fetch_assoc()) {
                $chatList[] = $row; // Contains partner_id and last_time
            }
            $stmt->close();
            
            // For each partner, get user details and the last message snippet.
            foreach ($chatList as $partnerRow) {
                $partnerId = intval($partnerRow['partner_id']);
                
                // Get partner details
                $user_sql = "SELECT name, profile_pic FROM users WHERE id = ? LIMIT 1";
                $u_stmt = $conn->prepare($user_sql);
                $u_stmt->bind_param("i", $partnerId);
                $u_stmt->execute();
                $u_result = $u_stmt->get_result();
                $userData = $u_result->fetch_assoc();
                $u_stmt->close();
                
                // Get last message snippet
                $snippet_sql = "
                  SELECT message, created_at 
                  FROM chat_messages
                  WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                  ORDER BY created_at DESC
                  LIMIT 1
                ";
                $s_stmt = $conn->prepare($snippet_sql);
                $s_stmt->bind_param("iiii", $currentUser, $partnerId, $partnerId, $currentUser);
                $s_stmt->execute();
                $s_result = $s_stmt->get_result();
                $snippetData = $s_result->fetch_assoc();
                $s_stmt->close();
                
                $partnerName = $userData ? $userData['name'] : "User $partnerId";
                $partnerPic = ($userData && !empty($userData['profile_pic'])) ? $userData['profile_pic'] : "https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcSjIfXwry7VJUFPcOYMF6GcShOoDm9EvGhbRbZXoNZhEvDFFY9CTENVO19a3maFYqsep71RNpu9LFz90zaiVQT3IkJ3OQOc1Uu72KUSypU";
                $lastMsg = $snippetData ? $snippetData['message'] : "No messages yet.";
                $timeLabel = $snippetData ? date("M d, Y H:i", strtotime($snippetData['created_at'])) : date("M d, Y H:i", strtotime($partnerRow['last_time']));
            ?>
            <div class="chat-item">
  <a href="chat.php?chat_with=<?php echo $partnerId; ?>" class="chat-item-link" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
    <img src="<?php echo htmlspecialchars($partnerPic); ?>" alt="User" class="chat-item-avatar" />
    <div class="chat-item-details">
      <h5><?php echo htmlspecialchars($partnerName); ?></h5>
      <p><?php echo htmlspecialchars($lastMsg); ?></p>
      <span class="time"><?php echo htmlspecialchars($timeLabel); ?></span>
    </div>
  </a>
</div>


            <?php } ?>
          </div>
        </div>
      </div> <!-- end .container -->
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-left">
        <p style="margin: 0;">
          Copyright &copy; 2025 All Rights Reserved
          by <a href="https://www.facebook.com/profile.php?id=100076790015696">MIST</a>.
        </p>
      </div>
    </div>
  </div>
</footer>

  
  <div class="scroll-up">
    <a href="#totop"><i class="fa fa-angle-double-up"></i></a>
  </div>

  <!-- jQuery and Bootstrap Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
  
</body>
</html>
