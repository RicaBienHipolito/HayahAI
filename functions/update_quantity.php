<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "hayahai_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['ProductID']);
$new_quantity = intval($_POST['Quantity']);

// Prevent quantity from going below 1
if ($new_quantity < 1) {
    $new_quantity = 1;
}

$stmt = $conn->prepare("UPDATE shopping_list SET Quantity = ? WHERE UserID = ? AND ProductID = ?");
$stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
$stmt->execute();

// Send user back to cart
header("Location: ../cart.php");
exit();
?>