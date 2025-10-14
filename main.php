<?php
session_start();

// Redirect to sign in if not logged in
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
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-weight: bold;
    color: white;
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
    <a href="compare.php">Compare Products</a>
    <a href="about.php">About Us</a>
    <a href="signout.php">Sign Out</a>
  </div>

  <!-- ===== SEARCH BAR ===== -->
  <div class="search-container">
    <div class="search-box">
      <input type="text" placeholder="Search for a product">
      <button>▶</button>
    </div>
  </div>

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
