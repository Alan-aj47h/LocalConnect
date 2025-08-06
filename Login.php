<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - LocalConnect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="Loginstyle.css">
</head>
<body>
  <div class="login-container">
    <h2>Welcome Back</h2>
    <p>Please log in to your account</p>

    <div class="role-tabs">
      <button type="button" id="customerTab" onclick="setRole('customer')">Customer</button>
      <button type="button" id="providerTab" onclick="setRole('provider')">Service Provider</button>
      <button type="button" id="adminTab" onclick="setRole('admin')">Admin</button>
    </div>

    <form action="Login_action.php" method="POST">
      <input type="hidden" name="role" id="role" value="">
      <input type="text" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <p><a class="forgot-password" href="forgot_password.php">Forgot Password?</a></p>
      <button type="submit" class="signin-btn">Sign In</button>
    </form>
    <div id="orDivider" class="or-divider">or</div>
    <button id="createAccountBtn" class="create-account" onclick="window.location.href='Registration.html'">Create New Account</button>
  </div>

  <script>
    function getRoleFromQuery() {
      const params = new URLSearchParams(window.location.search);
      return params.get('role');
    }

    function setRole(role) {
      document.getElementById('role').value = role;
      document.querySelectorAll('.role-tabs button').forEach(btn => btn.classList.remove('active'));
      const activeTab = document.getElementById(role + "Tab");
      if (activeTab) activeTab.classList.add('active');

      const orDivider = document.getElementById('orDivider');
      const createAccountBtn = document.getElementById('createAccountBtn');

      if (role === 'admin') {
        orDivider.style.display = 'none';
        createAccountBtn.style.display = 'none';
      } else {
        orDivider.style.display = 'block';
        createAccountBtn.style.display = 'inline-block';
      }
    }

    window.onload = function () {
      let role = getRoleFromQuery();
      if (!role) {
        role = sessionStorage.getItem("selectedRole");
      }
      if (!role) {
        role = "customer";
      }
      setRole(role);
    };
  </script>
</body>
</html>
