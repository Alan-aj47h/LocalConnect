<?php
include 'Login_dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Reset Password</title>
          <link rel="stylesheet" href="resetpass.css">
        </head>
        <body>
          <div class="login-box">
            <h2>Reset Password</h2>
            <form action="update_password.php" method="POST">
              <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
              <div class="user-box">
                <input type="password" name="new_password" required>
                <label>New Password</label>
              </div>
              <button type="submit">Update Password</button>
            </form>
          </div>
        </body>
        </html>
        <?php
    } else {
        echo "<script>alert('Email not found!'); window.location.href='forgot_password.php';</script>";
    }
}
?>
