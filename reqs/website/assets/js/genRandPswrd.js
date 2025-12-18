// Generate a secure random integer between 0 and max - 1 without bias.
function secureRandomInt(max) {
  const randomBuffer = new Uint32Array(1);
  const range = 0x100000000; // 2^32
  const threshold = range - (range % max);
  let r;
  do {
    crypto.getRandomValues(randomBuffer);
    r = randomBuffer[0];
  } while (r >= threshold);
  return r % max;
}

// Fisher–Yates Shuffle to randomly shuffle an array
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = secureRandomInt(i + 1);
    [array[i], array[j]] = [array[j], array[i]];
  }
  return array;
}

// Function to generate a random password based on security measures
function generateRandomPassword(length = 18) {
  // Define character sets (no special chars in base part)
  const lowerCaseChars = "abcdefghijklmnopqrstuvwxyz";
  const upperCaseChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const numbers = "0123456789";

  // Branding suffix
  const suffix = "@Temp13+";
  const suffixLength = suffix.length;

  // Ensure password length is at least 20 for better security
  length = Math.max(length, 18);

  // Calculate the random part length (total length minus suffix)
  const randomPartLength = length - suffixLength;

  const passwordChars = [];

  // Ensure the password has at least one character from each required set
  passwordChars.push(lowerCaseChars[secureRandomInt(lowerCaseChars.length)]);
  passwordChars.push(upperCaseChars[secureRandomInt(upperCaseChars.length)]);
  passwordChars.push(numbers[secureRandomInt(numbers.length)]);

  // Combine all character sets into one string for further random selection
  const allChars = lowerCaseChars + upperCaseChars + numbers;
  for (let i = passwordChars.length; i < randomPartLength; i++) {
    passwordChars.push(allChars[secureRandomInt(allChars.length)]);
  }

  // Shuffle the random part and append the suffix
  return shuffleArray(passwordChars).join("") + suffix;
}

// Attach the password generation functionality to a button.
// This function only fills the new password field and reveals its content.
// The confirmation field remains untouched so the user must manually re-enter it.
function attachGeneratePasswordButton(buttonId, passwordFieldId, length = 18) {
  const button = document.getElementById(buttonId);
  if (!button) return; // Exit if the button is not found

  button.addEventListener("click", function (e) {
    e.preventDefault();
    const passwordField = document.getElementById(passwordFieldId);
    if (passwordField) {
      const generatedPassword = generateRandomPassword(length);
      passwordField.value = generatedPassword;

      // Change the input type to "text" so the password is immediately visible
      passwordField.setAttribute("type", "text");

      // Update the corresponding toggle icon (if any) to reflect that the password is now visible
      const toggleIcon = document.querySelector(
        '.toggle-password[data-target="' + passwordField.id + '"]'
      );
      if (toggleIcon) {
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
      }
    }
  });
}

// Wait for the DOM to load, then attach the generation functionality.
document.addEventListener("DOMContentLoaded", () => {
  // Example: For the reset password form:
  attachGeneratePasswordButton("generatePasswordReset", "newPasswordReset");
  attachGeneratePasswordButton("generatePasswordVerify", "newPassword");
  attachGeneratePasswordButton("generatePasswordprofile", "newPassword");
});
