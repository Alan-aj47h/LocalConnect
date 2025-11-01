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
        // Password is correct, set session variables
        $_SESSION['user_id'] = $user['user_id'];  
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        switch ($user['role']) {
            case 'admin':
                header("Location: dashboard_admin.php");
                break;
            case 'provider':
                header("Location: dashboard_provider.php");
                break;
            case 'customer':
                header("Location: dashboard_customer.php");
                break;
            default:
                // This case should not be reached with valid data
                $_SESSION['login_error'] = "Invalid role detected.";
                header("Location: Login.php");
                break;
        }
        exit;

    } else {
        $_SESSION['login_error'] = "Wrong password. Please re-enter the correct password.";
        header("Location: Login.php?role=" . urlencode($role));
        exit;
    }
} else {
    // Invalid email or role
    $_SESSION['login_error'] = "Invalid email or role.";
    header("Location: Login.php?role=" . urlencode($role));
    exit;
}
?>