<?php
  // cart.php
  session_start();
  if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
  }

  $user_id = $_SESSION['user_id'];
  
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "hayahai_db";

  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $query = "
      SELECT sl.ListID, sl.Quantity, p.ProductID, p.Name, p.Price, p.Supplier
      FROM shopping_list sl
      JOIN products p ON sl.ProductID = p.ProductID
      WHERE sl.UserID = ?
  ";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
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
    <h2>Your Shopping List</h2>
    
    <?php
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT sl.ProductID, sl.Quantity, p.Name, p.Price 
            FROM shopping_list sl
            JOIN products p ON sl.ProductID = p.ProductID
            WHERE sl.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table style='margin:auto; border-collapse:collapse; width:60%;'>";
        echo "<tr><th>Product</th><th>Quantity</th><th>Price</th><th></th><th></th></tr>";

        while ($row = $result->fetch_assoc()) {
            $formattedPrice = number_format((float)$row['Price'], 2);
            echo "<tr style='border-bottom:1px solid #ddd;'>";
            echo "<td>{$row['Name']}</td>";

            // Update Quantity Form
            echo "<td>
                    <form action='functions/update_quantity.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='ProductID' value='{$row['ProductID']}'>
                        <input type='number' name='Quantity' value='{$row['Quantity']}' min='1' style='width:50px; text-align:center;'>
                        <button type='submit'>Update</button>
                    </form>
                  </td>";

            echo "<td>$ {$formattedPrice}</td>";

            // Remove from List Form
            echo "<td>
                    <form action='functions/remove_item.php' method='POST' style='display:inline;'>
                        <input type='hidden' name='ProductID' value='{$row['ProductID']}'>
                        <button type='submit' style='color:red;'>Remove</button>
                    </form>
                  </td>";

            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>Your shopping list is currently empty.</p>";
    }
    ?>
  </div>

  <footer>HayahAI</footer>
</body>
</html>
