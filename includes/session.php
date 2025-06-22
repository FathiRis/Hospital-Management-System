<?php
session_start();

if (!function_exists('requireLogin')) {
    function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
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
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
}

// function getUserName() {
//     return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
// }
?>