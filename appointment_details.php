<?php
session_start();
include 'Login_dbconn.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$customer_id = $_SESSION['user_id'];

// Check provider & service
if (!isset($_GET['provider_id']) || !isset($_GET['service_id'])) {
    die("Missing provider or service information.");
}

$provider_id = intval($_GET['provider_id']);
$service_id = intval($_GET['service_id']);

// Fetch customer details
$stmt = $conn->prepare("SELECT name, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Appointment</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f4f4f4; }
        form { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
        button { margin-top: 20px; padding: 10px; width: 100%; font-size: 16px; cursor: pointer; background: #4CAF50; color: #fff; border: none; border-radius: 5px; }
        button:hover { background: #45a049; }
    </style>
    <script>
        function showPopup() {
            alert("Appointment placed! Your order will soon be reviewed by the provider.");
        }
    </script>
</head>
<body>
    <h2 style="text-align:center;">Confirm Your Appointment</h2>
    <form action="save_appointment.php" method="POST" onsubmit="showPopup()">
        <input type="hidden" name="provider_id" value="<?php echo $provider_id; ?>">
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">

        <label>Your Name</label>
        <input type="text" value="<?php echo htmlspecialchars($customer['name']); ?>" readonly>

        <label>Address for Communication</label>
        <textarea name="address" required><?php echo htmlspecialchars($customer['address']); ?></textarea>

        <label>Landmark (Optional)</label>
        <input type="text" name="landmark">

        <label>Important Message to Provider</label>
        <textarea name="message"></textarea>

        <button type="submit">Confirm Appointment</button>
    </form>
</body>
</html>
