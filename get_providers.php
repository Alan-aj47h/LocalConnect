<?php
header('Content-Type: application/json; charset=utf-8');
include 'Login_dbconn.php';

$location = isset($_GET['manual']) ? trim($_GET['manual']) : '';
$service  = isset($_GET['service']) ? trim($_GET['service']) : '';

$response = [];

if ($location === '') {
    echo json_encode($response);
    exit;
}

$sql = "SELECT sd.service_id, sd.business_name, sd.category, sd.price, sd.locations 
        FROM service_details sd
        WHERE sd.locations LIKE ?";
$params = ["%$location%"];

if ($service !== '' && $service !== 'All Services') {
    $sql .= " AND sd.category = ?";
    $params[] = $service;
}

$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $response[] = [
        "id"      => (int)$row['service_id'], 
        "name"    => $row['business_name'],
        "service" => $row['category'],
        "price"   => $row['price']
    ];
}

echo json_encode($response);
?>
