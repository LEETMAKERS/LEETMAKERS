<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['id'])) {
    header("Location: ../dashboard");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <!--=============== REMIXICONS ===============-->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css">
      <!--=============== FONT AWESOME ===============-->
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
      <link rel="stylesheet" href="/assets/css/forms.css" />
      <link rel="stylesheet" href="/assets/css/notifier.css" />
      <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="64x64">
      <title>LEET MAKERS - Secure Account</title>
   </head>
   <body>
      <ul class="notifications"></ul>
      <div id="session-messages"
         data-error="<?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>"
         data-success="<?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>"
         data-warning="<?php echo isset($_SESSION['warning']) ? $_SESSION['warning'] : ''; ?>"
         data-info="<?php echo isset($_SESSION['info']) ? $_SESSION['info'] : ''; ?>">
      </div>
      <?php unset($_SESSION['error'], $_SESSION['success'], $_SESSION['warning'], $_SESSION['info']); // Clear session messages ?>
      <div class="container" id="container">
         <div class="forms-container">
            <div class="verify-reset">
               <!-- Verify Account Form -->
               <form action="/auth/verifyAccount" method="post" class="vrfy-accnt-form" id="vrfyAccountForm" autocomplete="off">
                  <h2 class="title-long">Activate Your Account</h2>
                  <!-- OTP Section -->
                  <div class="otp-section">
                     <label for="otp1" class="otp-label">Please Enter the OTP Sent to Your Email</label>
                     <div class="otp-container">
                        <input type="text" id="otp1" name="otp1" value="<?php echo isset($_SESSION['temp_data']['otp1']) ? htmlspecialchars($_SESSION['temp_data']['otp1']) : ''; ?>" class="otp-input" maxlength="1" autocomplete="off"/>
                        <input type="text" id="otp2" name="otp2" value="<?php echo isset($_SESSION['temp_data']['otp2']) ? htmlspecialchars($_SESSION['temp_data']['otp2']) : ''; ?>" class="otp-input" maxlength="1" autocomplete="off"/>
                        <input type="text" id="otp3" name="otp3" value="<?php echo isset($_SESSION['temp_data']['otp3']) ? htmlspecialchars($_SESSION['temp_data']['otp3']) : ''; ?>" class="otp-input" maxlength="1" autocomplete="off"/>
                        <input type="text" id="otp4" name="otp4" value="<?php echo isset($_SESSION['temp_data']['otp4']) ? htmlspecialchars($_SESSION['temp_data']['otp4']) : ''; ?>" class="otp-input" maxlength="1" autocomplete="off"/>
                        <input type="text" id="otp5" name="otp5" value="<?php echo isset($_SESSION['temp_data']['otp5']) ? htmlspecialchars($_SESSION['temp_data']['otp5']) : ''; ?>" class="otp-input" maxlength="1" autocomplete="off"/>
                        <input type="text" id="otp6" name="otp6" value="<?php echo isset($_SESSION['temp_data']['otp6']) ? htmlspecialchars($_SESSION['temp_data']['otp6']) : ''; ?>" class ="otp-input" maxlength="1" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="input-field">
                     <i class="fas fa-user"></i>
                     <input type="text" name="username" placeholder="Username" aria-label="Enter a unique username" autocomplete="off" value="<?php echo isset($_SESSION['temp_data']['username']) ? htmlspecialchars($_SESSION['temp_data']['username']) : ''; ?>" />
                  </div>
                  <div class="input-field">
                     <i class="fas fa-lock"></i>
                     <input type="password" name="newpassword" id="newPassword" placeholder="New Password" aria-label="Enter new password" autocomplete="off" value="<?php echo isset($_SESSION['temp_data']['newpassword']) ? htmlspecialchars($_SESSION['temp_data']['newpassword']) : ''; ?>"/>
                     <i class="fa-solid fa-eye-slash toggle-password" data-target="newPassword"></i>
                  </div>
                  <div class="input-field">
                     <i class="fas fa-lock"></i>
                     <input type="password" name="cnfrmpassword" id="confirmPassword" placeholder="Confirm Password" aria-label="Confirm your new password"autocomplete="off" value="<?php echo isset($_SESSION['temp_data']['cnfrmpassword']) ? htmlspecialchars($_SESSION['temp_data']['cnfrmpassword']) : ''; ?>"/>
                     <i class="fa-solid fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                  </div>
                  <!-- Verify Account Form -->
                  <div class="link-gnrtpswrd">
                     <a href="#" id="generatePasswordVerify" class="genpswrdlink" role="button" aria-label="Generate a secure password">
                     <i class="fa-solid fa-gear"></i>
                     Generate a Secure Password
                     </a>
                  </div>
                  <p class="social-text">
                     Token expired? <a href="/auth/recover?action=request">Request a new token</a>
                  </p>
                  <button type="submit" class="btn solid">Setup</button>
               </form>
               <!-- Reset Password Form -->
               <form action="/auth/resetPassword" method="post" class="rst-pswrd-form" id="rstPswrdForm" autocomplete="off">
                  <h2 class="title-long">Reset Password</h2>
                  <!-- Reset Token Field (Hidden) -->
                  <input type="hidden" name="resetToken" id="resetToken" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>" aria-hidden="true"/>
                  <!-- New Password Field -->
                  <div class="input-field">
                     <i class="ri-lock-2-fill"></i>
                     <input type="password" name="newpassword" id="newPasswordReset" placeholder="New Password" aria-label="Enter new password" value="<?php echo isset($_SESSION['temp_data']['newpassword']) ? htmlspecialchars($_SESSION['temp_data']['newpassword']) : ''; ?>" autocomplete="off"/>
                     <i class="fa-solid fa-eye-slash toggle-password" data-target="newPasswordReset"></i>
                  </div>
                  <!-- Confirm Password Field -->
                  <div class="input-field">
                     <i class="ri-lock-2-fill"></i>
                     <input type="password" name="cnfrmpassword" id="confirmPasswordReset" placeholder="Confirm Password" aria-label="Confirm your new password" autocomplete="off" value="<?php echo isset($_SESSION['temp_data']['cnfrmpassword']) ? htmlspecialchars($_SESSION['temp_data']['cnfrmpassword']) : ''; ?>"/>
                     <i class="fa-solid fa-eye-slash toggle-password" data-target="confirmPasswordReset"></i>
                  </div>
                  <!-- Generate Random Secure Password -->
                  <div class="link-gnrtpswrd">
                     <a href="#" id="generatePasswordReset" class="genpswrdlink" role="button" aria-label="Generate a secure password">
                     <i class="fa-solid fa-gear"></i>
                     Generate a Secure Password
                     </a>
                  </div>
                  <button type="submit" class="btn solid">Confirm</button>
                  <p class="social-text">
                     Return to <a href="/auth/authenticate?action=login">Login Page</a>
                  </p>
               </form>
            </div>
         </div>
         <!-- Panels -->
         <div class="panels-container">
            <div class="panel left-panel">
               <div class="content">
                  <h3>Ready to Reset?</h3>
                  <p>
                     Enter your new password and confirm it to reset your password.
                  </p>
                  <button class="btn transparent" id="reset-btn">Reset Pass</button>
               </div>
               <img src="/assets/res/illustrations/reset.webp" class="image" alt="Reset Password Illustration" />
            </div>
            <div class="panel right-panel">
               <div class="content">
                  <h3>Verify and Set Up Your Account</h3>
                  <p>
                     To get started, enter the 6-digit OTP code that was sent to your registered email address.
                  </p>
                  <button class="btn transparent" id="verify-btn">Verify</button>
               </div>
               <img src="/assets/res/illustrations/verify.webp" class="image" alt="Account Verification Illustration" />
            </div>
         </div>
      </div>
      <script>
         document.addEventListener("DOMContentLoaded", () => {
            const otpInputs = document.querySelectorAll('.otp-input');

            otpInputs.forEach((input, index) => {
               input.addEventListener('input', (event) => {
                  const currentInput = event.target;
                  currentInput.value = currentInput.value.replace(/[^0-9]/g, '').slice(0, 1); // Allow only one digit

                  // Add or remove 'filled' class based on the input value
                  if (currentInput.value.length === 1) {
                     currentInput.classList.add('filled');
                     // Move to the next input if the current one is filled
                     if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                     }
                  } else {
                     currentInput.classList.remove('filled');
                  }
               });

               input.addEventListener('keydown', (event) => {
                  // Allow backspace to move to the previous input
                  if (event.key === 'Backspace' && input.value === '' && index > 0) {
                     otpInputs[index - 1].focus();
                  }
               });
            });
         });
      </script>
      <script src="/assets/js/formSwitcher.js"></script>
      <script src="/assets/js/showHidePswrd.js"></script>
      <script src="/assets/js/genRandPswrd.js"></script>
      <script src="/assets/js/notifier.js"></script>
   </body>
</html>
