<?php
session_start();
include 'Login_dbconn.php';
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "error: not_logged_in";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $provider_id = !empty($_POST['provider_id']) ? (int)$_POST['provider_id'] : null;
    $appointment_id = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;
    $message = trim($_POST['message'] ?? '');
    if ($message === '' || $user_id === null || $provider_id === null || $appointment_id === null) {
        echo "error: missing_data";
        exit;
    }

    $sql = "INSERT INTO complaints (user_id, provider_id, appointment_id, message)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "error: prepare_failed";
        exit;
    }
    if (!$stmt->bind_param("iiis", $user_id, $provider_id, $appointment_id, $message)) {
        echo "error: bind_failed";
        exit;
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: execute_failed";
    }
    
    $stmt->close();
    $conn->close();
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Raise a Complaint</title>
  <link rel="stylesheet" href="raise_complaintstyle.css" />
</head>
<body>
  <div class="complaint-container">
    <?php if (isset($_SESSION['user_id'])): ?>
    <form id="complaintForm" class="complaint-box" method="POST">
      <h2>Raise a Complaint</h2>
      <div class="form-group">
        <label for="provider_id">Provider ID :</label>
        <input type="number" id="provider_id" name="provider_id">
      </div>

      <div class="form-group">
        <label for="appointment_id">Appointment ID :</label>
        <input type="number" id="appointment_id" name="appointment_id">
      </div>

      <div class="form-group">
        <label for="message">Complaint Details:</label>
        <textarea id="message" name="message" rows="4" required></textarea>
      </div>

      <button type="submit" class="btn-submit">Submit Complaint</button>
    </form>
    <?php else: ?>
        <div class="complaint-box">
             <h2 style="color: #ff4d4d;">Access Denied</h2>
             <p class="info-text">You must be logged in to raise a complaint. Please log in and try again.</p>
        </div>
    <?php endif; ?>
  </div>
  
  <script>
    document.getElementById("complaintForm")?.addEventListener("submit", function (event) {
  event.preventDefault();

  const provider = document.getElementById("provider_id").value.trim();
  const appointment = document.getElementById("appointment_id").value.trim();
  const message = document.getElementById("message").value.trim();
  if(provider === "" || appointment === "") {
      alert("Provider ID and Appointment ID must be entered to register a complaint.");
      return; 
  }

  if(message === "") {
      alert("Complaint details cannot be empty.");
      return;
  }
  const formData = new FormData(this);

  fetch("raise_complaint.php", {
    method: "POST",
    body: formData
  })
  .then(r => r.text())
  .then(result => {
    const response = result.trim();
    if (response === "success") {
      alert("Your complaint has been registered. We will review it soon and contact you with any further updates from localconnect@gmail.com.");
      window.location.href = 'dashboard_customer.php'; 
    } else {
      console.error("Server response:", response);
      alert("Something went wrong. Please ensure you have filled out the details correctly and try again.");
    }
  })
  .catch(err => {
    console.error("Fetch error:", err);
    alert("A network error occurred. Please check your connection and try again.");
  });
});
  </script>
</body>
</html>