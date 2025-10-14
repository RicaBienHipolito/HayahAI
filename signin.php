<?php
session_start();

// Database connection
$servername = "localhost:3306";  // or localhost:3307 if needed
$username = "root";
$password = "";
$dbname = "hayahai_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $db_email, $db_password);
            $stmt->fetch();

            if ($password === $db_password) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $db_email;
                header("Location: main.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - HayahAI</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 40px 50px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
      background-color: #fff;
      width: 300px;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    button {
      background-color: #ffc65b;
      border: none;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      transition: 0.3s;
    }

    button:hover {
      background-color: #e0aa45;
    }

    p {
      margin-top: 15px;
      font-size: 14px;
    }

    a {
      color: #ffc65b;
      text-decoration: none;
    }

    .error {
      color: red;
      margin-bottom: 15px;
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #ffc55c;
      text-align: center;
      padding: 10px 0;
      font-size: 20px;
      font-weight: bold;
      color: white;
      letter-spacing: 1px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Sign In</h2>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Sign In</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
  </div>

  <footer>HayahAI</footer>
</body>
</html>
