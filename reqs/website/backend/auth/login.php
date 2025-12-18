<?php
session_start();

require_once __DIR__ . "/../includes/utils.php";

checkRequestMethodAndLoginState();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'])) {

    require_once __DIR__ . "/../includes/dbConn.php";

    // redirectUrl
    $redirectUrl = "/auth/authenticate?action=login";

    checkDatabaseConnection($conn, $redirectUrl);

    $username = validate($_POST['username']);
    $password = validate($_POST['password']);

    // Store form data in session for repopulation
    $_SESSION['temp_data'] = [
        'username' => $username
    ];

    checkEmptyFields([
        'Username' => $username,
        'Password' => $password
    ], $redirectUrl);

    $query = "SELECT id, password FROM identity WHERE username = ?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = "Error: Invalid username or password";
        header("Location: $redirectUrl");
        exit();
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        $stmt->close();
        $conn->close();
        $_SESSION['error'] = "Error: Invalid username or password";
        header("Location: $redirectUrl");
        exit();
    }

    session_regenerate_id(delete_old_session: true);
    $_SESSION['id'] = $user['id'];
    $_SESSION['username'] = $username;

    // Clear login form data on success
    unset($_SESSION['temp_data']);

    // Check if a redirect URL is stored in the session, otherwise default to dashboard
    $redirectTo = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '/dashboard';

    // Unset the redirect after login once used
    unset($_SESSION['redirect_after_login']);

    header("Location: $redirectTo");
    $stmt->close();
    $conn->close();
    exit();
} else {
    header("Location: $redirectUrl");
    exit();
}
