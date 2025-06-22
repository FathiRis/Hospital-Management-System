<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function calculateAge($dob) {
    $today = new DateTime();
    $birthDate = new DateTime($dob);
    $age = $today->diff($birthDate);
    return $age->y;
}

function sendNotification($to, $subject, $message) {
    // Simple email notification (you can implement actual email sending)
    // For now, we'll just log it
    error_log("Email to: $to, Subject: $subject, Message: $message");
    return true;
}

function logActivity($user_id, $action, $details = '') {
    try {
        $db = new Database();
        $db->query("INSERT INTO activity_logs (user_id, action, details, created_at) VALUES (:user_id, :action, :details, NOW())");
        $db->bind(':user_id', $user_id);
        $db->bind(':action', $action);
        $db->bind(':details', $details);
        $db->execute();
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        function getUserId() {
        return $_SESSION['user_id'] ?? null;
        }
    }
}


if (!function_exists('getUserRole')) {
    function getUserRole() {
        function getUserRole() {
        return $_SESSION['role'] ?? null;
        }
    }
}


if (!function_exists('getUserName')) {
    function getUserName() {
        return ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
    }
}
// function getUserName() {
//     return ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
// }

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}


if (!function_exists('hasRole')) {
    function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
}


if (!function_exists('requireRole')) {
    function requireRole($role) {
        if (!hasRole($role)) {
            // Redirect to access denied page or dashboard
            header("Location: access-denied.php");
            exit;
        }
    }
}
?>