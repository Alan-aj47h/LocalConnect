<?php
session_start();
include 'Login_dbconn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die("Access denied. Admins only.");
}
$admin_user_id = $_SESSION['user_id'];
$admin_name = 'Admin'; 
$sql_admin = "SELECT name FROM users WHERE user_id = ?";
if ($stmt_admin = $conn->prepare($sql_admin)) {
    $stmt_admin->bind_param("i", $admin_user_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    if ($user_row = $result_admin->fetch_assoc()) {
        $admin_name = htmlspecialchars($user_row['name']);
    }
    $stmt_admin->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_complaint' && isset($_POST['complaint_id'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $sql = "UPDATE complaints SET status = 'resolved' WHERE complaint_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard_admin.php?tab=complaints"); 
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
    $sql = "UPDATE users SET status = ? WHERE user_id = ? AND role = 'provider'";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $status, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}
$complaints = [];
$csql = "SELECT c.*, u.name AS customer_name, u.email AS customer_email
         FROM complaints c
         JOIN users u ON c.user_id = u.user_id
         WHERE c.status = 'pending'
         ORDER BY c.created_at DESC";
if ($cres = $conn->query($csql)) {
    while ($row = $cres->fetch_assoc()) $complaints[] = $row;
}
$provider_details = null;
$provider_appointments = [];
if (isset($_GET['tab']) && $_GET['tab'] === 'search' && !empty($_GET['search'])) {
    $provider_id = intval($_GET['search']);
    $details_sql = "SELECT u.email, sd.business_name, sd.about_service, sd.price, sd.locations
                    FROM users u
                    LEFT JOIN service_details sd ON u.user_id = sd.provider_id
                    WHERE u.user_id = ? AND u.role = 'provider'";
    if ($stmt = $conn->prepare($details_sql)) {
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $provider_details = $result->fetch_assoc();
        $stmt->close();
    }
    if ($provider_details) {
        $appt_sql = "SELECT a.appointment_id, a.appointment_date, a.status, c.name as customer_name, c.email as customer_email
                     FROM appointments a
                     JOIN users c ON a.user_id = c.user_id
                     WHERE a.provider_id = ?
                     ORDER BY a.appointment_date DESC";
        if ($stmt = $conn->prepare($appt_sql)) {
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $provider_appointments[] = $row;
            }
            $stmt->close();
        }
    }
}
$pending_providers = [];
$psql = "SELECT u.user_id, u.name, u.email, u.phone, u.address,
                sd.business_name, sd.category, sd.about_service, sd.price, sd.locations
         FROM users u
         LEFT JOIN service_details sd ON u.user_id = sd.provider_id
         WHERE u.role='provider' AND u.status='pending'";
if ($pres = $conn->query($psql)) {
    while ($r = $pres->fetch_assoc()) $pending_providers[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - LocalConnect</title>
  <link rel="stylesheet" href="admin_dashboard_style.css">
</head>
<body>
<div class="container">
  <header class="header">
    <h1>LocalConnect</h1>
    <p>Welcome, <?= $admin_name ?>, Manage users and complaints here.</p>
  </header>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <ul>
        <li><a href="?tab=complaints" class="<?= ($_GET['tab'] ?? '') === 'complaints' ? 'active' : '' ?>">Complaints</a></li>
        <li><a href="?tab=search" class="<?= ($_GET['tab'] ?? '') === 'search' ? 'active' : '' ?>">Search Provider</a></li>
        <li><a href="?tab=verify" class="<?= ($_GET['tab'] ?? '') === 'verify' ? 'active' : '' ?>">Verify Provider</a></li>
      </ul>
    </aside>
    <main class="main-content">
      <?php $tab = $_GET['tab'] ?? 'complaints';  ?>
      <?php if ($tab === 'complaints'): ?>
        <h2>Complaints</h2>
        <?php if (!empty($complaints)): ?>
          <?php foreach ($complaints as $c): ?>
            <div class="appt-box">
              <p><strong>Customer Name:</strong> <?= htmlspecialchars($c['customer_name'] ?? 'N/A') ?></p>
              <p><strong>Customer Email:</strong> <?= htmlspecialchars($c['customer_email'] ?? 'N/A') ?></p>
              <p><strong>Provider ID:</strong> <?= htmlspecialchars($c['provider_id'] ?? 'N/A') ?></p>
              <p><strong>Appointment ID:</strong> <?= htmlspecialchars($c['appointment_id'] ?? 'N/A') ?></p>
              <p><strong>Complaint:</strong> <?= nl2br(htmlspecialchars($c['message'] ?? '')) ?></p>
              <small><strong>Reported on:</strong> <?= htmlspecialchars($c['created_at'] ?? '') ?></small>
              <form method="post" action="dashboard_admin.php?tab=complaints" style="margin-top: 10px;">
                  <input type="hidden" name="complaint_id" value="<?= intval($c['complaint_id']) ?>">
                  <button type="submit" name="action" value="resolve_complaint" class="btn btn-primary">Mark as Resolved</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No pending complaints found.</p>
        <?php endif; ?>
      <?php elseif ($tab === 'search'): ?>
        <h2>Search Provider</h2>
        <form method="get" style="margin-bottom:15px;">
          <input type="hidden" name="tab" value="search">
          <input class="provider-search-input" type="number" name="search" placeholder="Enter Provider ID" required value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <?php if (isset($_GET['search'])): ?>
            <?php if ($provider_details): ?>
              <?php
              $psql = $conn->prepare("SELECT status FROM users WHERE user_id=? LIMIT 1");
              $psql->bind_param("i", $provider_id);
              $psql->execute();
              $pstatus = $psql->get_result()->fetch_assoc()['status'];
              ?>
                <div class="appt-box">
               <h3>Provider Details</h3>
               <p><strong>Business Name:</strong> <?= htmlspecialchars($provider_details['business_name'] ?? 'Not set') ?></p>
               <p><strong>Email:</strong> <?= htmlspecialchars($provider_details['email'] ?? 'N/A') ?></p>
               <p><strong>Description:</strong> <?= htmlspecialchars($provider_details['about_service'] ?? 'Not set') ?></p>
               <p><strong>Price Range:</strong> <?= htmlspecialchars($provider_details['price'] ?? 'Not set') ?></p>
               <p><strong>Locations:</strong> <?= htmlspecialchars($provider_details['locations'] ?? 'Not set') ?></p>
               <div class="provider-action-buttons">
              <?php if($pstatus === 'approved'): ?>
            <form method="post" action="suspend_provider.php">
                <input type="hidden" name="user_id" value="<?= $provider_id ?>">
                <button class="btn btn-warning provider-btn">Suspend</button>
            </form>
        <?php elseif($pstatus === 'suspended'): ?>
            <form method="post" action="unsuspend_provider.php">
                <input type="hidden" name="user_id" value="<?= $provider_id ?>">
                <button class="btn btn-primary provider-btn">Un-Suspend</button>
            </form>
        <?php endif; ?>
        <?php if($pstatus !== 'deleted'): ?>
           <form method="post" action="delete_provider.php" onsubmit="return confirm('Are you sure you want to permanently delete this provider? This action cannot be undone.');">
            <input type="hidden" name="user_id" value="<?= $provider_id ?>">
            <button class="btn btn-danger provider-btn">Delete</button>
           </form>
       <?php else: ?>
          <div style="padding:10px;color:#b30000;font-weight:600;">
            Provider account is permanently deleted.
<         /div>
       <?php endif; ?>
     </div>
    </div>
                <h3 style="margin-top: 20px;">Appointments Received</h3>
                <?php if (!empty($provider_appointments)): ?>
                    <?php foreach ($provider_appointments as $appt): ?>
                        <div class="appt-box">
                            <p><strong>Appointment ID:</strong> <?= htmlspecialchars($appt['appointment_id']) ?></p>
                            <p><strong>Customer:</strong> <?= htmlspecialchars($appt['customer_name']) ?> (<?= htmlspecialchars($appt['customer_email']) ?>)</p>
                            <p><strong>Date:</strong> <?= htmlspecialchars($appt['appointment_date']) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($appt['status']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>This provider has no appointments on record.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No provider found with that ID.</p>
            <?php endif; ?>
        <?php endif; ?>
      <?php elseif ($tab === 'verify'): ?>
        <h2>Verify New Providers</h2>
        <?php if (!empty($pending_providers)): ?>
          <?php foreach ($pending_providers as $p): ?>
            <div class="appt-box">
              <p><strong><?= htmlspecialchars($p['name']) ?></strong> (<?= htmlspecialchars($p['email']) ?>)</p>
              <p>Phone: <?= htmlspecialchars($p['phone']) ?></p>
<p>Address: <?= htmlspecialchars($p['address']) ?></p>
<div style="margin-top:12px;padding:12px;background:#2b2f33;border-radius:8px;">
  <h4 style="margin:0 0 8px 0;color:#ffffff;font-size:16px;">Business Profile</h4>
  <p><strong>Business Name:</strong> <?= htmlspecialchars($p['business_name'] ?? 'Not Provided') ?></p>
  <p><strong>Category:</strong> <?= htmlspecialchars($p['category'] ?? 'Not Provided') ?></p>
  <p><strong>About Service:</strong> <?= nl2br(htmlspecialchars($p['about_service'] ?? 'Not Provided')) ?></p>
  <p><strong>Price:</strong> <?= htmlspecialchars($p['price'] ?? 'Not Provided') ?></p>
  <p><strong>Locations:</strong> <?= htmlspecialchars($p['locations'] ?? 'Not Provided') ?></p>
</div>
<form method="post" class="appt-actions" style="margin-top:10px;">
                <input type="hidden" name="user_id" value="<?= intval($p['user_id']) ?>">
                <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No pending providers found.</p>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
</div>
</body>
</html>