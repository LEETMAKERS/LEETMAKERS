<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();


$redirectUrl = "/auth/secure?action=verify";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Error: Invalid or missing CSRF token.";
        header("Location: $redirectUrl");
        exit();
    }
}
// Main logic for processing the form data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4'], $_POST['otp5'], $_POST['otp6'], $_POST['username'], $_POST['newpassword'], $_POST['cnfrmpassword'])) {

    // Connect to the database
    require_once __DIR__ . "/../includes/dbConn.php";

    checkDatabaseConnection($conn, $redirectUrl);

    // Collect and sanitize inputs
    $otp = validate($_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6']);
    $username = validate($_POST['username']);
    $newpassword = validate($_POST['newpassword']);
    $cnfrmpassword = validate($_POST['cnfrmpassword']);

    // Store form data in session for persistence
    $_SESSION['temp_data'] = [
        'otp1' => $_POST['otp1'],
        'otp2' => $_POST['otp2'],
        'otp3' => $_POST['otp3'],
        'otp4' => $_POST['otp4'],
        'otp5' => $_POST['otp5'],
        'otp6' => $_POST['otp6'],
        'username' => $username,
        'newpassword' => $newpassword,
        'cnfrmpassword' => $cnfrmpassword
    ];

    checkEmptyFields([
        'OTP' => $otp,
        'Username' => $username,
        'New Password' => $newpassword,
        'Confirm Password' => $cnfrmpassword
    ], $redirectUrl);

    // Validate OTP format
    $otpFormatError = validateOTPFormat($otp);
    if ($otpFormatError) {
        $_SESSION['error'] = "Error: $otpFormatError";
        header("Location: $redirectUrl");
        exit();
    }

    // Validate password security
    $passwordSecurityError = validatePasswordSecurity($newpassword);
    if ($passwordSecurityError) {
        $_SESSION['error'] = "Error: $passwordSecurityError";
        header("Location: $redirectUrl");
        exit();
    }

    // Check if passwords match
    if (!checkPasswordMatch($newpassword, $cnfrmpassword)) {
        $_SESSION['error'] = "Error: Passwords do not match";
        header("Location: $redirectUrl");
        exit();
    }

    // Check OTP validity in the database
    $user = checkOTP($otp, $conn);
    if (is_string($user)) {
        $_SESSION['error'] = "Error: $user";
        header("Location: $redirectUrl");
        exit();
    }

    // Retrieve user data including OTP expiry time
    $query = "SELECT email, firstname, lastname, otpTokenExpiry FROM identity WHERE otpToken = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $otp); // Bind OTP token
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Error: Invalid OTP.";
        header("Location: $redirectUrl");
        exit();
    }

    $stmt->bind_result($email, $first_name, $last_name, $otpTokenExpiry);
    $stmt->fetch();
    $stmt->close();

    // Check if OTP has expired
    if (strtotime($otpTokenExpiry) < time()) {
        $_SESSION['error'] = "Error: Your OTP token has expired. Please request a new verification request.";
        header("Location: $redirectUrl");
        exit();
    }

    // Check if username is already taken
    $usernameError = checkUsername($username, $conn);
    if ($usernameError) {
        $_SESSION['error'] = "Error: $usernameError";
        header("Location: $redirectUrl");
        exit();
    }

    // Hash the user password
    $hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);
    $accountStatus = 'verified';

    // Store all necessary data in the database
    $updateQuery = "UPDATE identity SET username = ?, password = ?, accountStatus = ?, otpToken = NULL, otpTokenExpiry = NULL WHERE otpToken = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssss", $username, $hashedPassword, $accountStatus, $otp);

    if ($updateStmt->execute()) {

        // Send the verification email with the retrieved user data
        sendEmail('accountVerified', $email, $first_name, $last_name, [
            'accountType' => 'normal'
        ]);

        // Clear temp data on success
        unset($_SESSION['temp_data']);

        session_regenerate_id(delete_old_session: true);
        $_SESSION['success'] = "Success: Account has been activated. Please login.";
        header("Location: /auth/authenticate?action=login");
    } else {
        $_SESSION['error'] = "Error: Unable to complete account setup";
        header("Location: $redirectUrl");
    }

    $updateStmt->close();
    $conn->close();
} else {
    header("Location: $redirectUrl");
    exit();
}
?>

