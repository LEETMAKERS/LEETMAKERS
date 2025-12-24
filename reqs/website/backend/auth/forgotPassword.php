<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();


$redirectUrl = "/auth/recover?action=forgot";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Error: Invalid or missing CSRF token.";
        header("Location: $redirectUrl");
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    require_once __DIR__ . "/../includes/dbConn.php";

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
        $_SESSION['info'] = "Info: If the provided email is valid, password reset instructions have been sent to your inbox.";
        header("Location: $redirectUrl");
        exit();
    }

    $user = $result->fetch_assoc();

    if ($user['accountStatus'] === 0) {
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = "Error: Your account is not verified yet. Please complete the verification process first.";
        header("Location: $redirectUrl");
        exit();
    }

    // Generate a secure reset token and expiry time with default expiry of 1 hour
    $resetDetails = generateResetTokenAndExpiry();

    // Access the reset token and expiry time
    $resetToken = $resetDetails['resetToken'];
    $resetTokenExpiry = $resetDetails['resetTokenExpiry'];

    // Update the reset token and expiry in the identity table
    $updateResetToken = "UPDATE identity SET resetTokenHash = ?, resetTokenExpiry = ? WHERE email = ?";
    $stmt = $conn->prepare($updateResetToken);
    $stmt->bind_param("sss", $resetToken, $resetTokenExpiry, $email);

    if ($stmt->execute()) {
        // Send reset password email
        sendEmail("forgotPassword", $email, $user['firstname'], $user['lastname'], ['resetToken' => $resetToken]);
        session_regenerate_id(true);
        $_SESSION['info'] = "Info: If the provided email is valid, password reset instructions have been sent to your inbox.";
        $stmt->close();
        $conn->close();
        header("Location: $redirectUrl");
        exit();
    } else {
        $_SESSION['error'] = "Error: Unable to request reset password. Please try again later.";
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
