document.addEventListener("DOMContentLoaded", function () {

  const signupForm = document.getElementById("signupForm");

  signupForm.addEventListener("submit", function (event) {
    let isFormValid = true;

    // Clear all previous error messages
    document.querySelectorAll(".error").forEach(el => el.textContent = "");

    // --- 1. Full Name Validation ---
    const nameInput = document.getElementById("name");
    const nameValue = nameInput.value.trim();
    if (!/^[A-Za-z\s]{3,}$/.test(nameValue)) {
      document.getElementById("nameError").textContent = "Please enter a valid name (letters and spaces only, at least 3 characters).";
      isFormValid = false;
    }

    // --- 2. Email Address Validation ---
    const emailInput = document.getElementById("email");
    const emailValue = emailInput.value.trim();
    if (!/^\S+@\S+\.\S+$/.test(emailValue)) {
      document.getElementById("emailError").textContent = "Please enter a valid email address.";
      isFormValid = false;
    }

    // --- 3. Phone Number Validation (for India) ---
    const phoneInput = document.getElementById("phone");
    const phoneValue = phoneInput.value.trim();
    if (!/^[6-9]\d{9}$/.test(phoneValue)) {
      document.getElementById("phoneError").textContent = "Please enter a valid 10-digit Indian mobile number.";
      isFormValid = false;
    }

    // --- 4. Address Validation ---
    const addressInput = document.getElementById("address");
    const addressValue = addressInput.value.trim();
    if (addressValue.length < 10) {
      document.getElementById("addressError").textContent = "Address must be at least 10 characters long.";
      isFormValid = false;
    }

    // --- 5. Password Validation ---
    const passwordInput = document.getElementById("password");
    const passwordValue = passwordInput.value;
    if (!/^(?=.*[A-Za-z])(?=.*\d).{6,}$/.test(passwordValue)) {
      document.getElementById("passwordError").textContent = "Password must be at least 6 characters and include both letters and numbers.";
      isFormValid = false;
    }

    // --- 6. Confirm Password Validation ---
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const confirmPasswordValue = confirmPasswordInput.value;
    if (confirmPasswordValue === "") {
      document.getElementById("confirmPasswordError").textContent = "Please confirm your password.";
      isFormValid = false;
    } else if (passwordValue !== confirmPasswordValue) {
      document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
      isFormValid = false;
    }

    // --- 7. Role Selection Validation ---
    const roleSelected = document.querySelector('input[name="role"]:checked');
    if (!roleSelected) {
      document.getElementById("roleError").textContent = "Please select a role (Service Receiver or Provider).";
      isFormValid = false;
    }

    // Prevent form submission if validation fails
    if (!isFormValid) {
      event.preventDefault();
    }
  });
});
