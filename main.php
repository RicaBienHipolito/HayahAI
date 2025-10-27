<?php
session_start();

// Redirect to sign in if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// ===== DATABASE CONNECTION =====
$servername = "localhost:3307 ";
$username = "root";     // change if you created a new MySQL user
$password = "";          // put your MySQL password if any
$dbname = "hayahai_db";    // make sure this matches your actual database name

// Try connecting to MySQL
$conn = @new mysqli($servername, $username, $password, $dbname);

// If connection fails, redirect to signin.php instead of crashing
if ($conn->connect_errno) {
    header("Location: signin.php");
    exit();
}

// ===== CHATBOT LOGIC =====
$answer = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = strtolower(trim($_POST["question"]));

    if (strpos($question, "hello") !== false || strpos($question, "hi") !== false) {
        $answer = "Hello! You can ask me about product locations or stock availability.";
    }
    elseif (strpos($question, "where") !== false) {
        $words = explode(" ", $question);
        $item = trim(end($words));

        $sql = "SELECT Category FROM products WHERE Name LIKE '%$item%'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $answer = ucfirst($item) . " is located in the " . $row["Category"] . " section.";
        } else {
            $answer = "Sorry, I couldn't find where " . htmlspecialchars($item) . " is located.";
        }
    }
    // Check stock of an item
    elseif (strpos($question, "stock") !== false || strpos($question, "available") !== false || strpos($question, "how many") !== false) {
        // Clean up question and extract possible product name
        $item = trim(str_replace(["how many", "are", "in stock", "stock", "available", "of", "?", "."], "", $question));

        $sql = "SELECT Name, StockQuantity FROM products WHERE Name LIKE '%$item%'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $answer = "There are " . $row["StockQuantity"] . " " . ucfirst($row["Name"]) . "(s) in stock.";
        } else {
            $answer = "Sorry, I couldn't find stock information for " . htmlspecialchars($item) . ".";
        }
    }
    else {
        $answer = "I'm not sure how to answer that. Try asking 'Where is pineapple?' or 'How many mango in stock?'";
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

  .search-container {
    width: 80%;
    max-width: 1000px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
  }

  .search-box {
    display: flex;
    align-items: center;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 50px;
    background-color: #e6e6e6;
    padding: 5px 10px;
  }

  .search-box input {
    flex: 1;
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
    width: 35px;
    height: 35px;
    cursor: pointer;
    font-weight: bold;
    color: white;
  }

  .response {
    background: white;
    border-radius: 10px;
    padding: 15px;
    width: 80%;
    max-width: 800px;
    box-shadow: 0 0 5px #ccc;
    text-align: center;
  }

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

  <!-- TOP BAR -->
  <div class="top-bar">
    <span class="menu-icon" onclick="openMenu()">☰</span>
  </div>

  <!-- SIDEBAR MENU -->
  <div id="sidebar" class="sidebar">
    <span class="close-btn" onclick="closeMenu()">×</span>
    <a href="compare.php">Compare Products</a>
    <a href="about.php">About Us</a>
    <a href="signout.php">Sign Out</a>
  </div>

  <!-- SEARCH BAR / CHATBOT -->
  <div class="search-container">
    <form method="post" class="search-box">
      <input type="text" name="question" placeholder="Ask me something, e.g. 'Where is mango?'" required>
      <button type="submit">▶</button>
    </form>

    <?php if ($answer): ?>
      <div class="response">
        <strong>Answer:</strong><br>
        <?= htmlspecialchars($answer) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- FOOTER -->
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
