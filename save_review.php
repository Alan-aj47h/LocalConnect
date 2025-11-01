<?php
session_start();
include 'Login_dbconn.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to write a review.");
}

$appointment_id = intval($_POST['appointment_id'] ?? 0);
$review = trim($_POST['review'] ?? '');
$customer_id = $_SESSION['user_id'];

if ($appointment_id <= 0 || $review === '') {
    die("Invalid data.");
}

// Get provider_id from appointment (security check)
$stmt = $conn->prepare("SELECT provider_id FROM appointments WHERE appointment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $appointment_id, $customer_id);
$stmt->execute();
$stmt->bind_result($provider_id);
if ($stmt->fetch()) {
    $stmt->close();

    // Insert review into reviews table
    $stmt2 = $conn->prepare("INSERT INTO reviews (provider_id, customer_id, review) VALUES (?, ?, ?)");
    $stmt2->bind_param("iis", $provider_id, $customer_id, $review);
    if ($stmt2->execute()) {
        echo "Review saved successfully!";
    } else {
        echo "Error saving review.";
    }
    $stmt2->close();
} else {
    echo "Invalid appointment.";
}
$conn->close();
?>
