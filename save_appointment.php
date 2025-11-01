<?php
session_start();
include 'Login_dbconn.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to confirm appointment.");
}

$customer_id = $_SESSION['user_id'];
$provider_id = intval($_POST['provider_id']);
$service_id = intval($_POST['service_id']);
$address    = $_POST['address'];
$landmark   = $_POST['landmark'];
$message    = $_POST['message'];
$appointment_date = date("Y-m-d H:i:s"); 
$status = "Pending";
$stmt = $conn->prepare("INSERT INTO appointments 
    (user_id, provider_id, service_id, appointment_date, address, landmark, message, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisssss", $customer_id, $provider_id, $service_id, $appointment_date, $address, $landmark, $message, $status);

if ($stmt->execute()) {
    header("Location: dashboard_customer.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>
