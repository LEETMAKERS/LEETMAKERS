<?php

session_start();
require_once __DIR__ . "/../includes/utils.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /errors/handler?error=403");
    exit();
}

// CSRF token validation
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = "Error: Invalid or missing CSRF token.";
    header("Location: /profile");
    exit();
}

ensureUserIsLoggedIn();
$redirectUrl = "/profile";

$userId = $_SESSION['id'];
require_once __DIR__ . "/../includes/dbConn.php";
checkDatabaseConnection($conn, $redirectUrl);

$userDetails = getUserDetails($userId, $conn);
if (isset($userDetails['error'])) {
    $_SESSION['error'] = $userDetails['error'];
    header("Location: $redirectUrl");
    exit();
}

$profileChanged = false;
$updateTypes = [];

// Ensure pictureDirId exists
if (empty($userDetails['pictureDirId'])) {
    // Generate unique picture directory ID (16 characters)
    $pictureDirId = bin2hex(random_bytes(8));

    // Update database with new pictureDirId
    $updateDirQuery = $conn->prepare("UPDATE identity SET pictureDirId = ? WHERE id = ?");
    $updateDirQuery->bind_param("si", $pictureDirId, $userId);
    if (!$updateDirQuery->execute()) {
        recordLogs("Failed to set pictureDirId: " . $updateDirQuery->error, 'ERROR');
        $_SESSION['error'] = "Error: Failed to set up user directory.";
        header("Location: $redirectUrl");
        exit();
    }
    $updateDirQuery->close();
    $userDetails['pictureDirId'] = $pictureDirId;
}

// Base upload directory using picture directory ID
$baseUploadDir = __DIR__ . "/../../assets/res/avatars/uploads/" . $userDetails['pictureDirId'] . '/';

recordLogs("Received POST data: " . print_r($_POST, true), 'INFO');
recordLogs("Received FILES data: " . print_r($_FILES, true), 'INFO');

// Initialize variables for picture update
$pictureValue = null;
$pictureType = null;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file = $_FILES['profile_picture'];

    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['error'] = "Error: Only JPG, PNG, GIF, and WEBP images are allowed.";
        header("Location: $redirectUrl");
        exit();
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Error: Image size must be less than 2MB.";
        header("Location: $redirectUrl");
        exit();
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(4)) . '.' . $extension;

    // Create user directory if it doesn't exist
    if (!is_dir($baseUploadDir)) {
        mkdir($baseUploadDir, 0755, true);
    }

    $destination = $baseUploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $profileChanged = true;
        $updateTypes[] = 'picture';
        $pictureType = 'uploaded';
        $pictureValue = $filename;
        recordLogs("Uploaded file saved: $destination", 'INFO');
    } else {
        $error = error_get_last();
        $_SESSION['error'] = "Error: Failed to upload image. " . $error['message'];
        recordLogs("File upload failed: " . $error['message'], 'ERROR');
        header("Location: $redirectUrl");
        exit();
    }
} elseif (isset($_POST['default_picture']) && !empty($_POST['default_picture'])) {
    $defaultPicture = validate($_POST['default_picture']);

    $allowedDefaults = [
        'f1.webp',
        'f10.webp',
        'f11.webp',
        'f12.webp',
        'f13.webp',
        'f14.webp',
        'f15.webp',
        'f16.webp',
        'f17.webp',
        'f18.webp',
        'f19.webp',
        'f2.webp',
        'f20.webp',
        'f21.webp',
        'f22.webp',
        'f23.webp',
        'f24.webp',
        'f25.webp',
        'f26.webp',
        'f27.webp',
        'f28.webp',
        'f29.webp',
        'f3.webp',
        'f30.webp',
        'f31.webp',
        'f32.webp',
        'f4.webp',
        'f5.webp',
        'f6.webp',
        'f7.webp',
        'f8.webp',
        'f9.webp',
        'm1.webp',
        'm10.webp',
        'm11.webp',
        'm12.webp',
        'm13.webp',
        'm14.webp',
        'm15.webp',
        'm16.webp',
        'm17.webp',
        'm18.webp',
        'm19.webp',
        'm2.webp',
        'm20.webp',
        'm21.webp',
        'm22.webp',
        'm23.webp',
        'm24.webp',
        'm25.webp',
        'm26.webp',
        'm27.webp',
        'm28.webp',
        'm29.webp',
        'm3.webp',
        'm30.webp',
        'm31.webp',
        'm32.webp',
        'm4.webp',
        'm5.webp',
        'm6.webp',
        'm7.webp',
        'm8.webp',
        'm9.webp'
    ];

    if (!in_array($defaultPicture, $allowedDefaults)) {
        $_SESSION['error'] = "Error: Invalid default picture selection.";
        header("Location: $redirectUrl");
        exit();
    }

    $pictureType = 'default';
    if ($defaultPicture !== $userDetails['picture']) {
        $profileChanged = true;
        $updateTypes[] = 'picture';
        $pictureValue = $defaultPicture;
        recordLogs("Default picture selected: $defaultPicture", 'INFO');
    }
}

// Handle name changes
$firstname = validate($_POST['fst-name'] ?? '');
$lastname = validate($_POST['lst-name'] ?? '');
$username = validate($_POST['usr-name'] ?? '');

// Check if username is changing
$usernameChanged = false;
if (!empty($username) && $username !== $userDetails['username']) {
    if (checkUsername($username, $conn)) {
        $_SESSION['error'] = "Error: Username already taken.";
        header("Location: $redirectUrl");
        exit();
    }
    $usernameChanged = true;
    $profileChanged = true;
    $updateTypes[] = 'name';
}

// Check if other names are changing
if ($firstname !== $userDetails['firstname'] || $lastname !== $userDetails['lastname']) {
    $profileChanged = true;
    $updateTypes[] = 'name';
    recordLogs("Name changed: $firstname $lastname", 'INFO');
}

$passwordChanged = false;
$newPassword = "";

if (!empty($_POST['new-password']) && !empty($_POST['cnfrm-password'])) {
    $currentPassword = validate($_POST['crnt-password'] ?? '');
    $newPassword = validate($_POST['new-password']);
    $confirmPassword = validate($_POST['cnfrm-password']);

    // Check if user has an existing password (not OAuth user setting first password)
    $hasExistingPassword = !empty($userDetails['password']) && $userDetails['password'] !== null;

    // Only verify current password if user has an existing password
    if ($hasExistingPassword) {
        if (!verifyCurrentPassword($conn, $currentPassword)) {
            $_SESSION['error'] = "Error: Entered Current Password is incorrect.";
            header("Location: $redirectUrl");
            exit();
        }
        if (checkPasswordMatch($currentPassword, $newPassword)) {
            $_SESSION['warning'] = "Warning: The New Password cannot be the same as the Current Password.";
            header("Location: $redirectUrl");
            exit();
        }
    }

    if (!checkPasswordMatch($newPassword, $confirmPassword)) {
        $_SESSION['error'] = "Error: New Password and Confirm Password do not match.";
        header("Location: $redirectUrl");
        exit();
    }

    $passwordSecurityError = validatePasswordSecurity($newPassword);
    if ($passwordSecurityError) {
        $_SESSION['error'] = "Error: $passwordSecurityError";
        header("Location: $redirectUrl");
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $profileChanged = true;
    $updateTypes[] = 'password';
    $passwordChanged = true;

    if ($hasExistingPassword) {
        recordLogs("Password changed for user ID: $userId", 'INFO');
    } else {
        recordLogs("First password set for OAuth user ID: $userId", 'INFO');
    }
}

if ($profileChanged) {
    $updateQuery = "UPDATE identity SET ";
    $updateValues = [];
    $params = [];
    $types = "";

    if (in_array('picture', $updateTypes)) {
        $updateValues[] = "picture = ?, pictureType = ?";
        $params[] = $pictureValue;
        $params[] = $pictureType;
        $types .= "ss";
    }

    if (in_array('name', $updateTypes)) {
        $updateValues[] = "firstname = ?, lastname = ?";
        $params[] = $firstname;
        $params[] = $lastname;
        $types .= "ss";

        if ($usernameChanged) {
            $updateValues[] = "username = ?";
            $params[] = $username;
            $types .= "s";
            $_SESSION['username'] = $username;
        }
    }

    if (in_array('password', $updateTypes)) {
        $updateValues[] = "password = ?";
        $params[] = $hashedPassword;
        $types .= "s";
    }

    if (!empty($updateValues)) {
        $query = $conn->prepare($updateQuery . implode(", ", $updateValues) . " WHERE id = ?");
        $params[] = $userId;
        $types .= "i";

        $query->bind_param($types, ...$params);

        if ($query->execute()) {
            $_SESSION['success'] = "Success: Profile updated successfully.";

            if (in_array('picture', $updateTypes)) {
                if ($pictureType === 'default') {
                    $_SESSION['avatar'] = "/assets/res/avatars/default/{$pictureValue}";
                } else {
                    $_SESSION['avatar'] = "/assets/res/avatars/uploads/" .
                        $userDetails['pictureDirId'] . '/' .
                        $pictureValue;
                }
                recordLogs("Avatar updated in session: {$_SESSION['avatar']}", 'INFO');
            }
        } else {
            $_SESSION['error'] = "Error: Database update failed. Error: " . $query->error;
            recordLogs("Database error: " . $query->error, 'ERROR');
        }
        $query->close();
    }
} else {
    $_SESSION['warning'] = "Warning: No changes were detected in your profile.";
    header("Location: $redirectUrl");
    exit();
}

$conn->close();
header("Location: $redirectUrl");
exit();
?>

