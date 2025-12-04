<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'bagd');

if ($conn->connect_error) {
    echo json_encode(['success'=>false,'message'=>$conn->connect_error]);
    exit;
}

$result = $conn->query("SELECT * FROM delivery_requests WHERE status='pending' ORDER BY created_at DESC");
$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

echo json_encode(['success'=>true,'jobs'=>$jobs]);
$conn->close();
?>
