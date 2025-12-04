<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Database connection
$host = 'localhost';
$db = 'bagd';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// Insert order
$cartItemsJson = $conn->real_escape_string(json_encode($data['cartItems']));
$address = $conn->real_escape_string($data['deliveryAddress']);
$paymentMethod = $conn->real_escape_string($data['paymentMethod']);
$subtotal = $data['subtotal'];
$deliveryFee = $data['deliveryFee'];
$tax = $data['tax'];
$total = $subtotal + $deliveryFee + $tax;

$sql = "INSERT INTO orders (cart_items, delivery_address, payment_method, subtotal, delivery_fee, tax, total)
        VALUES ('$cartItemsJson', '$address', '$paymentMethod', $subtotal, $deliveryFee, $tax, $total)";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
?>
