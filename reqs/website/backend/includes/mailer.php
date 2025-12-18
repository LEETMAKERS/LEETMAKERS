<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../composer/vendor/autoload.php';
require_once __DIR__ . '/credLoader.php';

// Load environment variables
$env = loadEnv('/var/www/env/.env');

date_default_timezone_set('Africa/Casablanca');

// Validate essential environment variables
if (empty($env['MAIL_HOST']) || empty($env['MAIL_PORT']) || empty($env['PASS_APP_MAIL']) || empty($env['PASS_APP_KEY'])) {
    throw new Exception("Mail configuration variables are missing or invalid.");
}

// Initialize PHPMailer
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = gethostbyname($env['MAIL_HOST']); // Resolve hostname to IP
$mail->Port = $env['MAIL_PORT'] ?? 587; // Default to 587 if not set
$mail->SMTPAuth = true;
$mail->Username = $env['PASS_APP_MAIL'];
$mail->Password = $env['PASS_APP_KEY'];

// Debugging (optional, for development)
// $mail->SMTPDebug = SMTP::DEBUG_OFF;

return $mail;
?>
