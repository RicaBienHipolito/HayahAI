<?php
session_start();

// Redirect to sign in if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$servername = "localhost:3306 ";
$username = "root";
$password = "";
$dbname = "hayahai_db";

// MySQL stuff
$conn = @new mysqli($servername, $username, $password, $dbname);

// Redir to sign in if it errors
if ($conn->connect_errno) {
    header("Location: signin.php");
    exit();
}

// The yapbot
$answer = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["question"])) {
    $question = trim($_POST["question"]);
    $user_id = $_SESSION['user_id'];

    // Insert user message
    $stmt = $conn->prepare("INSERT INTO customer_query (user_id, message, response) VALUES (?, ?, '')");
    $stmt->bind_param("is", $user_id, $question);
    $stmt->execute();
    $stmt->close();

    // Run Python synchronously
    exec(__DIR__ . "\\python\\python.exe " . __DIR__ . '\\functions\\hayah_ai\\hayahai.py', $output, $return_var);

    // Wait a tiny bit for DB to be updated (optional, usually exec waits)
    usleep(100000); // 0.1 seconds

    // Fetch all messages for this user
    $result = $conn->query("SELECT message, response FROM customer_query WHERE user_id = $user_id ORDER BY created_at ASC");
    $chat_history = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chat_history[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HayahAI - Main</title>
    <style>
      body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: white;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }

      /* ===== TOP BAR ===== */
      .top-bar {
        position: absolute;
        top: 20px;
        left: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 1000;
      }

      .menu-icon,
      .cart-icon {
        cursor: pointer;
        font-size: 28px;
        color: black;
        text-decoration: none;
        transition: 0.3s;
      }

      .menu-icon:hover,
      .cart-icon:hover {
        transform: scale(1.1);
      }

      /* ===== SIDEBAR MENU ===== */
      .sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        top: 0;
        left: -250px;
        background-color: #ffc55c;
        padding-top: 60px;
        transition: 0.3s;
        box-shadow: 2px 0 5px rgba(0,0,0,0.3);
        z-index: 999;
      }

      .sidebar a {
        display: block;
        padding: 15px 25px;
        color: black;
        text-decoration: none;
        font-size: 18px;
        transition: background-color 0.3s;
      }

      .sidebar a:hover {
        background-color: #f0b93c;
      }

      .close-btn {
        position: absolute;
        top: 15px;
        right: 25px;
        font-size: 30px;
        cursor: pointer;
      }

      /* ===== SEARCH BAR ===== */
      .search-container {
        width: 80%;
        max-width: 1000px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .search-box {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        border: 1px solid #ccc;
        border-radius: 50px;
        background-color: #e6e6e6;
        padding: 5px 10px;
      }

      .search-box input {
        flex: 1;
        min-width: 0;
        border: none;
        background: transparent;
        padding: 10px;
        outline: none;
        font-size: 16px;
      }

      .search-box button {
        background-color: #ffc55c;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        font-weight: bold;
        color: white;
        flex-shrink: 0;
      }

      .chat-container {
          display: flex;
          flex-direction: column;
          width: 100%;
          max-width: 500px;
          gap: 10px;
          margin-top: 15px;
      }


      .chat-box {
          width: 100%;
          max-width: 500px;       
          height: 300px;          
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 10px;
          background-color: #f8f8f8;
          overflow-y: auto;       /* makes the thingy scrollable */
          display: flex;
          flex-direction: column;
          gap: 8px;
          margin-top: 15px;
      }

      .chat-message {
          padding: 8px 12px;
          border-radius: 12px;
          max-width: 80%;
      }

      .chat-message.user {
          background-color: #d0f0ff;
          align-self: flex-end;
      }

      .chat-message.bot {
          background-color: #fff;
          border: 1px solid #ccc;
          align-self: flex-start;
      }

      /* ===== FOOTER ===== */
      footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #ffc55c;
        text-align: center;
        padding: 10px 0;
        font-size: 24px;
        font-weight: bold;
        color: white;
      }
    </style>
  </head>
  <body>

    <!-- ===== TOP BAR ===== -->
    <div class="top-bar">
      <span class="menu-icon" onclick="openMenu()">☰</span>
      <a href="cart.php" class="cart-icon">🛒</a>
    </div>

    <!-- ===== SIDEBAR MENU ===== -->
    <div id="sidebar" class="sidebar">
      <span class="close-btn" onclick="closeMenu()">×</span>
      <a href="products.php">Products</a>
      <a href="about.php">About Us</a>
      <a href="functions/signout.php">Sign Out</a>
    </div>

    <!-- SEARCH BAR / CHATBOT -->
    <form id="chat-form" class="search-box" autocomplete="off">
      <input type="text" id="question" placeholder="Ask me something..." required>
      <button type="submit">▶</button>
    </form>

    <div class="chat-box" id="chat-box"></div>
    
    <!-- Uses AJAX for asynch. operations/connection -->
    <script>
    const form = document.getElementById('chat-form');
    const input = document.getElementById('question');
    const chatBox = document.getElementById('chat-box');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const question = input.value;
      input.value = '';

      // Show user message immediately
      chatBox.innerHTML += `<div class="chat-message user">${question}</div>`;
      chatBox.scrollTop = chatBox.scrollHeight;

      // Send to PHP
      fetch('functions/hayah_ai/chat_endpoint.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'question=' + encodeURIComponent(question)
      })
      .then(res => res.json())
      .then(data => {
        if (data.bot) {
          chatBox.innerHTML += `<div class="chat-message bot">${data.bot}</div>`;
          chatBox.scrollTop = chatBox.scrollHeight;
        }
      });
    });
    </script>

    <!-- ===== FOOTER ===== -->
    <footer>HayahAI</footer>

    <script>
      function openMenu() {
        document.getElementById("sidebar").style.left = "0";
      }
      function closeMenu() {
        document.getElementById("sidebar").style.left = "-250px";
      }
    </script>
  </body>
</html>