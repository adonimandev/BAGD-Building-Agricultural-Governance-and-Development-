<?php
header('Content-Type: application/json');

// --- Database configuration ---
$host = 'localhost';
$db   = 'bagd';
$user = 'root';  // no password
$pass = '';
$charset = 'utf8mb4';

// Set DSN
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// --- Convert addresses to lat/lng using OpenStreetMap Nominatim API ---
function geocode($address) {
    if (!$address) return null;
    $address = urlencode($address);
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
    $response = @file_get_contents($url);
    if (!$response) return null;
    $data = json_decode($response, true);
    if (!$data || !isset($data[0]['lat'])) return null;
    return [floatval($data[0]['lat']), floatval($data[0]['lon'])];
}

try {
    // --- Fetch latest pickup and dropoff ---
    $stmt = $pdo->query("SELECT pickup_location, dropoff_location FROM delivery_requests ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode([
            'success' => false,
            'message' => 'No pickup data found',
            'start' => [9.03, 38.74],   // Fallback Addis Ababa
            'end'   => [11.60, 37.39]   // Fallback Bahir Dar
        ]);
        exit;
    }

    $pickup = $row['pickup_location'];
    $dropoff = $row['dropoff_location'];

    $start = geocode($pickup);
    $end = geocode($dropoff);

    // Fallback if geocoding fails
    if (!$start) $start = [9.03, 38.74];
    if (!$end)   $end   = [11.60, 37.39];

    echo json_encode([
        'success' => true,
        'pickup' => $pickup,
        'dropoff' => $dropoff,
        'start' => $start,
        'end' => $end
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Query failed',
        'start' => [9.03, 38.74],
        'end'   => [11.60, 37.39]
    ]);
    exit;
}
