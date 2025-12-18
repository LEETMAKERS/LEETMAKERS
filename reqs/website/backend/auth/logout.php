<?php
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to landing page (or the homepage, adjust path if necessary)
header("Location: /");
exit();
