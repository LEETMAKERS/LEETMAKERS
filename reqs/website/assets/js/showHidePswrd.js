// Wait for the DOM to load before adding event listeners.
document.addEventListener("DOMContentLoaded", () => {
  // Select all toggle icons for password visibility
  const togglePasswords = document.querySelectorAll(".toggle-password");

  // Add click event listeners to each toggle icon
  togglePasswords.forEach((togglePassword) => {
    togglePassword.addEventListener("click", function () {
      // Get the target input field's ID from the data-target attribute
      const targetId = this.getAttribute("data-target");
      const passwordInput = document.getElementById(targetId);

      if (passwordInput) {
        // Toggle the type attribute between 'password' and 'text'
        const currentType = passwordInput.getAttribute("type");
        const newType = currentType === "password" ? "text" : "password";
        passwordInput.setAttribute("type", newType);

        // Toggle the eye icon classes to reflect the current state
        this.classList.toggle("fa-eye-slash");
        this.classList.toggle("fa-eye");
      } else {
        console.error(`Password input with id '${targetId}' not found.`);
      }
    });
  });
});
