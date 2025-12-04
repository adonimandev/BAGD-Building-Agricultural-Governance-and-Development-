<?php
// process_payment.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// DB connection
$servername = "localhost";
$username   = "root";   // your DB username
$password   = "";       // your DB password
$dbname     = "bagd";   // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Get raw POST body
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

// Extract customer info
$customer_name  = $conn->real_escape_string($data['customer_name']);
$customer_phone = $conn->real_escape_string($data['customer_phone']);
$customer_email = $conn->real_escape_string($data['customer_email']);

// Check if customer exists
$customer_id = null;
$sql_check = "SELECT id FROM customers WHERE phone = '$customer_phone' OR email = '$customer_email' LIMIT 1";
$result = $conn->query($sql_check);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customer_id = $row['id'];
} else {
    $sql_insert = "INSERT INTO customers (name, phone, email, created_at) 
                   VALUES ('$customer_name', '$customer_phone', '$customer_email', NOW())";
    if ($conn->query($sql_insert)) {
        $customer_id = $conn->insert_id;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to create customer"]);
        exit;
    }
}

// Order data
$delivery_address      = $conn->real_escape_string($data['delivery_address']);
$delivery_instructions = $conn->real_escape_string($data['delivery_instructions']);
$payment_method        = $conn->real_escape_string($data['payment_method']);
$products_total        = floatval($data['products_total']);
$delivery_fee          = floatval($data['delivery_fee']);
$tax                   = floatval($data['tax']);
$total_amount          = floatval($data['total_amount']);

// Insert into orders
$sql_order = "INSERT INTO orders (customer_id, products_total, delivery_fee, tax, total_amount, status, created_at)
              VALUES ($customer_id, $products_total, $delivery_fee, $tax, $total_amount, 'pending', NOW())";

if (!$conn->query($sql_order)) {
    echo json_encode(["success" => false, "message" => "Failed to create order"]);
    exit;
}
$order_id = $conn->insert_id;

// Insert order items
if (!empty($data['cart_items'])) {
    foreach ($data['cart_items'] as $item) {
        $product_id = intval($item['id']);
        $crop       = $conn->real_escape_string($item['crop']);
        $variety    = $conn->real_escape_string($item['variety'] ?? '');
        $price      = floatval($item['price']);
        $quantity   = intval($item['quantity']);
        $weight     = isset($item['weight']) ? floatval($item['weight']) : 0;

        $conn->query("INSERT INTO order_items (order_id, product_id, crop, variety, price, quantity, weight) 
                      VALUES ($order_id, $product_id, '$crop', '$variety', $price, $quantity, $weight)");
    }
}

// Insert payment
$telebirr_phone = isset($data['telebirr_phone']) ? $conn->real_escape_string($data['telebirr_phone']) : null;
$bank_name      = isset($data['bank_name']) ? $conn->real_escape_string($data['bank_name']) : null;
$bank_account   = isset($data['bank_account']) ? $conn->real_escape_string($data['bank_account']) : null;

$sql_payment = "INSERT INTO payments (order_id, method, amount, status, telebirr_phone, bank_name, bank_account, created_at)
                VALUES ($order_id, '$payment_method', $total_amount, 'pending',
                " . ($telebirr_phone ? "'$telebirr_phone'" : "NULL") . ",
                " . ($bank_name ? "'$bank_name'" : "NULL") . ",
                " . ($bank_account ? "'$bank_account'" : "NULL") . ",
                NOW())";

$conn->query($sql_payment);

// Insert delivery request
$sql_delivery = "INSERT INTO delivery_requests (order_id, address, instructions, fee, status, created_at)
                 VALUES ($order_id, '$delivery_address', '$delivery_instructions', $delivery_fee, 'pending', NOW())";

$conn->query($sql_delivery);

// ✅ Success response
echo json_encode(["success" => true, "message" => "Order placed successfully!", "order_id" => $order_id]);

$conn->close();
