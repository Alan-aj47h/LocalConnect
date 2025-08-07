<?php
session_start();
include 'Login_dbconn.php';

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// Find user by email + role
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();

  if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Role-based redirection
    switch ($user['role']) {
      case 'admin':
        header("Location: dashboard_admin.php");
        break;
      case 'provider':
        header("Location: dashboard_provider.php");
        break;
      case 'customer':
        header("Location: dashboard_user.php");
        break;
      default:
        echo "Invalid role.";
        exit;
    }
    exit;
  } else {
    echo "Incorrect password.";
  }
} else {
  echo "Invalid email or role.";
}
?>
