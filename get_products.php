<?php
// get_products.php
include 'config.php';

// Fetch all active products
$sql = "SELECT id, name, crop, variety, weight, price, harvest_date, storage_location, photo 
        FROM products 
        WHERE status = 'active' 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(["success" => true, "products" => $products]);
} else {
    echo json_encode(["success" => false, "message" => "No products found"]);
}

$conn->close();
?>