<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: patients.php');
    exit();
}

$patient_id = $_GET['id'];

// Get patient information
$db->query("SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.address 
           FROM patients p 
           JOIN users u ON p.user_id = u.user_id 
           WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient = $db->single();

if (!$patient) {
    header('Location: patients.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $blood_type = sanitizeInput($_POST['blood_type']);
    $height = sanitizeInput($_POST['height']);
    $weight = sanitizeInput($_POST['weight']);
    $address = sanitizeInput($_POST['address']);
    $emergency_contact_name = sanitizeInput($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitizeInput($_POST['emergency_contact_phone']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($dob) || empty($gender)) {
        $error_message = "All required fields must be filled.";
    } elseif (!validateEmail($email)) {
        $error_message = "Please enter a valid email address.";
    } else {
        try {
            // Update user information
            $db->query("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address WHERE user_id = :user_id");
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':address', $address);
            $db->bind(':user_id', $patient['user_id']);
            $db->execute();
            
            // Update patient information
            $db->query("UPDATE patients SET dob = :dob, gender = :gender, blood_type = :blood_type, height = :height, weight = :weight, emergency_contact_name = :emergency_contact_name, emergency_contact_phone = :emergency_contact_phone WHERE patient_id = :patient_id");
            $db->bind(':dob', $dob);
            $db->bind(':gender', $gender);
            $db->bind(':blood_type', $blood_type);
            $db->bind(':height', $height);
            $db->bind(':weight', $weight);
            $db->bind(':emergency_contact_name', $emergency_contact_name);
            $db->bind(':emergency_contact_phone', $emergency_contact_phone);
            $db->bind(':patient_id', $patient_id);
            $db->execute();
            
            logActivity(getUserId(), 'Edit Patient', "Updated patient: $first_name $last_name");
            
            header('Location: view_patient.php?id=' . $patient_id . '&success=Patient updated successfully');
            exit();
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
    <title>Edit Patient - Hospital Management System</title>
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
                    <a href="patients.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="doctors.php" class="nav-link">
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
                <h1>Edit Patient</h1>
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
            
            <!-- Edit Patient Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Edit Patient Information</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($patient['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob" class="form-label">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $patient['dob']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender *</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <?php foreach (getGenders() as $gender): ?>
                                    <option value="<?php echo $gender; ?>" <?php echo $patient['gender'] === $gender ? 'selected' : ''; ?>><?php echo $gender; ?></option>
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
                                    <option value="<?php echo $type; ?>" <?php echo $patient['blood_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" id="height" name="height" class="form-control" step="0.1" value="<?php echo $patient['height']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" step="0.1" value="<?php echo $patient['weight']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($patient['emergency_contact_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($patient['emergency_contact_phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Update Patient</button>
                        <a href="view_patient.php?id=<?php echo $patient_id; ?>" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
```