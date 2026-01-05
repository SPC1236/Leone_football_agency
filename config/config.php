<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'Freetown Football Agency');
define('SITE_URL', 'http://localhost/work2/');

// File upload paths
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('PROFILE_PATH', 'uploads/profiles/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
if (!file_exists(UPLOAD_PATH . 'profiles/')) {
    mkdir(UPLOAD_PATH . 'profiles/', 0777, true);
}

// Include database connection
require_once 'database.php';
?>