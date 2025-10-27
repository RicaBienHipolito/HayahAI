<?php
session_start();

$conn = new mysqli(hostname: "localhost", username: "root", password: "", database: "hayahai_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirects to signin page if not logged in
if (!isset($_SESSION['user_id'])) {
    header(header: "Location: signin.php");
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
        overflow-y: auto;
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
        margin: 20px 0;
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

      .compare-results {
        width: 80%;
        max-width: 1000px;
        margin: 20px auto 0; /* tighten the gap */
        text-align: center;
      }

      .compare-grid {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
      }

      .compare-card {
        flex: 1;
        min-width: 250px;
        max-width: 300px;
        border: 1px solid #ccc;
        border-radius: 15px;
        padding: 15px;
        background: #fafafa;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

      /* Product List stuff*/
      h2 {
        margin-top: 100px; /* pushes it below top bar */
        text-align: center;
      }

      .product-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        width: 80%;
        margin: 40px auto 100px;
        padding-bottom: 100px; /* anti-footer measures */
      }

      .product-card {
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: transform 0.2s;
      }

      .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
    <a href="products.php" style="display:block; margin-bottom:15px; text-decoration:none; color:black;">⚖️ Products</a>
    <a href="about.php" style="display:block; margin-bottom:15px; text-decoration:none; color:black;">ℹ️ About Us</a>
    <a href="signout.php" style="display:block; text-decoration:none; color:black;">🚪 Sign Out</a>
  </div>

  <script>
  function toggleMenu() {
    var menu = document.getElementById("sideMenu");
    menu.style.display = (menu.style.display === "none") ? "block" : "none";
  }
  </script>

  <h2>Products</h2>

  <!-- Compare Section --> 
    <?php
      if (isset($_GET['product1']) && isset($_GET['product2'])) {
          $p1 = $_GET['product1'];
          $p2 = $_GET['product2'];

          // Prevent SQL injection
          $stmt = $conn->prepare(query: "SELECT * FROM products WHERE Name LIKE ? OR Name LIKE ?");
          $like1 = "%$p1%";
          $like2 = "%$p2%";
          $stmt->bind_param("ss", $like1, $like2);
          $stmt->execute();
          $result = $stmt->get_result();

          echo '<div class="compare-results">';
          echo '<h3>Comparison Results</h3>';

          if ($result->num_rows > 0) {
              echo '<div class="compare-grid">';
              while ($row = $result->fetch_assoc()) {
                  $priceValue = preg_replace(pattern: '/[^0-9.]/', replacement: '', subject: $row['Price']);
                  echo '<div class="compare-card">';
                  echo '<h4>' . htmlspecialchars(string: $row['Name']) . '</h4>';
                  echo '<p>Category: ' . htmlspecialchars(string: $row['Category']) . '</p>';
                  echo '<p>Supplier: ' . htmlspecialchars(string: $row['Supplier']) . '</p>';
                  echo '<p>Price: $' . number_format(num: (float)$priceValue, decimals: 2) . '</p>';
                  echo '<p>Stock: ' . htmlspecialchars(string: $row['StockQuantity']) . '</p>';
                  echo '</div>';
              }
              echo '</div>';
          } else {
              echo '<p>No products found for comparison.</p>';
          }
          echo '</div>';
      }
    ?>

  <div class="compare-container">
    <form method="GET" class="compare-container">
    <input type="text" name="product1" placeholder="Search for a product">
    <span class="compare-icon">🔁</span>
    <input type="text" name="product2" placeholder="Search for a product">
    <button type="submit">Compare</button>
  </form>
  </div>

  <!-- Product Stuff -->
  <div class = "product-container">
    <?php 
      $query = "SELECT ProductID, Name, Category, Supplier, Price, StockQuantity FROM products";
      $result = $conn->query(query: $query);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "
              <div class='product-card'>
                  <h3>{$row['Name']}</h3>
                  <p><strong>Category:</strong> {$row['Category']}</p>
                  <p><strong>Supplier:</strong> {$row['Supplier']}</p>
                  <p><strong>Price:</strong> $" . number_format(num: $row['Price'], decimals: 2) . "</p>
                  <p><strong>Stock:</strong> {$row['StockQuantity']} available</p>
                  <button>Add to List</button>
              </div>
              ";
          }
      } else {
          echo "<p>No products found.</p>";
      }
    ?>
  </div>

  <footer>HayahAI</footer>

  </body>
</html>
