<?php

// Function to load .env file
function loadEnv($filePath) {
    $env = [];
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                // Remove potential wrapping quotes
                $value = trim($value, " \t\n\r\0\x0B\"");

                $env[$key] = $value;
            }
        }
    }
    return $env;
}

?>
