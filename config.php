<?php
$servername = "localhost:3307";
$username = "root";   // default for XAMPP
$password = "";       // default for XAMPP (no password)
$dbname = "hayahai_db"; // replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
