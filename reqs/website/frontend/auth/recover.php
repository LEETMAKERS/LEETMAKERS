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
      <link
         href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
         rel="stylesheet"
      />
      <link rel="stylesheet" href="/assets/css/forms.css" />
      <link rel="stylesheet" href="/assets/css/notifier.css" />
      <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="64x64">
      <title>LEET MAKERS - Recover Creds</title>
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
            <div class="forgot-request">
               <!-- Forgot Password Form -->
               <form action="/auth/forgotPassword" method="post" class="frgt-pswrd-form" id="forgotPasswordForm" autocomplete="off" novalidate>
                  <h2 class="title-long">Request Password Reset</h2>
                  <!-- Email Field -->
                  <div class="input-field">
                     <i class="fas fa-envelope"></i>
                     <input type="email" name="email" id="email" placeholder="Email" />
                  </div>
                  <button type="submit" class="btn solid">Submit</button>
                  <p class="social-text">
                     Return to <a href="/auth/authenticate?action=login">Login Page</a>
                  </p>
               </form>
               <!-- Request Verification Token Form -->
               <form action="/auth/requestOtp" method="post" class="rqst-tkn-form" id="requestTokenForm" autocomplete="off" novalidate>
                  <h2 class="title-long">Request Account Verification</h2>
                  <!-- Email Field -->
                  <div class="input-field">
                     <i class="fas fa-envelope"></i>
                     <input type="email" name="email" id="email" placeholder="Email" />
                  </div>
                  <button type="submit" class="btn solid">Submit</button>
                  <p class="social-text">
                     Return to <a href="/auth/secure?action=verify">Verify Account</a>
                  </p>
               </form>
            </div>
         </div>

         <div class="panels-container">
            <!-- Left Panel -->
            <div class="panel left-panel">
               <div class="content">
                  <h3>Didn't Receive Your Token?</h3>
                  <p>
                     If you missed the verification step or didn't receive your token, you can
                     request a new one.
                  </p>
                  <button class="btn transparent" id="request-btn">Request Token</button>
               </div>
               <img src="/assets/res/illustrations/recover.webp" class="image" alt="Request Verification Illustration"/>
            </div>

            <!-- Right Panel -->
            <div class="panel right-panel">
               <div class="content">
                  <h3>Forgot Password?</h3>
                  <p>
                     Don't worry! Use the form to reset it and regain access to your account.
                  </p>
                  <button class="btn transparent" id="forgot-btn">Forgot Pass</button>
               </div>
               <img src="/assets/res/illustrations/forgot.webp" class="image" alt="Forgot Password Illustration"
               />
            </div>
         </div>
      </div>

      <script src="/assets/js/formSwitcher.js"></script>
      <script src="/assets/js/notifier.js"></script>
   </body>
</html>
