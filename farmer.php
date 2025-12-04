<?php
// process.php
include 'config.php';

// Enable CORS for cross-domain requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required_fields = ['name', 'crop', 'variety', 'weight', 'price', 'harvest_date', 'storage_location'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $response["message"] = "Missing required fields: " . implode(", ", $missing_fields);
        echo json_encode($response);
        exit;
    }
    
    // Sanitize input data
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $crop = filter_var($_POST['crop'], FILTER_SANITIZE_STRING);
    $variety = filter_var($_POST['variety'], FILTER_SANITIZE_STRING);
    $weight = filter_var($_POST['weight'], FILTER_SANITIZE_NUMBER_INT);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $harvest_date = $_POST['harvest_date'];
    $storage_location = filter_var($_POST['storage_location'], FILTER_SANITIZE_STRING);
    
    // Handle file upload
    $photo_name = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $response["message"] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            echo json_encode($response);
            exit;
        }
        
        // Validate file size (max 5MB)
        if ($_FILES['photo']['size'] > 5000000) {
            $response["message"] = "File is too large. Maximum size is 5MB.";
            echo json_encode($response);
            exit;
        }
        
        $file_extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $photo_name = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $photo_name;
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $response["message"] = "Sorry, there was an error uploading your file.";
            echo json_encode($response);
            exit;
        }
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] != UPLOAD_ERR_NO_FILE) {
        $response["message"] = "File upload error: " . $_FILES['photo']['error'];
        echo json_encode($response);
        exit;
    }
    
    // Insert into database
    $sql = "INSERT INTO products (name, crop, variety, weight, price, harvest_date, storage_location, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response["message"] = "Database error: " . $conn->error;
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("sssiisss", $name, $crop, $variety, $weight, $price, $harvest_date, $storage_location, $photo_name);
    
    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Product published successfully!";
        $response["product_id"] = $stmt->insert_id;
    } else {
        $response["message"] = "Error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response["message"] = "Invalid request method";
}

$conn->close();
echo json_encode($response);
?>