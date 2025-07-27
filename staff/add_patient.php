<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('staff');

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $blood_type = sanitizeInput($_POST['blood_type']);
    $address = sanitizeInput($_POST['address']);
    $emergency_contact_name = sanitizeInput($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitizeInput($_POST['emergency_contact_phone']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($dob) || empty($gender)) {
        $error_message = "All required fields must be filled.";
    } elseif (!validateEmail($email)) {
        $error_message = "Please enter a valid email address.";
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
                $db->query("INSERT INTO users (username, password, email, role, first_name, last_name, phone, address) VALUES (:username, :password, :email, 'patient', :first_name, :last_name, :phone, :address)");
                $db->bind(':username', $username);
                $db->bind(':password', $password);
                $db->bind(':email', $email);
                $db->bind(':first_name', $first_name);
                $db->bind(':last_name', $last_name);
                $db->bind(':phone', $phone);
                $db->bind(':address', $address);
                $db->execute();
                
                $user_id = $db->lastInsertId();
                
                // Insert patient
                $db->query("INSERT INTO patients (user_id, dob, gender, blood_type, emergency_contact_name, emergency_contact_phone) VALUES (:user_id, :dob, :gender, :blood_type, :emergency_contact_name, :emergency_contact_phone)");
                $db->bind(':user_id', $user_id);
                $db->bind(':dob', $dob);
                $db->bind(':gender', $gender);
                $db->bind(':blood_type', $blood_type);
                $db->bind(':emergency_contact_name', $emergency_contact_name);
                $db->bind(':emergency_contact_phone', $emergency_contact_phone);
                $db->execute();
                
                logActivity(getUserId(), 'Add Patient', "Added new patient: $first_name $last_name");
                
                header('Location: patients.php?success=Patient added successfully');
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
    <title>Add Patient - Hospital Management System</title>
    <link rel="stylesheet" href="../css/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediCare Staff</h2>
                <p>Hospital Management</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Patients
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
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Add New Patient</h1>
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
            
            <!-- Add Patient Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Patient Information</h3>
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
                            <label for="dob" class="form-label">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender *</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <?php foreach (getGenders() as $gender): ?>
                                    <option value="<?php echo $gender; ?>"><?php echo $gender; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_type" class="form-label">Blood Type</label>
                            <select id="blood_type" name="blood_type" class="form-control">
                                <option value="">Select Blood Type</option>
                                <?php foreach (getBloodTypes() as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Add Patient</button>
                        <a href="patients.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>