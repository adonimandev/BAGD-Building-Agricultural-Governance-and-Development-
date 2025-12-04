<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'bagd');

$order_id = $_POST['order_id'] ?? '';
$hauler_name = $_POST['hauler_name'] ?? '';

if (!$order_id || !$hauler_name) {
    echo json_encode(['success'=>false,'message'=>'Order ID and Hauler required']);
    exit;
}

$stmt = $conn->prepare("UPDATE delivery_requests SET status='accepted', hauler_name=? WHERE order_id=?");
$stmt->bind_param("ss", $hauler_name, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Job accepted']);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
