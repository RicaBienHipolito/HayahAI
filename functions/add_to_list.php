<?php
session_start();

// Connects to DB
$conn = new mysqli("localhost", "root", "", "hayahai_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['ProductID']);

// Check if product is already in cart/list
$check = $conn->prepare("SELECT Quantity FROM shopping_list WHERE UserID = ? AND ProductID = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    
    $row = $result->fetch_assoc(); // If item exists, item go up
    $newQty = $row['Quantity'] + 1;

    $update = $conn->prepare("UPDATE shopping_list SET Quantity = ? WHERE UserID = ? AND ProductID = ?");
    $update->bind_param("iii", $newQty, $user_id, $product_id);
    $update->execute();

} else {
    // Insert new item
    $insert = $conn->prepare("INSERT INTO shopping_list (UserID, ProductID, Quantity) VALUES (?, ?, 1)");
    $insert->bind_param("ii", $user_id, $product_id);
    $insert->execute();
}

echo json_encode(['status' => 'added']);
exit();
?>