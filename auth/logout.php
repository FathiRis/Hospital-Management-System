<?php
session_start();
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'Logout', 'User logged out');
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: ../index.php');
exit();
?>