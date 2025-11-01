<?php
session_start();
include 'Login_dbconn.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to save a service.");
}

$provider_id   = $_SESSION['user_id'];
$business_name = $_POST['service_name'] ?? '';
$category      = $_POST['category'] ?? '';
$about_service = $_POST['about_service'] ?? '';
$price         = $_POST['service_price'] ?? '';
$locations     = $_POST['locations'] ?? '';

if (empty($business_name) || empty($category) || empty($price)) {
    die("Business Name, Category, and Price are required.");
}
$stmt = $conn->prepare("SELECT service_id FROM service_details WHERE provider_id = ?");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE service_details 
        SET business_name=?, category=?, about_service=?, price=?, locations=? 
        WHERE provider_id=?");
    $stmt->bind_param("sssssi", $business_name, $category, $about_service, $price, $locations, $provider_id);
} else {
    $stmt = $conn->prepare("INSERT INTO service_details 
        (provider_id, business_name, category, about_service, price, locations) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $provider_id, $business_name, $category, $about_service, $price, $locations);
}

if ($stmt->execute()) {
    header("Location: dashboard_provider.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
