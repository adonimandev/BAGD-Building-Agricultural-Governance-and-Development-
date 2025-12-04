<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'bagd'); // change credentials

if ($conn->connect_error) {
    echo json_encode(['success'=>false,'message'=>$conn->connect_error]);
    exit;
}

$order_id = $_POST['order_id'] ?? '';
$pickup = $_POST['pickup'] ?? '';
$dropoff = $_POST['dropoff'] ?? '';

if (!$order_id || !$pickup || !$dropoff) {
    echo json_encode(['success'=>false,'message'=>'All fields required']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO delivery_requests (order_id, pickup_location, dropoff_location) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $order_id, $pickup, $dropoff);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Pickup requested']);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
