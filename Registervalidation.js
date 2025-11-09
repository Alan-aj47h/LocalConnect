document.addEventListener("DOMContentLoaded", function () {

  const signupForm = document.getElementById("signupForm");

  signupForm.addEventListener("submit", function (event) {
    let isFormValid = true;
    document.querySelectorAll(".error").forEach(el => el.textContent = "");
    const nameInput = document.getElementById("name");
    const nameValue = nameInput.value.trim();
    if (!/^[A-Za-z\s]{3,}$/.test(nameValue)) {
      document.getElementById("nameError").textContent = "Please enter a valid name (letters and spaces only, at least 3 characters).";
      isFormValid = false;
    }
    const emailInput = document.getElementById("email");
    const emailValue = emailInput.value.trim();
    if (!/^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|outlook\.com)$/.test(emailValue)) {
     document.getElementById("emailError").textContent =
    "Only Gmail, Yahoo, or Outlook email allowed.";
     isFormValid = false;
     }
    const phoneInput = document.getElementById("phone");
    const phoneValue = phoneInput.value.trim();
    if (!/^[6-9]\d{9}$/.test(phoneValue)) {
      document.getElementById("phoneError").textContent = "Please enter a valid 10-digit Indian mobile number.";
      isFormValid = false;
    }
    const addressInput = document.getElementById("address");
    const addressValue = addressInput.value.trim();
    if (addressValue.length < 10) {
      document.getElementById("addressError").textContent = "Address must be at least 10 characters long.";
      isFormValid = false;
    }
    const passwordInput = document.getElementById("password");
    const passwordValue = passwordInput.value;
    if (!/^(?=.*[A-Za-z])(?=.*\d).{6,}$/.test(passwordValue)) {
      document.getElementById("passwordError").textContent = "Password must be at least 6 characters and include both letters and numbers.";
      isFormValid = false;
    }
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const confirmPasswordValue = confirmPasswordInput.value;
    if (confirmPasswordValue === "") {
      document.getElementById("confirmPasswordError").textContent = "Please confirm your password.";
      isFormValid = false;
    } else if (passwordValue !== confirmPasswordValue) {
      document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
      isFormValid = false;
    }
    const roleSelected = document.querySelector('input[name="role"]:checked');
    if (!roleSelected) {
      document.getElementById("roleError").textContent = "Please select a role (Service Receiver or Provider).";
      isFormValid = false;
    }
    if (!isFormValid) {
      event.preventDefault();
    }
  });
});