
<?php
session_start();
// Check if the user is already logged in
if (isset($_SESSION['id'])) {
    header("Location: /dashboard");
    exit();
}
// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/forms.css" />
    <link rel="stylesheet" href="/assets/css/notifier.css" />
    <link rel="shortcut icon" href="/assets/res/logo/leetmakers.ico" type="image/x-icon" sizes="128x128">
    <title>LEET MAKERS - Authenticate</title>
</head>

<body>
    <ul class="notifications"></ul>
    <div id="session-messages" data-error="<?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>"
        data-success="<?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>"
        data-warning="<?php echo isset($_SESSION['warning']) ? $_SESSION['warning'] : ''; ?>"
        data-info="<?php echo isset($_SESSION['info']) ? $_SESSION['info'] : ''; ?>">
    </div>
    <?php unset($_SESSION['error'], $_SESSION['success'], $_SESSION['warning'], $_SESSION['info']); // Clear session messages ?>
    <div class="container" id="container">
        <div class="forms-container">
            <div class="signin-signup">
                <form action="/auth/login" method="post" class="sign-in-form" id="signInForm" autocomplete="off">
                    <h2 class="title">Sign in</h2>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Username" autocomplete="off"
                            value="<?php echo isset($_SESSION['temp_data']['username']) ? htmlspecialchars($_SESSION['temp_data']['username']) : ''; ?>" />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Password"
                            autocomplete="off" />
                        <i class="fa-solid fa-eye-slash toggle-password" data-target="password"></i>
                    </div>
                    <div class="password-options">
                        <!-- Remember Me Checkbox -->
                        <div class="remember-wrapper">
                            <input type="checkbox" name="remember" id="remember" class="inp-cbx" />
                            <label for="remember" class="cbx">
                                <span>
                                    <svg viewBox="0 0 12 10" height="10px" width="12px">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                                <span>Remember me</span>
                            </label>
                        </div>
                        <div class="forgot-password">
                            <a href="/auth/recover?action=forgot" class="forgot-password-link">Forgot your Password?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn solid">Login</button>
                    <div class="divider">
                        <span>Or Sign in using</span>
                    </div>
                    <div class="social-media">
                        <a href="/auth/oauth?action=login&method=google" class="social-icon">
                            <img class="social-img" src="/assets/res/icons/google.webp" alt="google">
                        </a>
                        <a href="/auth/oauth?action=login&method=42intra" class="social-icon">
                            <img class="social-img" src="/assets/res/icons/42.webp" alt="42">
                        </a>
                    </div>
                </form>
                <form action="/auth/register" method="post" class="sign-up-form" id="signUpForm" autocomplete="off"
                    novalidate>
                    <h2 class="title">Sign up</h2>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fstname" placeholder="First Name" autocomplete="off"
                            value="<?php echo isset($_SESSION['temp_data']['fstname']) ? htmlspecialchars($_SESSION['temp_data']['fstname']) : ''; ?>" />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="lstname" placeholder="Last Name" autocomplete="off"
                            value="<?php echo isset($_SESSION['temp_data']['lstname']) ? htmlspecialchars($_SESSION['temp_data']['lstname']) : ''; ?>" />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" autocomplete="off"
                            value="<?php echo isset($_SESSION['temp_data']['email']) ? htmlspecialchars($_SESSION['temp_data']['email']) : ''; ?>" />
                    </div>
                    <div class="gender-select">
                        <input type="hidden" name="gender" id="gender" />
                        <div class="gender-option male" data-value="male" id="gender-male">
                            <i class="fas fa-mars"></i> Male
                        </div>
                        <div class="gender-option female" data-value="female" id="gender-female">
                            <i class="fas fa-venus"></i> Female
                        </div>
                    </div>
                    <!-- Terms Checkbox -->
                    <div class="terms-wrapper">
                        <input type="checkbox" name="terms" id="terms" class="inp-cbx" value="accepted" />
                        <label for="terms" class="cbx">
                            <span>
                                <svg viewBox="0 0 12 10" height="10px" width="12px">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                            <span> I agree to the <a href="/policies/terms" target="_blank">Terms of Use</a>
                                and
                                <a href="/policies/privacy" target="_blank">Privacy Policy</a>.
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="btn">Sign up</button>
                    <div class="divider">
                        <span>Or Sign up using</span>
                    </div>
                    <div class="social-media">
                        <a href="/auth/oauth?action=register&method=google" class="social-icon">
                            <img class="social-img" src="/assets/res/icons/google.webp" alt="google">
                        </a>
                        <a href="/auth/oauth?action=register&method=42intra" class="social-icon">
                            <img class="social-img" src="/assets/res/icons/42.webp" alt="42">
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>New here?</h3>
                    <p>
                        Join our community and unlock the full potential of our platform. Sign up now to enjoy exclusive
                        features and stay updated!
                    </p>
                    <button class="btn transparent" id="sign-up-btn">
                        Sign up
                    </button>
                </div>
                <img src="/assets/res/illustrations/register.webp" class="image" alt="Register Illustration" />
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <h3>One of us?</h3>
                    <p>
                        Welcome back! Please sign in to access your account.
                    </p>
                    <button class="btn transparent" id="sign-in-btn">
                        Sign in
                    </button>
                </div>
                <img src="/assets/res/illustrations/login.webp" class="image" alt="Login Illustration" />
            </div>
        </div>
    </div>
    <?php
    // Pre-select gender if form data exists
    if (isset($_SESSION['temp_data']['gender'])) {
        $selectedGender = $_SESSION['temp_data']['gender'];
        echo "<script>document.addEventListener('DOMContentLoaded', function() { const genderOption = document.querySelector('.gender-option[data-value=\"" . $selectedGender . "\"]'); if (genderOption) { genderOption.classList.add('active'); document.getElementById('gender').value = '" . $selectedGender . "'; } });</script>";
    }
    // Clear form data after using it
    unset($_SESSION['temp_data']);
    unset($_SESSION['login_form_data']); ?>
    <script src="/assets/js/showHidePswrd.js"></script>
    <script src="/assets/js/formSwitcher.js"></script>
    <script src="/assets/js/genderSelector.js"></script>
    <script src="/assets/js/notifier.js"></script>
</body>

</html>
