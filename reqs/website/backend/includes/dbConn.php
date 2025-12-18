<?php

// Include the credloader.php file
require_once __DIR__ . '/credLoader.php';

// Load environment variables
$env = loadEnv('/var/www/env/.env');

// Use the loaded environment variables
$sname = $env['PMA_HOST'] ?? '';
$uname = $env['MYSQL_USER'] ?? '';
$password = $env['MYSQL_PASSWORD'] ?? '';
$dbname = $env['MYSQL_DATABASE_NAME'] ?? '';

// Create a connection to the database
$conn = mysqli_connect($sname,$uname,$password,$dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
