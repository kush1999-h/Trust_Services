<?php
session_start();
include 'db_config.php';

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$currentUser = intval($_SESSION['user_id']);

// Get the chat partner's ID from GET parameter (e.g. ?chat_with=2)
if (!isset($_GET['chat_with']) || empty($_GET['chat_with'])) {
    die("No chat partner specified.");
}
$chatWith = intval($_GET['chat_with']);

// (Optional) Fetch chat partner's name to display in header.
$partnerName = "User $chatWith";
$partnerSql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($partnerSql);
$stmt->bind_param("i", $chatWith);
$stmt->execute();
$stmt->bind_result($name);
if ($stmt->fetch()) {
    $partnerName = $name;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chat - Trust Services</title>
  <style>
    /* Global & Reset */
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
    /* Ensure main content uses the same container width as navbar/footer */
.main {
  flex: 1;
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: 'Arial', sans-serif; /* Same font as elsewhere */
}

/* Chat container styling */
.chat-container {
  width: 100%;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  height: 80vh;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Chat Header */
.chat-header {
  padding: 10px 15px;
  border-bottom: 1px solid #ccc;
  background: #f9f9f9;
}
.chat-header h4 {
  margin: 0;
  font-size: 1.2rem;
}

/* Chat Body */
.chat-body {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #fff;
}

/* Chat Footer */
.chat-footer {
  padding: 10px 15px;
  border-top: 1px solid #ccc;
  background: #f9f9f9;
  display: flex;
  gap: 10px;
}

.message-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 20px;
  outline: none;
  font-size: 1rem;
}
.btn-send {
  background-color: #333;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-send:hover {
  background-color: #333;
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
    .main {
  flex: 1;
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: 'Arial', sans-serif; /* Same font as elsewhere */
}

/* Chat container styling */
.chat-container {
  width: 100%;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  height: 80vh;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

    /* Chat Header */
    .chat-header {
      padding: 10px 15px;
      border-bottom: 1px solid #ccc;
      background: #f9f9f9;
    }
    .chat-header h4 {
      margin: 0;
      font-size: 1.2rem;
    }
    /* Chat Body */
    .chat-body {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
      background: #fff;
    }
    .message {
      margin-bottom: 10px;
      clear: both;
    }
    .message .bubble {
      display: inline-block;
      padding: 10px 15px;
      border-radius: 18px;
      max-width: 70%;
      word-wrap: break-word;
      margin-bottom: 2px; /* Increase spacing between messages */
  clear: both;

    }
    .message.left .bubble {
      background-color: #e4e6eb;
      float: left;
    }
    .message.right .bubble {
      background-color: #333;
      color: #fff;
      float: right;
    }
    /* Chat Footer */
    .chat-footer {
      padding: 10px 15px;
      border-top: 1px solid #ccc;
      background: #f9f9f9;
      display: flex;
      gap: 10px;
    }
    .message-input {
      flex: 1;
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 20px;
      outline: none;
      font-size: 1rem;
    }
    .btn-send {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 1rem;
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
  </style>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <<!-- Header with Navbar -->
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
  <!-- Main Content -->
  <<main class="main">
  <div class="container">
    <div class="chat-container">
      <div class="chat-header">
        <h4>Chat with <?php echo htmlspecialchars($partnerName); ?></h4>
      </div>
      <div class="chat-body" id="chatBody">
        <!-- Messages will load here via AJAX -->
      </div>
      <div class="chat-footer">
        <input type="text" id="messageInput" class="message-input" placeholder="Type a message...">
        <button id="sendBtn" class="btn-send">Send</button>
      </div>
    </div>
  </div>
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
  <!-- Bootstrap JS -->
    <!-- jQuery and Bootstrap Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
  <script>
    var currentUser = <?php echo $currentUser; ?>;
    var chatWith = <?php echo $chatWith; ?>;
    
    // Function to load messages via AJAX
    function loadMessages(){
      $.ajax({
         url: "get_messages.php",
         type: "GET",
         data: { chat_with: chatWith },
         dataType: "json",
         success: function(data) {
            var html = "";
            data.forEach(function(msg) {
              var alignment = (msg.sender_id == currentUser) ? "right" : "left";
              html += '<div class="message ' + alignment + '"><div class="bubble">' + msg.message + '</div></div>';
            });
            $("#chatBody").html(html);
            $("#chatBody").scrollTop($("#chatBody")[0].scrollHeight);
         },
         error: function(xhr, status, error) {
             console.error("Error loading messages: " + error);
         }
      });
    }
    
    // Initially load messages and poll every 3 seconds.
    loadMessages();
    setInterval(loadMessages, 3000);
    
    // Send message function
    $("#sendBtn").click(function(){
      var message = $("#messageInput").val().trim();
      if(message !== ""){
        $.ajax({
          url: "send_message.php",
          type: "POST",
          data: { receiver_id: chatWith, message: message },
          success: function(response){
            $("#messageInput").val("");
            loadMessages();
          },
          error: function(xhr, status, error) {
            console.error("Error sending message: " + error);
          }
        });
      }
    });
    
    // Send message on Enter key press
    $("#messageInput").keypress(function(e){
      if(e.which === 13){
        $("#sendBtn").click();
        return false;
      }
    });
  </script>
</body>
</html>


