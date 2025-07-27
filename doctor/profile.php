<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

// Get doctor information
$doctor_id = getDoctorId(getUserId());

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $specialization = sanitizeInput($_POST['specialization']);
    $department = sanitizeInput($_POST['department']);
    $consultation_fee = sanitizeInput($_POST['consultation_fee']);
    $bio = sanitizeInput($_POST['bio']);
    $qualifications = sanitizeInput($_POST['qualifications']);
    $experience_years = sanitizeInput($_POST['experience_years']);
    
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
        
        // Update doctor information
        $db->query("UPDATE doctors SET specialization = :specialization, department = :department, consultation_fee = :consultation_fee, bio = :bio, qualifications = :qualifications, experience_years = :experience_years WHERE doctor_id = :doctor_id");
        $db->bind(':specialization', $specialization);
        $db->bind(':department', $department);
        $db->bind(':consultation_fee', $consultation_fee);
        $db->bind(':bio', $bio);
        $db->bind(':qualifications', $qualifications);
        $db->bind(':experience_years', $experience_years);
        $db->bind(':doctor_id', $doctor_id);
        $db->execute();
        
        // Update session variables
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        
        logActivity(getUserId(), 'Update Profile', "Updated doctor profile");
        $success_message = "Profile updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}

// Get doctor info
$db->query("SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.address FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hospital Management System</title>
    <link rel="stylesheet" href="../css/doctor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="doctor-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="doctor-avatar">
                    <?php echo strtoupper(substr($doctor_info['first_name'], 0, 1)); ?>
                </div>
                <h2>Dr. <?php echo $doctor_info['first_name'] . ' ' . $doctor_info['last_name']; ?></h2>
                <p><?php echo $doctor_info['specialization']; ?></p>
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
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        My Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="schedule.php" class="nav-link">
                        <i class="fas fa-clock"></i>
                        Schedule
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
                    <p>Manage your personal and professional information</p>
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
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($doctor_info['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($doctor_info['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($doctor_info['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($doctor_info['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($doctor_info['address']); ?></textarea>
                    </div>
                    
                    <div class="form-header" style="margin-top: 30px;">
                        <h3 class="form-title">Professional Information</h3>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" id="specialization" name="specialization" class="form-control" value="<?php echo htmlspecialchars($doctor_info['specialization']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" id="department" name="department" class="form-control" value="<?php echo htmlspecialchars($doctor_info['department']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="consultation_fee" class="form-label">Consultation Fee</label>
                            <input type="number" id="consultation_fee" name="consultation_fee" class="form-control" step="0.01" value="<?php echo $doctor_info['consultation_fee']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="experience_years" class="form-label">Years of Experience</label>
                            <input type="number" id="experience_years" name="experience_years" class="form-control" value="<?php echo $doctor_info['experience_years']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="qualifications" class="form-label">Qualifications</label>
                        <textarea id="qualifications" name="qualifications" class="form-control" rows="3" placeholder="List your medical qualifications and certifications"><?php echo htmlspecialchars($doctor_info['qualifications']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio" class="form-label">Biography</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Write a brief biography about yourself"><?php echo htmlspecialchars($doctor_info['bio']); ?></textarea>
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