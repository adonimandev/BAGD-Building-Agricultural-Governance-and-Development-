<?php
session_start();

// Check if hauler is logged in
if(isset($_SESSION['hauler_id'])){
    // Logged in → go to dashboard
    header('Location: hauler.html');
    exit;
} else {
    // Not logged in → go to login page
    header('Location: hauler-login.html');
    exit;
}
?>
