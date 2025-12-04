<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors',1); error_reporting(E_ALL);

$host='localhost'; $user='root'; $pass=''; $db='bagd';

$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){ echo json_encode(["success"=>false,"message"=>"DB failed"]); exit; }

$fullName = trim($_POST['fullName']??'');
$phone = trim($_POST['phoneNumber']??'');
$license = trim($_POST['license']??'');
$plate = trim($_POST['licensePlate']??'');
$carType = trim($_POST['carType']??'');
$password = trim($_POST['password']??'');

if(!$fullName||!$phone||!$license||!$plate||!$password){
    echo json_encode(["success"=>false,"message"=>"Missing fields"]); exit;
}

// Escape inputs
$fullName=$conn->real_escape_string($fullName);
$phone=$conn->real_escape_string($phone);
$license=$conn->real_escape_string($license);
$plate=$conn->real_escape_string($plate);
$carType=$conn->real_escape_string($carType);

// Image upload
$carImagePath='';
if(isset($_FILES['carImage']) && $_FILES['carImage']['error']===UPLOAD_ERR_OK){
    $imgTmp=$_FILES['carImage']['tmp_name'];
    $imgName=basename($_FILES['carImage']['name']);
    $targetDir='uploads/';
    if(!is_dir($targetDir)) mkdir($targetDir,0777,true);
    $carImagePath = $targetDir.uniqid('car_').'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$imgName);
    if(!move_uploaded_file($imgTmp,$carImagePath)){
        echo json_encode(["success"=>false,"message"=>"Failed to upload image"]); exit;
    }
}

// Check existing user
$checkStmt=$conn->prepare("SELECT id FROM hauler WHERE phone_number=? OR license_number=?");
$checkStmt->bind_param('ss',$phone,$license);
$checkStmt->execute(); $checkStmt->store_result();
if($checkStmt->num_rows>0){ echo json_encode(["success"=>false,"message"=>"You are already registered"]); exit; }
$checkStmt->close();

// Hash password
$passHash = password_hash($password,PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO hauler (full_name, phone_number, license_number, license_plate, car_type, car_image, password) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param('sssssss',$fullName,$phone,$license,$plate,$carType,$carImagePath,$passHash);

if($stmt->execute()){
    $_SESSION['hauler_id']=$stmt->insert_id;
    $_SESSION['hauler_name']=$fullName;
    echo json_encode(["success"=>true,"message"=>"Registered"]);
}else{
    echo json_encode(["success"=>false,"message"=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
