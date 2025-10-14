<?php
session_start();

// Redirect to signin if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compare Products - HayahAI</title>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100vh;
    overflow: hidden;
  }

  /* Top Menu */
  .top-bar {
    position: absolute;
    top: 20px;
    left: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .menu-icon, .cart-icon {
    font-size: 28px;
    cursor: pointer;
    text-decoration: none;
    color: black;
  }

  /* Compare Section */
  .compare-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-top: 250px;
    width: 100%;
  }

  input[type="text"] {
    width: 20%;
    min-width: 250px;
    padding: 10px 15px;
    border-radius: 20px;
    border: 1px solid #aaa;
    background-color: #e6e6e6;
    font-size: 14px;
    outline: none;
  }

  .compare-icon {
    font-size: 22px;
  }

  button {
    border: 1px solid #888;
    border-radius: 20px;
    background-color: #ffc55c;
    padding: 6px 25px;
    cursor: pointer;
    transition: 0.3s;
    font-size: 14px;
  }

  button:hover {
    background-color: #ffb933;
  }

  /* Footer */
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

<!-- Top Icons -->
<div class="top-bar">
  <a href="#" class="menu-icon" onclick="toggleMenu()">☰</a>
  <a href="cart.php" class="cart-icon">🛒</a>
</div>

<!-- Side Menu -->
<div id="sideMenu" style="display:none; position:fixed; top:0; left:0; width:200px; height:100%; background:#fff; border-right:1px solid #ccc; padding:20px;">
  <a href="main.php" style="display:block; margin-bottom:15px; text-decoration:none; color:black;">🏠 Home</a>
  <a href="compare.php" style="display:block; margin-bottom:15px; text-decoration:none; color:black;">⚖️ Compare Products</a>
  <a href="about.php" style="display:block; margin-bottom:15px; text-decoration:none; color:black;">ℹ️ About Us</a>
  <a href="signout.php" style="display:block; text-decoration:none; color:black;">🚪 Sign Out</a>
</div>

<script>
function toggleMenu() {
  var menu = document.getElementById("sideMenu");
  menu.style.display = (menu.style.display === "none") ? "block" : "none";
}
</script>

<!-- Compare Section -->
<div class="compare-container">
  <input type="text" placeholder="Search for a product">
  <span class="compare-icon">🔁</span>
  <input type="text" placeholder="Search for a product">
  <button>Compare</button>
</div>

<footer>HayahAI</footer>

</body>
</html>
