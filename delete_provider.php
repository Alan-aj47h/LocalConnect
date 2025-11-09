<?php
include 'Login_dbconn.php';
$id = intval($_POST['user_id']);
$conn->query("UPDATE users SET status='deleted' WHERE user_id=$id");
header("Location: dashboard_admin.php?tab=search&search=$id");
