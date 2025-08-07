<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - LocalConnect</title>
  <link rel="stylesheet" href="forgotpass.css">
</head>
<body>
  <div class="login-box">
    <h2>Forgot Password</h2>
    <form action="reset_password.php" method="POST">
      <div class="user-box">
        <input type="email" name="email" required>
        <label>Enter your registered email</label>
      </div>
      <button type="submit">Verify Email</button>
    </form>
  </div>
</body>
</html>
