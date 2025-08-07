<?php
include 'Login_dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Password updated successfully!'); window.location.href='Login.php';</script>";
    } else {
        echo "<script>alert('Error updating password.'); window.location.href='forgot_password.php';</script>";
    }
}
?>
