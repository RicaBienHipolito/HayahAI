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

$stmt = $conn->prepare("DELETE FROM shopping_list WHERE UserID = ? AND ProductID = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

header("Location: ../cart.php");
exit();
?>