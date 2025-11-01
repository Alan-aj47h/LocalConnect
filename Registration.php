<?php
$conn = new mysqli("localhost", "root", "", "localconnect");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$name     = $_POST['name'];
$email    = $_POST['email'];
$phone    = $_POST['phone'];
$address  = $_POST['address'];
$role     = $_POST['role']; 
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo "<script>alert('Email already used. Please use a different email.'); window.location.href = 'register.html';</script>";
    exit();
}
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, address, password, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $email, $phone, $address, $password, $role);

if ($stmt->execute()) {
    echo "<script>alert('Account created successfully. Redirecting to login.'); window.location.href = 'Login.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>