<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $specialization = sanitizeInput($_POST['specialization']);
    $license_number = sanitizeInput($_POST['license_number']);
    $department = sanitizeInput($_POST['department']);
    $consultation_fee = sanitizeInput($_POST['consultation_fee']);
    $bio = sanitizeInput($_POST['bio']);
    $address = sanitizeInput($_POST['address']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($specialization) || empty($license_number) || empty($department) || empty($consultation_fee)) {
        $error_message = "All required fields must be filled.";
    } elseif (!validateEmail($email)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!is_numeric($consultation_fee) || $consultation_fee <= 0) {
        $error_message = "Please enter a valid consultation fee.";
    } else {
        try {
            // Check if username or email already exists
            $db->query("SELECT user_id FROM users WHERE username = :username OR email = :email");
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $existing = $db->single();
            
            if ($existing) {
                $error_message = "Username or email already exists.";
            } else {
                // Insert user
                $db->query("INSERT INTO users (username, password, email, role, first_name, last_name, phone, address) VALUES (:username, :password, :email, 'doctor', :first_name, :last_name, :phone, :address)");
                $db->bind(':username', $username);
                $db->bind(':password', $password);
                $db->bind(':email', $email);
                $db->bind(':first_name', $first_name);
                $db->bind(':last_name', $last_name);
                $db->bind(':phone', $phone);
                $db->bind(':address', $address);
                $db->execute();
                
                $user_id = $db->lastInsertId();
                
                // Insert doctor
                $db->query("INSERT INTO doctors (user_id, specialization, license_number, department, consultation_fee, bio) VALUES (:user_id, :specialization, :license_number, :department, :consultation_fee, :bio)");
                $db->bind(':user_id', $user_id);
                $db->bind(':specialization', $specialization);
                $db->bind(':license_number', $license_number);
                $db->bind(':department', $department);
                $db->bind(':consultation_fee', $consultation_fee);
                $db->bind(':bio', $bio);
                $db->execute();
                
                logActivity(getUserId(), 'Add Doctor', "Added new doctor: Dr. $first_name $last_name");
                
                header('Location: doctors.php?success=Doctor added successfully');
                exit();
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediCare Admin</h2>
                <p>Hospital Management</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="doctors.php" class="nav-link active">
                        <i class="fas fa-user-md"></i>
                        Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="billing.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Billing
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pharmacy.php" class="nav-link">
                        <i class="fas fa-pills"></i>
                        Pharmacy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Add New Doctor</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Doctor Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Doctor Information</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization" class="form-label">Specialization *</label>
                            <input type="text" id="specialization" name="specialization" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="license_number" class="form-label">License Number *</label>
                            <input type="text" id="license_number" name="license_number" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="department" class="form-label">Department *</label>
                            <input type="text" id="department" name="department" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="consultation_fee" class="form-label">Consultation Fee *</label>
                            <input type="number" id="consultation_fee" name="consultation_fee" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Doctor's biography and qualifications"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Add Doctor</button>
                        <a href="doctors.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>