<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$question = trim($_POST['question'] ?? '');
if ($question === '') {
    echo json_encode(['error' => 'Empty question']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hayahai_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_errno) {
    echo json_encode(['error' => 'DB connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Dictionary for confirmation replies
$confirmation_phrases = ['yes', 'sure', 'okay', 'add it', 'yep', 'yeah'];
$is_confirmation = in_array(strtolower(trim($question)), $confirmation_phrases);

// Insert user message
$stmt = $conn->prepare("INSERT INTO customer_query (user_id, message, response) VALUES (?, ?, '')");
$stmt->bind_param("is", $user_id, $question);
$stmt->execute();
$stmt->close();

// Add to cart logic
if ($is_confirmation) {
    $stmt = $conn->prepare("SELECT suggested_product_id FROM customer_query WHERE user_id = ? AND suggested_product_id IS NOT NULL ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_row = $result->fetch_assoc();
    $product_id = $product_row['suggested_product_id'] ?? null;

    if ($product_id) {
        $check = $conn->prepare("SELECT Quantity FROM shopping_list WHERE UserID = ? AND ProductID = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $cart_row = $res->fetch_assoc();
            $newQty = $cart_row['Quantity'] + 1;
            $update = $conn->prepare("UPDATE shopping_list SET Quantity = ? WHERE UserID = ? AND ProductID = ?");
            $update->bind_param("iii", $newQty, $user_id, $product_id);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO shopping_list (UserID, ProductID, Quantity) VALUES (?, ?, 1)");
            $insert->bind_param("ii", $user_id, $product_id);
            $insert->execute();
        }

        echo json_encode([
            'user' => $question,
            'bot' => 'Item added to your cart ✅'
        ]);
        exit();
    } else {
        echo json_encode([
            'user' => $question,
            'bot' => 'Sorry, I couldn’t find a product to add.'
        ]);
        exit();
    }
}

// Run Python and capture reply directly
$config = require(__DIR__ . '/py_path.php');
$python = $config['python_path'];
$script = '"' . __DIR__ . '\\hayahai.py' . '"';
exec("\"$python\" $script 2>&1", $output, $return_var);
$bot_reply = implode("\n", $output);

echo json_encode([
    'user' => $question,
    'bot' => $bot_reply ?: 'No reply'
]);