<?php
session_start();
include 'Login_dbconn.php';
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}
$customer_id = $_SESSION['user_id'];
if (!isset($_GET['provider_id']) || !isset($_GET['service_id'])) {
    die("Missing provider or service information.");
}

$provider_id = intval($_GET['provider_id']);
$service_id = intval($_GET['service_id']);

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
        body { font-family: Arial, sans-serif; padding: 30px; background: #111827; color: #fff; }
        form { max-width: 500px; margin: auto; background: #1f2937; padding: 20px; border-radius: 12px; box-shadow: 0 6px 12px rgba(0,0,0,0.4); }
        label { display: block; margin-top: 10px; font-weight: bold; color: #ddd; }
        input, textarea { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #374151; border-radius: 6px; background: #111827; color: #fff; }
        button { margin-top: 20px; padding: 12px; width: 100%; font-size: 16px; cursor: pointer; background: #3b82f6; color: #fff; border: none; border-radius: 8px; }
        button:hover { background: #2563eb; }
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

        <label>Important Message to Provider (Optional) </label>
        <textarea name="message"></textarea>

        <button type="submit">Confirm Appointment</button>
    </form>
</body>
</html>
