<?php
session_start();

// Include database and functions for logging
require_once '../config/database.php';
require_once '../includes/functions.php';

// Log the logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        logActivity($_SESSION['user_id'], 'Logout', 'User logged out successfully');
    } catch (Exception $e) {
        // Log error but don't prevent logout
        error_log("Logout activity log error: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../index.php?message=You have been logged out successfully');
exit();
?>