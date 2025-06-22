<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: register.php');
        exit();
    }
    
    if (!validateEmail($email)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: register.php');
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        header('Location: register.php');
        exit();
    }
    
    try {
        $db = new Database();
        
        // Check if username already exists
        $db->query("SELECT user_id FROM users WHERE username = :username");
        $db->bind(':username', $username);
        $existing_user = $db->single();
        
        if ($existing_user) {
            $_SESSION['error'] = 'Username already exists. Please choose a different username.';
            header('Location: register.php');
            exit();
        }
        
        // Check if email already exists
        $db->query("SELECT user_id FROM users WHERE email = :email");
        $db->bind(':email', $email);
        $existing_email = $db->single();
        
        if ($existing_email) {
            $_SESSION['error'] = 'Email already exists. Please use a different email address.';
            header('Location: register.php');
            exit();
        }
        
        // Insert new user
        $db->query("INSERT INTO users (username, password, email, role, first_name, last_name, phone) VALUES (:username, :password, :email, :role, :first_name, :last_name, :phone)");
        $db->bind(':username', $username);
        $db->bind(':password', $password); // Not hashed for practice
        $db->bind(':email', $email);
        $db->bind(':role', $role);
        $db->bind(':first_name', $first_name);
        $db->bind(':last_name', $last_name);
        $db->bind(':phone', $phone);
        
        if ($db->execute()) {
            $user_id = $db->lastInsertId();
            
            // If registering as patient, create patient record
            if ($role === 'patient') {
                $db->query("INSERT INTO patients (user_id, dob, gender) VALUES (:user_id, '1990-01-01', 'Male')");
                $db->bind(':user_id', $user_id);
                $db->execute();
            }
            
            // If registering as doctor, create doctor record (admin approval needed)
            if ($role === 'doctor') {
                $db->query("INSERT INTO doctors (user_id, specialization, license_number, department, consultation_fee) VALUES (:user_id, 'General Medicine', 'PENDING', 'General', 100.00)");
                $db->bind(':user_id', $user_id);
                $db->execute();
            }
            
            $_SESSION['success'] = 'Account created successfully! Please login with your credentials.';
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to create account. Please try again.';
            header('Location: register.php');
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
?>