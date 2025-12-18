<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    require_once __DIR__ . "/../includes/dbConn.php";

    // Redirect URL
    $redirectUrl = "/auth/recover?action=request";

    checkDatabaseConnection($conn, $redirectUrl);

    $email = validate($_POST['email']);

    checkEmptyFields([
        'email' => $email,
    ], $redirectUrl);

    validateEmailFormat($email, $redirectUrl);

    $query = "SELECT id, firstname, lastname, accountStatus FROM identity WHERE email = ?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        $_SESSION['info'] = "Info: If your email is associated and valid, account verification instructions have been sent.";
        header("Location: $redirectUrl");
        exit();
    }

    $user = $result->fetch_assoc();

    // Check if the account is already verified
    if ($user['accountStatus'] === 'verified') {
        $stmt->close();
        $conn->close();
        $_SESSION['info'] = "Info: Your account is already verified. No need for a new verification.";
        header("Location: $redirectUrl");
        exit();
    }

    // Generate a new OTP token and expiry time
    $otpDetails = generateOtpAndExpiry();

    // Access the OTP token and expiry time
    $otp = $otpDetails['otp'];
    $otpExpiry = $otpDetails['otpExpiry'];

    // Update the OTP token and expiry in the identity table
    $updateOtpToken = "UPDATE identity SET otpToken = ?, otpTokenExpiry = ? WHERE email = ?";
    $stmt = $conn->prepare($updateOtpToken);
    $stmt->bind_param("sss", $otp, $otpExpiry, $email);

    if ($stmt->execute()) {
        // Send the verification email with the correct firstname and lastname
        sendEmail("resendVerification", $email, fstname: $user['firstname'], lstname: $user['lastname'], data: ['otpToken' => $otp]);
        session_regenerate_id(true);
        $_SESSION['info'] = "Info: If your email is associated and valid, account verification instructions have been sent.";
        $stmt->close();
        $conn->close();
        header("Location: $redirectUrl");
        exit();
    } else {
        $_SESSION['error'] = "Error: Unable to request account verification. Please try again later.";
        $stmt->close();
        $conn->close();
        header("Location: $redirectUrl");
        exit();
    }
} else {
    header("Location: $redirectUrl");
    exit();
}
?>
