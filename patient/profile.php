<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $blood_type = sanitizeInput($_POST['blood_type']);
    $height = sanitizeInput($_POST['height']);
    $weight = sanitizeInput($_POST['weight']);
    $emergency_contact_name = sanitizeInput($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitizeInput($_POST['emergency_contact_phone']);
    $allergies = sanitizeInput($_POST['allergies']);
    $current_medications = sanitizeInput($_POST['current_medications']);
    $medical_history = sanitizeInput($_POST['medical_history']);
    
    try {
        // Update user information
        $db->query("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address WHERE user_id = :user_id");
        $db->bind(':first_name', $first_name);
        $db->bind(':last_name', $last_name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':address', $address);
        $db->bind(':user_id', getUserId());
        $db->execute();
        
        // Update patient information
        $db->query("UPDATE patients SET dob = :dob, gender = :gender, blood_type = :blood_type, height = :height, weight = :weight, emergency_contact_name = :emergency_contact_name, emergency_contact_phone = :emergency_contact_phone, allergies = :allergies, current_medications = :current_medications, medical_history = :medical_history WHERE patient_id = :patient_id");
        $db->bind(':dob', $dob);
        $db->bind(':gender', $gender);
        $db->bind(':blood_type', $blood_type);
        $db->bind(':height', $height);
        $db->bind(':weight', $weight);
        $db->bind(':emergency_contact_name', $emergency_contact_name);
        $db->bind(':emergency_contact_phone', $emergency_contact_phone);
        $db->bind(':allergies', $allergies);
        $db->bind(':current_medications', $current_medications);
        $db->bind(':medical_history', $medical_history);
        $db->bind(':patient_id', $patient_id);
        $db->execute();
        
        // Update session variables
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        
        logActivity(getUserId(), 'Update Profile', "Updated patient profile");
        $success_message = "Profile updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}

// Get patient info
$db->query("SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.address FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hospital Management System</title>
    <link rel="stylesheet" href="../css/patient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="patient-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="patient-avatar">
                    <?php echo strtoupper(substr($patient_info['first_name'], 0, 1)); ?>
                </div>
                <h2><?php echo $patient_info['first_name'] . ' ' . $patient_info['last_name']; ?></h2>
                <p>Patient ID: #<?php echo str_pad($patient_id, 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        My Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="book_appointment.php" class="nav-link">
                        <i class="fas fa-calendar-plus"></i>
                        Book Appointment
                    </a>
                </li>
                <li class="nav-item">
                    <a href="medical_records.php" class="nav-link">
                        <i class="fas fa-file-medical"></i>
                        Medical Records
                    </a>
                </li>
                <li class="nav-item">
                    <a href="prescriptions.php" class="nav-link">
                        <i class="fas fa-prescription-bottle"></i>
                        Prescriptions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="billing.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Billing
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="header-left">
                    <h1>My Profile</h1>
                    <p>Manage your personal and medical information</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Personal Information</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($patient_info['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($patient_info['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($patient_info['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($patient_info['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($patient_info['address']); ?></textarea>
                    </div>
                    
                    <div class="form-header" style="margin-top: 30px;">
                        <h3 class="form-title">Medical Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $patient_info['dob']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="form-control" required>
                                <?php foreach (getGenders() as $gender): ?>
                                    <option value="<?php echo $gender; ?>" <?php echo $patient_info['gender'] === $gender ? 'selected' : ''; ?>><?php echo $gender; ?></option>
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
                                    <option value="<?php echo $type; ?>" <?php echo $patient_info['blood_type'] === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" id="height" name="height" class="form-control" step="0.1" value="<?php echo $patient_info['height']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" class="form-control" step="0.1" value="<?php echo $patient_info['weight']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($patient_info['emergency_contact_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($patient_info['emergency_contact_phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="allergies" class="form-label">Allergies</label>
                        <textarea id="allergies" name="allergies" class="form-control" rows="3" placeholder="List any known allergies"><?php echo htmlspecialchars($patient_info['allergies']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_medications" class="form-label">Current Medications</label>
                        <textarea id="current_medications" name="current_medications" class="form-control" rows="3" placeholder="List current medications you are taking"><?php echo htmlspecialchars($patient_info['current_medications']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="medical_history" class="form-label">Medical History</label>
                        <textarea id="medical_history" name="medical_history" class="form-control" rows="4" placeholder="Describe any significant medical history"><?php echo htmlspecialchars($patient_info['medical_history']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>