<?php
// cart.php
session_start();
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
  <title>Cart - HayahAI</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      text-align: center;
      background-color: #fff;
    }

    .home-icon {
      position: absolute;
      top: 20px;
      left: 20px;
      font-size: 28px;
      text-decoration: none;
      color: black;
    }

    .content {
      margin-top: 100px;
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #ffc65b;
      color: white;
      text-align: center;
      padding: 10px 0;
      font-size: 24px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <a href="main.php" class="home-icon">🏠</a>

  <div class="content">
    <h2>Your Shopping Cart</h2>
    <p>Looks like your cart is empty for now!</p>
  </div>

  <footer>HayahAI</footer>
</body>
</html>
