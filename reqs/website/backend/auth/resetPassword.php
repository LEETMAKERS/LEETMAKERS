<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();

if (isset($_POST['resetToken'], $_POST['newpassword'], $_POST['cnfrmpassword'])) {

    // Connect to the database
    require_once __DIR__ . "/../includes/dbConn.php";

    $redirectUrl = "/auth/secure?action=reset";

    checkDatabaseConnection($conn, $redirectUrl);

    $resetToken = validate($_POST['resetToken']);
    $newPassword = validate($_POST['newpassword']);
    $confirmPassword = validate($_POST['cnfrmpassword']);

    // Store form data in session for persistence
    $_SESSION['temp_data'] = [
        'newpassword' => $newPassword,
        'cnfrmpassword' => $confirmPassword
    ];

    // Check for an empty reset token and set a clearer error message
    checkResetToken($resetToken, $redirectUrl);

    // Check for other empty fields
    checkEmptyFields([
        'New Password' => $newPassword,
        'Confirm Password' => $confirmPassword
    ], $redirectUrl);

    // Check if passwords match
    if (!checkPasswordMatch($newPassword, $confirmPassword)) {
        $_SESSION['error'] = "Error: Passwords do not match.";
        header("Location: $redirectUrl");
        exit();
    }

    // Validate password security
    $passwordError = validatePasswordSecurity($newPassword);
    if ($passwordError) {
        $_SESSION['error'] = "Error: " . $passwordError;
        header("Location: $redirectUrl");
        exit();
    }

    // Query the database to find the user with the reset token
    $query = "SELECT id, firstname, lastname, email, resetTokenHash, resetTokenExpiry FROM identity WHERE resetTokenHash = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $resetToken);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the token is valid
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Error: The reset token is invalid. Please request a new reset link.";
        header("Location: $redirectUrl");
        $stmt->close();
        $conn->close();
        exit();
    }

    // Fetch the user details
    $user = $result->fetch_assoc();

    // Check if the reset token has expired
    if (new DateTime() > new DateTime($user['resetTokenExpiry'])) {
        $_SESSION['error'] = "Error: Your password reset token has expired. Please request a new one.";
        header("Location: $redirectUrl");
        $stmt->close();
        $conn->close();
        exit();
    }

    // Hash the new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    // Update the password and clear the reset token
    $updateQuery = "UPDATE identity SET password = ?, resetTokenHash = NULL, resetTokenExpiry = NULL WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newPasswordHash, $user['id']);

    if ($stmt->execute()) {

        // Send the password reset confirmation email
        sendEmail('passwordReseted', $user['email'], $user['firstname'], $user['lastname']);

        // Clear temp data on success
        unset($_SESSION['temp_data']);

        $_SESSION['success'] = "Success: Password has been successfully reset.";
        session_regenerate_id(delete_old_session: true);
        header("Location: /auth/authenticate?action=login");
    } else {
        $_SESSION['error'] = "Error: Unable to reset your password. Please try again later.";
        header("Location: $redirectUrl");
    }

    // Close database connection after execution
    $stmt->close();
    $conn->close();
    exit();
}
?>

