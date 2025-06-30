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
    if (empty($date)) return 'N/A';
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('M d, Y g:i A', strtotime($datetime));
}

function formatTime($time) {
    if (empty($time)) return 'N/A';
    return date('g:i A', strtotime($time));
}

function calculateAge($dob) {
    if (empty($dob)) return 'N/A';
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

// Session and Authentication Functions
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserName() {
    return ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ../unauthorized.php');
        exit();
    }
}

// Database Helper Functions
function getDoctorId($user_id) {
    try {
        $db = new Database();
        $db->query("SELECT doctor_id FROM doctors WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $result = $db->single();
        return $result ? $result['doctor_id'] : null;
    } catch (Exception $e) {
        error_log("Error getting doctor ID: " . $e->getMessage());
        return null;
    }
}

function getPatientId($user_id) {
    try {
        $db = new Database();
        $db->query("SELECT patient_id FROM patients WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $result = $db->single();
        return $result ? $result['patient_id'] : null;
    } catch (Exception $e) {
        error_log("Error getting patient ID: " . $e->getMessage());
        return null;
    }
}

// ID Generation Functions
function generateAppointmentId() {
    return 'APT' . date('Ymd') . rand(1000, 9999);
}

function generateBillId() {
    return 'BILL' . date('Ymd') . rand(1000, 9999);
}

// Dropdown Data Functions
function getAppointmentStatuses() {
    return ['Scheduled', 'Completed', 'Cancelled', 'No-Show', 'In-Progress'];
}

function getBillingStatuses() {
    return ['Paid', 'Unpaid', 'Partial', 'Overdue'];
}

function getPrescriptionStatuses() {
    return ['Pending', 'Filled', 'Cancelled', 'Partial'];
}

function getBloodTypes() {
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
}

function getGenders() {
    return ['Male', 'Female', 'Other'];
}

function getUserRoles() {
    return ['admin', 'doctor', 'staff', 'patient'];
}

function getMedicineCategories() {
    return ['Analgesic', 'Antibiotic', 'Antidiabetic', 'Antihypertensive', 'Antihistamine', 'Antacid', 'Vitamin', 'Other'];
}

function getDosageForms() {
    return ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Ointment', 'Drops'];
}

function getDepartments() {
    return ['Cardiology', 'Pediatrics', 'Neurology', 'Orthopedics', 'General Medicine', 'Emergency', 'Surgery', 'Radiology', 'Laboratory'];
}

// Utility Functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $alertClass = 'alert-' . $alert['type'];
        echo '<div class="alert ' . $alertClass . '">' . htmlspecialchars($alert['message']) . '</div>';
        unset($_SESSION['alert']);
    }
}

// Validation Functions
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function isValidTime($time, $format = 'H:i') {
    $t = DateTime::createFromFormat($format, $time);
    return $t && $t->format($format) === $time;
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

// Financial Functions
function calculateTax($amount, $rate = 10) {
    return ($amount * $rate) / 100;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Medical Functions
function calculateBMI($weight, $height) {
    if ($weight <= 0 || $height <= 0) return 'N/A';
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters * $heightInMeters);
    return round($bmi, 1);
}

function getBMICategory($bmi) {
    if ($bmi < 18.5) return 'Underweight';
    if ($bmi < 25) return 'Normal weight';
    if ($bmi < 30) return 'Overweight';
    return 'Obese';
}

// File Upload Functions
function uploadFile($file, $uploadDir = 'uploads/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > 5000000) { // 5MB limit
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    $ext = array_search($mimeType, $allowedTypes, true);
    if ($ext === false) {
        throw new RuntimeException('Invalid file format.');
    }

    $fileName = sprintf('%s.%s', sha1_file($file['tmp_name']), $ext);
    $uploadPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $uploadPath;
}

// Search Functions
function searchPatients($searchTerm) {
    try {
        $db = new Database();
        $db->query("SELECT p.patient_id, u.first_name, u.last_name, u.email, u.phone 
                   FROM patients p 
                   JOIN users u ON p.user_id = u.user_id 
                   WHERE u.first_name LIKE :search 
                   OR u.last_name LIKE :search 
                   OR u.email LIKE :search 
                   OR u.phone LIKE :search 
                   ORDER BY u.first_name");
        $db->bind(':search', "%$searchTerm%");
        return $db->resultset();
    } catch (Exception $e) {
        error_log("Error searching patients: " . $e->getMessage());
        return [];
    }
}

function searchDoctors($searchTerm) {
    try {
        $db = new Database();
        $db->query("SELECT d.doctor_id, u.first_name, u.last_name, d.specialization, d.department 
                   FROM doctors d 
                   JOIN users u ON d.user_id = u.user_id 
                   WHERE u.first_name LIKE :search 
                   OR u.last_name LIKE :search 
                   OR d.specialization LIKE :search 
                   OR d.department LIKE :search 
                   ORDER BY u.first_name");
        $db->bind(':search', "%$searchTerm%");
        return $db->resultset();
    } catch (Exception $e) {
        error_log("Error searching doctors: " . $e->getMessage());
        return [];
    }
}

// Statistics Functions
function getMonthlyStats($month = null) {
    if (!$month) {
        $month = date('Y-m');
    }
    
    try {
        $db = new Database();
        $stats = [];
        
        // Appointments this month
        $db->query("SELECT COUNT(*) as total FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m') = :month");
        $db->bind(':month', $month);
        $stats['appointments'] = $db->single()['total'];
        
        // New patients this month
        $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient' AND DATE_FORMAT(created_at, '%Y-%m') = :month");
        $db->bind(':month', $month);
        $stats['new_patients'] = $db->single()['total'];
        
        // Revenue this month
        $db->query("SELECT SUM(total_amount) as total FROM billing WHERE status = 'Paid' AND DATE_FORMAT(billing_date, '%Y-%m') = :month");
        $db->bind(':month', $month);
        $stats['revenue'] = $db->single()['total'] ?: 0;
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting monthly stats: " . $e->getMessage());
        return ['appointments' => 0, 'new_patients' => 0, 'revenue' => 0];
    }
}

// Notification Functions
function addNotification($user_id, $title, $message, $type = 'info') {
    try {
        $db = new Database();
        $db->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (:user_id, :title, :message, :type, NOW())");
        $db->bind(':user_id', $user_id);
        $db->bind(':title', $title);
        $db->bind(':message', $message);
        $db->bind(':type', $type);
        $db->execute();
        return true;
    } catch (Exception $e) {
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

function getUnreadNotifications($user_id) {
    try {
        $db = new Database();
        $db->query("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = FALSE ORDER BY created_at DESC");
        $db->bind(':user_id', $user_id);
        return $db->resultset();
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

// System Health Functions
function checkSystemHealth() {
    $health = [
        'database' => false,
        'storage' => false,
        'memory' => false
    ];
    
    // Check database connection
    try {
        $db = new Database();
        $db->query("SELECT 1");
        $db->execute();
        $health['database'] = true;
    } catch (Exception $e) {
        error_log("Database health check failed: " . $e->getMessage());
    }
    
    // Check storage space
    $freeBytes = disk_free_space(".");
    $totalBytes = disk_total_space(".");
    $usagePercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
    $health['storage'] = $usagePercent < 90; // Alert if over 90% full
    
    // Check memory usage
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = ini_get('memory_limit');
    $memoryLimitBytes = convertToBytes($memoryLimit);
    $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;
    $health['memory'] = $memoryPercent < 80; // Alert if over 80% used
    
    return $health;
}

function convertToBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

//billing

// functions.php additions
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function hasMessage() {
    return !empty($_SESSION['message']);
}

function getMessage() {
    $message = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);
    return $message;
}

function getMessageType() {
    $type = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message_type']);
    return $type;
}
?>