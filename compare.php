<?php
session_start();

// Redirect to signin if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// ===== DATABASE CONNECTION =====
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "hayahai_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_errno) {
    die("Database connection failed: " . $conn->connect_error);
}

// ===== COMPARISON LOGIC =====
$product1 = $product2 = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name1 = trim($_POST["product1"]);
    $name2 = trim($_POST["product2"]);

    if ($name1 !== "" && $name2 !== "") {
        $stmt1 = $conn->prepare("SELECT Name, Supplier, Price, StockQuantity FROM products WHERE Name LIKE CONCAT('%', ?, '%') LIMIT 1");
        $stmt1->bind_param("s", $name1);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $product1 = $result1->fetch_assoc();

        $stmt2 = $conn->prepare("SELECT Name, Supplier, Price, StockQuantity FROM products WHERE Name LIKE CONCAT('%', ?, '%') LIMIT 1");
        $stmt2->bind_param("s", $name2);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $product2 = $result2->fetch_assoc();

        if (!$product1 || !$product2) {
            $error = "One or both products were not found in the database.";
        }

        $stmt1->close();
        $stmt2->close();
    } else {
        $error = "Please enter two product names.";
    }
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
  }

  /* ===== Top Bar ===== */
  .top-bar {
    position: fixed;
    top: 20px;
    left: 30px;
    display: flex;
    align-items: center;
    z-index: 1001;
  }

  .menu-icon, .close-icon {
    font-size: 28px;
    cursor: pointer;
    border: none;
    background: none;
  }

  /* ===== Sidebar Menu ===== */
  .side-menu {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #ffc55c;
    overflow-x: hidden;
    transition: 0.4s;
    padding-top: 60px;
    z-index: 1000;
  }

  .side-menu a {
    padding: 12px 30px;
    text-decoration: none;
    font-size: 18px;
    color: black;
    display: block;
    transition: 0.3s;
  }

  .side-menu a:hover {
    background-color: #ffb933;
  }

  .close-icon {
    position: absolute;
    top: 15px;
    right: 25px;
  }

  /* ===== Compare Section ===== */
  .compare-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-top: 200px;
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

  /* ===== Product Results ===== */
  .results {
    display: flex;
    justify-content: center;
    margin-top: 40px;
    width: 90%;
    max-width: 900px;
    gap: 30px;
  }

  .product-card {
    flex: 1;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 0 10px #ccc;
    padding: 20px;
    text-align: center;
  }

  .product-card h3 {
    margin-bottom: 10px;
    color: #333;
  }

  .product-card p {
    margin: 5px 0;
    font-size: 15px;
  }

  .error {
    margin-top: 30px;
    color: red;
    font-weight: bold;
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

<!-- Top Bar -->
<div class="top-bar">
  <button class="menu-icon" onclick="openMenu()">☰</button>
</div>

<!-- Sidebar Menu -->
<div id="sideMenu" class="side-menu">
  <button class="close-icon" onclick="closeMenu()">×</button>
  <a href="main.php">Home</a>
  <a href="compare.php">Compare Products</a>
  <a href="aboutus.php">About Us</a>
  <a href="signout.php">Sign Out</a>
</div>

<!-- Compare Section -->
<form method="POST" class="compare-container">
  <input type="text" name="product1" placeholder="Search first product" required>
  <span class="compare-icon">🔁</span>
  <input type="text" name="product2" placeholder="Search second product" required>
  <button type="submit">Compare</button>
</form>

<!-- Results -->
<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php elseif ($product1 && $product2): ?>
  <div class="results">
    <?php
      $stock1 = ($product1['StockQuantity'] > 0) ? "In Stock" : "Out of Stock";
      $stock2 = ($product2['StockQuantity'] > 0) ? "In Stock" : "Out of Stock";
    ?>
    <div class="product-card">
      <h3><?= htmlspecialchars($product1['Name']) ?></h3>
      <p><strong>Supplier:</strong> <?= htmlspecialchars($product1['Supplier']) ?></p>
      <p><strong>Price:</strong> ₱<?= htmlspecialchars($product1['Price']) ?></p>
      <p><strong>Stock:</strong> <?= $stock1 ?></p>
    </div>

    <div class="product-card">
      <h3><?= htmlspecialchars($product2['Name']) ?></h3>
      <p><strong>Supplier:</strong> <?= htmlspecialchars($product2['Supplier']) ?></p>
      <p><strong>Price:</strong> ₱<?= htmlspecialchars($product2['Price']) ?></p>
      <p><strong>Stock:</strong> <?= $stock2 ?></p>
    </div>
  </div>
<?php endif; ?>

<footer>HayahAI</footer>

<script>
function openMenu() {
  document.getElementById("sideMenu").style.width = "250px";
}

function closeMenu() {
  document.getElementById("sideMenu").style.width = "0";
}
</script>

</body>
</html>
