<?php

/**
 * Validate and sanitize input data.
 *
 * @param string $data The input data to validate.
 * @return string The sanitized data.
 */
function validate($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}


/**
 * Check if any required fields are empty. If any field is empty,
 * a session warning is set and the user is redirected.
 *
 * @param array  $fields      An associative array of field names to their values.
 * @param string $redirectUrl The URL to redirect to if a field is empty.
 * @return void Redirects and exits if any field is empty.
 */
function checkEmptyFields($fields, $redirectUrl)
{
    foreach ($fields as $field => $value) {
        if (empty($value)) {
            $_SESSION['warning'] = "Warning: " . ucfirst($field) . " is required";
            header("Location: $redirectUrl");
            exit();
        }
    }
}

/**
 * Check if the desired page matches the current page.
 *
 * @param string $desiredPage The page name to check.
 * @param string $currentPage The current page name.
 * @return string Returns 'active-link' if pages match, otherwise an empty string.
 */
function isActive($desiredPage, $currentPage)
{
    return $desiredPage === $currentPage ? 'active-link' : '';
}

/**
 * Validate the email format and redirect if it is invalid.
 *
 * @param string $email       The email address to validate.
 * @param string $redirectUrl The URL to redirect to if the email format is invalid.
 * @return void Redirects and exits if the email is not valid.
 */
function validateEmailFormat($email, $redirectUrl)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['warning'] = "Warning: Invalid email format.";
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Validate the security of a password.
 *
 * Checks for minimum length, uppercase, lowercase, numeric, and special character requirements.
 *
 * @param string $password The password to validate.
 * @return string|null Returns an error message if the password does not meet security requirements, or null if it is secure.
 */
function validatePasswordSecurity($password)
{
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must include at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must include at least one lowercase letter.";
    }
    if (!preg_match('/\d/', $password)) {
        return "Password must include at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must include at least one special character.";
    }
    return null;
}


/**
 * Record a log message to the application log file.
 *
 * @param string $message The log message.
 * @param string $level   Optional. The log level (default 'INFO').
 * @return void
 */
function recordLogs($message, $level = 'INFO')
{
    // Use server logs directory (mounted from host)
    $logFolder = "/var/www/logs/application";
    $logFilePath = $logFolder . "/application_" . date("Y-m-d") . ".log";

    if (!is_dir($logFolder)) {
        mkdir($logFolder, 0755, true);
    }

    if (file_exists($logFilePath) && filesize($logFilePath) > 5 * 1024 * 1024) {
        rename($logFilePath, $logFolder . "/application_" . date("Y-m-d_H-i-s") . ".log");
    }

    $timestamp = date("Y-m-d H:i:s");
    $formattedMessage = "[$timestamp] [$level]: $message" . PHP_EOL;
    file_put_contents($logFilePath, $formattedMessage, FILE_APPEND);
}

/**
 * Check the database connection and redirect if there is a connection error.
 *
 * @param mysqli $conn      The database connection.
 * @param string $redirectUrl The URL to redirect to if the connection fails.
 * @return void Redirects and exits if the connection has an error.
 */
function checkDatabaseConnection($conn, $redirectUrl)
{
    if ($conn->connect_error) {
        $_SESSION['error'] = "Error: Database connection failed";
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Check if two passwords match.
 *
 * @param string $password        The first password.
 * @param string $confirmPassword The second password to compare.
 * @return bool Returns true if the passwords match, false otherwise.
 */
function checkPasswordMatch($password, $confirmPassword)
{
    return $password === $confirmPassword;
}

/**
 * Ensure the user is an admin. Redirects to 403 error if not admin.
 *
 * @param int    $userId The user ID from the session.
 * @param mysqli $conn   The database connection.
 * @return void Redirects to 403 error page if user is not admin.
 */
function ensureUserIsAdmin($userId, $conn)
{
    $query = "SELECT role FROM identity WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        recordLogs("Database Error: " . $conn->error, 'ERROR');
        header("Location: /errors/handler?code=500");
        exit();
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['role'] !== 'admin') {
            header("Location: /errors/handler?code=403");
            exit();
        }
    } else {
        header("Location: /errors/handler?code=403");
        exit();
    }

    $stmt->close();
}

/**
 * Retrieve the user details from the database.
 *
 * @param int    $sessionId The user ID from the session.
 * @param mysqli $conn      The database connection.
 * @return array An associative array containing user details or an error message.
 */
function getUserDetails($sessionId, $conn)
{
    $query = "SELECT firstname, lastname, username, email, password, 
                     pictureType, pictureDirId, picture, gender, role
              FROM identity 
              WHERE id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        recordLogs("Database Error: " . $conn->error, 'ERROR');
        return ['error' => "An error occurred. Please try again later."];
    }

    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user['avatar'] = getAvatarPath($user);
        return $user;
    } else {
        return ['error' => "Error: No available user. Please create an account first."];
    }
}

/**
 * Determine the avatar path for the user based on their picture type.
 *
 * @param array $user An associative array containing user details.
 * @return string The URL path to the user's avatar image.
 */
function getAvatarPath($user)
{
    $basePath = "/assets/res/avatars/";
    $defaultAvatar = $basePath . "nouser.webp";

    if ($user['pictureType'] === "uploaded") {
        $avatarPath = __DIR__ . "/../../assets/res/avatars/uploads/" .
            $user['pictureDirId'] . '/' .
            $user['picture'];

        return file_exists($avatarPath)
            ? $basePath . "uploads/" .
            $user['pictureDirId'] . '/' .
            htmlspecialchars($user['picture'])
            : $defaultAvatar;
    } elseif ($user['pictureType'] === "default") {
        $avatarPath = __DIR__ . "/../../assets/res/avatars/default/" . $user['picture'];
        return file_exists($avatarPath)
            ? $basePath . "default/" . htmlspecialchars($user['picture'])
            : $defaultAvatar;
    } elseif ($user['pictureType'] === "url") {
        return filter_var($user['picture'], FILTER_VALIDATE_URL)
            ? htmlspecialchars($user['picture'])
            : $defaultAvatar;
    }
    return $defaultAvatar;
}

/**
 * Set up a default user profile picture based on the user's gender.
 *
 * @param string $gender The gender of the user ('male' or 'female').
 * @return string The filename of the chosen avatar.
 */
function setupUserProfilePic($gender)
{
    if (!defined('BASE_DIR')) {
        define('BASE_DIR', '/var/www/html');
    }

    $avatarList = [];
    if ($gender == 'male') {
        $avatarList = glob(BASE_DIR . "/assets/res/avatars/default/m*.webp");
    } elseif ($gender == 'female') {
        $avatarList = glob(BASE_DIR . "/assets/res/avatars/default/f*.webp");
    }
    if (empty($avatarList)) {
        $avatarList = [BASE_DIR . "/assets/res/avatars/nouser.webp"];
    }
    $chosenAvatar = $avatarList[array_rand($avatarList)];
    return basename($chosenAvatar);
}

/**
 * Ensure that the user is logged in. If not, redirect them to the login page.
 *
 * @param string|null $pageName Optional. The name of the page the user is trying to access.
 * @return void Redirects to the login page if the user is not logged in.
 */
function ensureUserIsLoggedIn($pageName = null)
{
    // Start the session if it hasn't been started yet.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the user is not logged in.
    if (!isset($_SESSION['id']) && !isset($_SESSION['google_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        if ($pageName) {
            $_SESSION['info'] = "Info: Please log in to access the $pageName page.";
        } else {
            $_SESSION['info'] = "Info: Please log in first.";
        }
        header("Location: /auth/authenticate?action=login");
        exit();
    }
}

/**
 * Check if the request method is POST and if the user is not already logged in.
 *
 * If the request is not POST, or the user is already logged in, this function redirects accordingly.
 *
 * @return void Redirects and exits if conditions are not met.
 */
function checkRequestMethodAndLoginState()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: /errors/handler");
        exit();
    }

    if (isset($_SESSION['id']) || isset($_SESSION['google_id'])) {
        header("Location: ../dashboard");
        exit();
    }
}

/**
 * Check if the reset token is provided; if not, redirect with an error.
 *
 * @param string $token       The reset token.
 * @param string $redirectUrl The URL to redirect to if the token is empty.
 * @return void Redirects and exits if the token is missing.
 */
function checkResetToken($token, $redirectUrl)
{
    if (empty($token)) {
        $_SESSION['error'] = 'Error: Invalid or missing reset token.';
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Verify if the provided current password matches the stored password for the authenticated user.
 *
 * @param mysqli $conn           The database connection.
 * @param string $inputPassword  The password entered by the user.
 * @return bool Returns true if the provided password is correct, false otherwise.
 */
function verifyCurrentPassword($conn, $inputPassword)
{
    // Get the session ID
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['id'])) {
        return false;
    }

    // Fetch user details with correct parameter order
    $userDetails = getUserDetails($_SESSION['id'], $conn);

    // Return false if an error occurred while fetching user details
    if (isset($userDetails['error']) || empty($userDetails['password'])) {
        return false;
    }

    return password_verify($inputPassword, $userDetails['password']);
}

/**
 * Generate a secure reset token and its expiry time.
 *
 * @param string $expiryInterval Optional. A strtotime-compatible string representing the expiry interval (default '+1 hour').
 * @return array An associative array with keys:
 *         - resetToken: The generated reset token (hexadecimal string).
 *         - resetTokenExpiry: The expiry time as a datetime string.
 */
function generateResetTokenAndExpiry($expiryInterval = '+1 hour')
{
    $rsToken = bin2hex(random_bytes(32));
    $rsTokenExpiry = date("Y-m-d H:i:s", strtotime($expiryInterval));
    return [
        'resetToken' => $rsToken,
        'resetTokenExpiry' => $rsTokenExpiry
    ];
}

/**
 * Generate a 6-digit OTP (One-Time Password) and its expiry time.
 *
 * @param string $expiryInterval Optional. A strtotime-compatible string for the OTP expiry (default '+15 minutes').
 * @return array An associative array with keys:
 *         - otp: The generated OTP as a string.
 *         - otpDigits: An array of individual digits of the OTP.
 *         - otpExpiry: The expiry time as a datetime string.
 */
function generateOtpAndExpiry($expiryInterval = '+15 minutes')
{
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpDigits = str_split($otp);
    $otpExpiry = date("Y-m-d H:i:s", strtotime($expiryInterval));
    return [
        'otp' => $otp,
        'otpDigits' => $otpDigits,
        'otpExpiry' => $otpExpiry
    ];
}

/**
 * Check if a given username is already taken in the database.
 *
 * @param string $username The username to check.
 * @param mysqli $conn     The database connection.
 * @return string|null Returns an error message if the username is taken or a database error occurs,
 *                     otherwise returns null.
 */
function checkUsername($username, $conn)
{
    $query = "SELECT id FROM identity WHERE username = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return "Error: Failed to prepare the username check statement.";
    }

    $stmt->bind_param("s", $username);

    if (!$stmt->execute()) {
        $stmt->close();
        return "Error: Failed to execute the username check statement.";
    }

    $result = $stmt->get_result();
    $isTaken = $result && $result->num_rows > 0;

    $stmt->close();

    return $isTaken
        ? "Error: The username '{{username}}' is already in use. Please choose a different one."
        : null;
}


/**
 * Generate a unique username based on the user's first and last name.
 *
 * @param string $firstName The user's first name.
 * @param string $lastName  The user's last name.
 * @param mysqli $conn      The database connection.
 * @return string The generated unique username.
 */
function generateUsername($firstName, $lastName, $conn, $maxLength = 8)
{
    $lastName = str_replace(' ', '-', $lastName);
    $username = '';

    while (true) {
        $firstPartLength = rand(1, min(3, strlen($firstName)));
        $lastPartLength = $maxLength - $firstPartLength;
        $lastPartLength = min($lastPartLength, strlen($lastName));
        $firstPart = strtolower(substr($firstName, 0, $firstPartLength));
        $lastPart = strtolower(substr($lastName, 0, $lastPartLength));
        $username = $firstPart . $lastPart;

        if (strlen($username) > $maxLength) {
            $username = substr($username, 0, $maxLength);
        }

        if (strlen($username) < $maxLength) {
            $username .= rand(10, 99);
        }

        if (strlen($username) > $maxLength) {
            $username = substr($username, 0, $maxLength);
        }

        $existingUsernameCheck = checkUsername($username, $conn);
        if ($existingUsernameCheck === null) {
            return $username;
        }
    }
}

/**
 * Check if the provided OTP is valid and not expired.
 *
 * @param string $otp  The OTP token to check.
 * @param mysqli $conn The database connection.
 * @return mixed Returns an associative array with user details if valid, otherwise a string error message.
 */
function checkOTP($otp, $conn)
{
    $otpQuery = "SELECT id, otpTokenExpiry FROM identity WHERE otpToken = ?";
    $otpStmt = $conn->prepare($otpQuery);
    if (!$otpStmt) {
        return "Error: Unable to prepare database statement.";
    }
    $otpStmt->bind_param("s", $otp);
    $otpStmt->execute();
    $otpResult = $otpStmt->get_result();

    if ($otpResult->num_rows === 0) {
        $otpStmt->close();
        return "Invalid or expired OTP Token.";
    }

    $user = $otpResult->fetch_assoc();
    if (strtotime($user['otpTokenExpiry']) < time()) {
        $otpStmt->close();
        return "OTP Token has expired.";
    }

    $otpStmt->close();
    return $user;
}

/**
 * Send an email based on the provided type and parameters.
 *
 * @param string     $type    The type of email to send.
 * @param string     $email   The recipient's email address.
 * @param string     $fstname The recipient's first name.
 * @param string     $lstname The recipient's last name.
 * @param array|null $data    Optional. Additional data required for the email template.
 * @return bool
 */
function sendEmail($type, $email, $fstname, $lstname, $data = null)
{
    $mail = require __DIR__ . "/mailer.php";
    $env = loadEnv('/var/www/env/.env');

    $url = $env['WEBSITE_URL'];
    $wbstname = $env['WEBSITE_NAME'];
    $supportMail = $env['SUPPORT_MAIL'];
    $supportName = "$wbstname Support Team";
    $currentYear = date("Y");

    // Base replacements
    $baseReplacements = [
        '{{fstname}}' => $fstname,
        '{{lstname}}' => $lstname,
        '{{wbstname}}' => $wbstname,
        '{{sprtmail}}' => $supportMail,
        '{{date}}' => $currentYear,
    ];

    $mailConfigs = [
        'verifyAccount' => [
            'subject' => "Verify and setup your $wbstname Account",
            'template' => 'verify.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/auth/authenticate?action=register",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{verifyUrl}}' => "$url/auth/secure?action=verify",
                    '{{requestUrl}}' => "$url/auth/recover?action=request",
                    '{{otpToken}}' => $data['otp'] ?? 'LEETMK',
                ]);
            }
        ],
        'resendVerification' => [
            'subject' => "Resend Account Verification for your $wbstname Account",
            'template' => 're-verify.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/auth/recover?action=request",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{verifyUrl}}' => "$url/auth/secure?action=verify",
                    '{{otpToken}}' => $data['otpToken'] ?? 'LEETMK',
                ]);
            }
        ],
        'accountVerified' => [
            'subject' => "Welcome to $wbstname! Your Account is Ready to Use",
            'template' => 'verified.html',
            'from_email' => $env['UPDATES_MAIL'],
            'from_name' => "$wbstname Updates Team",
            'redirect' => "/auth/authenticate?action=register",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{loginUrl}}' => "$url/auth/authenticate?action=login",
                ]);
            }
        ],
        'forgotPassword' => [
            'subject' => "Forgot your $wbstname Account Password?",
            'template' => 'forgot.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/auth/recover?action=forgot",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{resetUrl}}' => "$url/auth/secure?action=reset&token=" . ($data['resetToken'] ?? ''),
                ]);
            }
        ],
        'passwordReseted' => [
            'subject' => "Your $wbstname Account Password has been Reset successfully",
            'template' => 'reseted.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/auth/authenticate?action=login",
            'replacements' => fn($data) => $baseReplacements,
        ],
        'deleteAccount' => [
            'subject' => "Confirm Your $wbstname Account Deletion Request",
            'template' => 'delete.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/settings",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{confirmUrl}}' => "$url/settings/confirm-delete?token=" . ($data['deleteToken'] ?? ''),
                ]);
            }
        ],
        'accountDeleted' => [
            'subject' => "Your $wbstname Account has been Deleted!",
            'template' => 'deleted.html',
            'from_email' => $env['SECURITY_MAIL'],
            'from_name' => "$wbstname Security Team",
            'redirect' => "/auth/authenticate?action=login",
            'replacements' => fn($data) => $baseReplacements,
        ],
        'oauth-registered' => [
            'subject' => "Welcome to $wbstname! Your Account has been Created Successfully",
            'template' => 'oauth-registered.html',
            'from_email' => $env['UPDATES_MAIL'],
            'from_name' => "$wbstname Updates Team",
            'redirect' => "/auth/authenticate?action=login",
            'replacements' => function ($data) use ($baseReplacements, $url) {
                return array_merge($baseReplacements, [
                    '{{loginUrl}}' => "$url/auth/authenticate?action=login",
                ]);
            }
        ],
    ];

    if (!isset($mailConfigs[$type])) {
        recordLogs("Invalid email type '{{type}}' encountered.", 'ERROR');
        return false;
    }

    $config = $mailConfigs[$type];
    $templatePath = __DIR__ . "/../../frontend/templates/mails/" . $config['template'];

    if (!file_exists($templatePath)) {
        recordLogs("Email template '{$config['template']}' not found.", 'ERROR');
        return false;
    }

    try {
        $template = file_get_contents($templatePath);
        $replacements = $config['replacements']($data ?? []);
        $body = str_replace(array_keys($replacements), array_values($replacements), $template);
    } catch (Exception $e) {
        recordLogs("Error processing email template: " . $e->getMessage(), 'ERROR');
        return false;
    }

    // Send email
    $mail->clearAllRecipients();
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addReplyTo($supportMail, $supportName);
    $mail->addAddress($email);
    $mail->Subject = $config['subject'];
    $mail->Body = $body;
    $mail->IsHTML(true);
    $mail->addEmbeddedImage(__DIR__ . "/../../assets/res/logo/leetmakers.jpg", 'logo', 'leetmakers.jpg');

    try {
        if (!$mail->send()) {
            recordLogs("Mailer Error: " . $mail->ErrorInfo, 'ERROR');
            return false;
        }
        recordLogs("Mailer Success: Sent $type email to $email.");
        return true;
    } catch (Exception $e) {
        recordLogs("Exception sending email: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Validate the OTP (One-Time Password) format.
 *
 * @param string $otp The OTP to validate.
 * @return string|null Returns an error message if the OTP is invalid, or null if it is valid.
 */
function validateOTPFormat($otp)
{
    if (!preg_match('/^\d{6}$/', $otp)) {
        return "OTP Token must be 6 digits long.";
    }
    return null;
}
