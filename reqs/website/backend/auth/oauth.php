<?php
session_start();

require __DIR__ . '/../../../composer/vendor/autoload.php';
require_once __DIR__ . '/../includes/credLoader.php';
require_once __DIR__ . '/../includes/dbConn.php';
require_once __DIR__ . '/../includes/utils.php';

$redirectUrl = "/auth/authenticate?action=login";
checkDatabaseConnection($conn, $redirectUrl);

// Store 'action' and 'method' in session before redirecting to OAuth provider
if (isset($_GET['action'])) {
    $_SESSION['oauth_action'] = $_GET['action'];
}
if (isset($_GET['method'])) {
    $_SESSION['oauth_method'] = $_GET['method'];
}

// Check if 42 Intra OAuth is requested (under development)
$method = $_SESSION['oauth_method'] ?? 'google';
if ($method === '42intra') {
    $action = $_SESSION['oauth_action'] ?? 'login'; // Store action before unsetting
    $_SESSION['info'] = "Info: 42 Intra authentication is currently under development. Please use Google Sign-In or standard authentication.";
    unset($_SESSION['oauth_action']);
    unset($_SESSION['oauth_method']);
    header("Location: /auth/authenticate?action=" . $action);
    exit();
}

// Retrieve 'action' after redirect

$action = $_SESSION['oauth_action'] ?? 'login';
$method = $_SESSION['oauth_method'] ?? 'google'; // Default to 'google' if missing

// Set accountType and oauthProvider
$accountType = 'oauth';
$oauthProvider = ($method === '42intra') ? '42 intra' : 'google';


// If Google OAuth was denied, handle the error
if (isset($_GET['error'])) {
    $_SESSION['error'] = "Google authentication failed: " . $_GET['error'];
    header("Location: /auth/authenticate?action=login");
    exit();
}

// Load environment variables
$env = loadEnv('/var/www/env/.env');

// Initialize Google Client
$client = new Google_Client();
$client->setClientId($env['OAUTH_CLIENT_ID']);
$client->setClientSecret($env['OAUTH_CLIENT_SECRET']);
$client->setRedirectUri("https://leetmakers.com/auth/oauth");

$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
$client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);

$authUrl = $client->createAuthUrl();

// If no authorization code is received, redirect to Google OAuth
if (!isset($_GET['code'])) {
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit();
}

// Exchange authorization code for an access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

// Check for token errors
if (isset($token['error'])) {
    $_SESSION['error'] = "Error: Unable to authenticate with Google: " . $token['error'];
    header("Location: /auth/authenticate?action=login");
    exit();
}

// Ensure access token is present
if (!isset($token['access_token'])) {
    $_SESSION['error'] = "Error: Access token not found.";
    header("Location: /auth/authenticate?action=login");
    exit();
}

// Store access token in session
$_SESSION['access_token'] = $token['access_token'];

// Retrieve user profile information
$client->setAccessToken($token['access_token']);
$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();

// Extract user details
$acntid = $userInfo->id;
$email = filter_var($userInfo->email, FILTER_SANITIZE_EMAIL);
$name = filter_var($userInfo->name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$gender = $userInfo->gender ?? 'unspecified';
$picture = $userInfo->picture ?? '';

// Split name into first and last names
$nameParts = explode(' ', trim($name));
$fstname = $nameParts[0] ?? '';
$lstname = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

$username = generateUsername($fstname, $lstname, $conn);

// Validate required fields
$redirectUrl = "/auth/authenticate?action=" . $action;

validateEmailFormat($email, $redirectUrl);

// Register User if action is 'register'
if ($action === 'register') {
    // Check if the user already exists via Google ID or Email
    $checkAccountQuery = "SELECT id, email, googleID FROM identity WHERE googleID = ? OR email = ?";
    $checkAccountStmt = $conn->prepare($checkAccountQuery);
    $checkAccountStmt->bind_param("ss", $acntid, $email);
    $checkAccountStmt->execute();
    $checkAccountResult = $checkAccountStmt->get_result();

    if ($checkAccountResult->num_rows > 0) {
        $_SESSION['error'] = "Error: Account already exists. Please login instead.";
        header("Location: /auth/authenticate?action=login");
        exit();
    }
    $checkAccountStmt->close();

    // Generate unique picture directory ID (16 characters)
    $pictureDirId = bin2hex(random_bytes(8));

    // Insert new OAuth user into the database
    $insertAccountQuery = "INSERT INTO identity (firstname, lastname, email, gender, username, pictureType, picture, pictureDirId, accountType, oauthProvider, googleID, accountStatus, terms) VALUES (?, ?, ?, ?, ?, 'url', ?, ?, ?, ?, ?, 'verified', 'accepted')";
    $stmt = $conn->prepare($insertAccountQuery);
    $stmt->bind_param("ssssssssss", $fstname, $lstname, $email, $gender, $username, $picture, $pictureDirId, $accountType, $oauthProvider, $acntid);

    if ($stmt->execute()) {
        // Get the ID of the newly created user
        $newUserId = $stmt->insert_id;

        // Log the user in immediately
        session_regenerate_id(true);
        $_SESSION['id'] = $newUserId;
        $_SESSION['google_id'] = $acntid;

        // Send the verification email with the retrieved user data
        sendEmail('oauth-registered', $email, $fstname, $lstname, [
            'accountType' => $oauthProvider . ' oauth'
        ]);

        $_SESSION['success'] = "Success: Account created successfully! You are now logged in.";

        $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '/dashboard';
        // Redirect to dashboard instead of login page
        unset($_SESSION['redirect_after_login']);
        header("Location: $redirectTo");
        exit();
    } else {
        $_SESSION['error'] = "Error: Unable to create account. Please try again later.";
    }

    // Close database connection
    $stmt->close();
    $conn->close();

    header("Location: $redirectUrl");
    exit();
}

// Log the user in using Google OAuth
if ($action === 'login') {
    // Check if the user exists via Google ID or Email
    $checkGoogleIdQuery = "SELECT id, googleID FROM identity WHERE googleID = ?";
    $checkGoogleIdStmt = $conn->prepare($checkGoogleIdQuery);
    $checkGoogleIdStmt->bind_param("s", $acntid);
    $checkGoogleIdStmt->execute();
    $checkGoogleIdResult = $checkGoogleIdStmt->get_result();

    if ($checkGoogleIdResult->num_rows === 0) {
        $_SESSION['error'] = "Error: Account isn't registered yet. Please register and try again.";
        header("Location: /auth/authenticate?action=register");
        exit();
    }

    // Log the user in
    $userData = $checkGoogleIdResult->fetch_assoc();

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Store user session data
    $_SESSION['id'] = $userData['id']; // Normal login flow
    $_SESSION['google_id'] = $acntid;  // Store the google_id in the session

    // Check if a redirect URL is stored in the session, otherwise default to dashboard
    $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '/dashboard';

    // Unset the redirect after login once used
    unset($_SESSION['redirect_after_login']);

    // Redirect to the intended page
    header("Location: $redirectTo");
    exit();
}

// Redirect for invalid action
$_SESSION['error'] = "Error: Invalid OAuth request.";
header("Location: /auth/authenticate?action=login");
exit();
?>

