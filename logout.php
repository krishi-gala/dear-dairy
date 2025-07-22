<?php
// logout.php
require_once __DIR__ . '/config/config.php';

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>