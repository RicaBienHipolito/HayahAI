<?php
// register.php

// Database connection
$host = "localhost:3307";    // match your setup
$user = "root";              
$pass = "";                  
$dbname = "hayahai_db";      

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);

  // Check if email already exists
  $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $message = "Email already registered.";
  } else {
    // Insert plain text password (for testing purposes only)
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $password);

    if ($stmt->execute()) {
      // Redirect to sign in after successful registration
      header("Location: signin.php");
      exit();
    } else {
      $message = "Error: " . $stmt->error;
    }
    $stmt->close();
  }

  $check->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - HayahAI</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
    }

    .home-icon {
      position: absolute;
      top: 20px;
      left: 20px;
      cursor: pointer;
      font-size: 28px;
      text-decoration: none;
      color: black;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: -50px;
      width: 100%;
    }

    input[type="email"],
    input[type="password"] {
      width: 60%;
      max-width: 700px;
      padding: 10px 15px;
      margin: 10px 0;
      border-radius: 30px;
      border: 1px solid #aaa;
      background-color: #e6e6e6;
      font-size: 14px;
      outline: none;
      text-align: center;
    }

    button {
      border: 1px solid #888;
      border-radius: 20px;
      background-color: #e6e6e6;
      padding: 6px 25px;
      margin-top: 15px;
      cursor: pointer;
      transition: 0.3s;
      font-size: 14px;
    }

    button:hover {
      background-color: #f2f2f2;
    }

    .message {
      color: red;
      margin-top: 10px;
      font-size: 14px;
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
      letter-spacing: 1px;
    }
  </style>
</head>
<body>

  <a href="signin.php" class="home-icon">🏠</a>

  <form method="POST" action="">
    <input type="email" name="email" placeholder="Enter email" required />
    <input type="password" name="password" placeholder="Enter password" required />
    <button type="submit">Register</button>

    <?php if (!empty($message)) : ?>
      <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
  </form>

  <footer>
    HayahAI
  </footer>

</body>
</html>
