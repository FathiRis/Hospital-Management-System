<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    if (empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: ../index.php');
        exit();
    }
    
    try {
        $db = new Database();
        
        // Check if user exists with the provided username and role
        $db->query("SELECT * FROM users WHERE username = :username AND role = :role");
        $db->bind(':username', $username);
        $db->bind(':role', $role);
        $user = $db->single();
        
        if ($user && $password === $user['password']) { // Simple password check (not hashed)
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            // Log the login activity
            logActivity($user['user_id'], 'Login', 'User logged in successfully');
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                case 'doctor':
                    header('Location: ../doctor/dashboard.php');
                    break;
                case 'staff':
                    header('Location: ../staff/dashboard.php');
                    break;
                case 'patient':
                    header('Location: ../patient/dashboard.php');
                    break;
                default:
                    $_SESSION['error'] = 'Invalid user role.';
                    header('Location: ../index.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Invalid username, password, or role.';
            header('Location: ../index.php');
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>