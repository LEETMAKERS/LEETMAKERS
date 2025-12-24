<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();

// Define the base directory (root of the project)
define('BASE_DIR', '/var/www/html');


$redirectUrl = "/auth/authenticate?action=register";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Error: Invalid or missing CSRF token.";
        header("Location: $redirectUrl");
        exit();
    }
}
// Check if required fields are submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fstname'], $_POST['lstname'], $_POST['email'], $_POST['gender'])) {

    require_once __DIR__ . "/../includes/dbConn.php";

    checkDatabaseConnection($conn, $redirectUrl);

    // Sanitize and validate input
    $fstname = validate($_POST['fstname']);
    $lstname = validate($_POST['lstname']);
    $email = validate($_POST['email']);
    $gender = validate($_POST['gender']);

    // Store temporary form data in session for repopulation
    $_SESSION['temp_data'] = [
        'fstname' => $fstname,
        'lstname' => $lstname,
        'email' => $email,
        'gender' => $gender
    ];

    // Check for empty fields
    checkEmptyFields([
        'First Name' => $fstname,
        'Last Name' => $lstname,
        'Email' => $email,
        'Gender' => $gender
    ], $redirectUrl);

    // Validate email format
    validateEmailFormat($email, $redirectUrl);

    // Check if the terms checkbox is accepted
    if (!isset($_POST['terms'])) {
        // Set warning message and redirect back to the registration page
        $_SESSION['warning'] = "Warning: You must review and agree to the Terms of Service and Privacy Policy.";
        header("Location: /auth/authenticate?action=register");
        exit();
    }

    // Check if the first name and last name already exist
    $checkNameQuery = "SELECT id FROM identity WHERE firstname = ? AND lastname = ?";
    $checkNameStmt = $conn->prepare($checkNameQuery);
    $checkNameStmt->bind_param("ss", $fstname, $lstname);
    $checkNameStmt->execute();
    $checkNameResult = $checkNameStmt->get_result();

    if ($checkNameResult->num_rows > 0) {
        $_SESSION['error'] = "Error: Account with entered name already exists";
        header("Location: $redirectUrl");
        exit();
    }

    // Check if the email is already taken
    $checkEmailQuery = "SELECT id FROM identity WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
        $_SESSION['error'] = "Error: Account with entered email already exists";
        header("Location: $redirectUrl");
        exit();
    }

    // Generate OTP and expiry time (default expiry of 15 minutes)
    $result = generateOtpAndExpiry();
    $otp = $result['otp'];
    $otpDigits = $result['otpDigits'];
    $otpExpiry = $result['otpExpiry'];

    // Setup the user profile picture according his gender
    $chosenAvatarPath = setupUserProfilePic($gender);

    // Generate unique picture directory ID (16 characters)
    $pictureDirId = bin2hex(random_bytes(8));

    // Insert new account into the database
    $insertAccountQuery = "INSERT INTO identity (firstname, lastname, email, gender, picture, pictureDirId, otpToken, otpTokenExpiry) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertAccountQuery);
    $stmt->bind_param("ssssssss", $fstname, $lstname, $email, $gender, $chosenAvatarPath, $pictureDirId, $otp, $otpExpiry);

    if ($stmt->execute() === TRUE) {
        // Send verification email
        session_regenerate_id(true);  // Ensure a new session ID is created
        $emailSent = sendEmail("verifyAccount", $email, $fstname, $lstname, ['otp' => $otp]);

        if ($emailSent) {
            $_SESSION['success'] = "Success: Account created successfully. An OTP has been sent to your email. Please use it to verify and set up your account.";
            // Clear temporary form data on success
            unset($_SESSION['temp_data']);
        } else {
            // Rollback: Delete the created account since email failed
            $deleteQuery = "DELETE FROM identity WHERE email = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();
            $deleteStmt->close();

            $_SESSION['error'] = "Error: Account creation failed. We couldn't send the verification email. Please try again later.";
        }
    } else {
        // Handle insert failure
        $_SESSION['error'] = "Error: Oops! We couldn’t create your account at the moment. Please try again in a few minutes.";
    }

    // Close database connection
    $stmt->close();
    $conn->close();

    // Redirect to the registration page with success or error message
    header("Location: $redirectUrl");
    exit();
}
?>

