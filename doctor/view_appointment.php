<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit();
}

$appointment_id = $_GET['id'];

// Get doctor information
$doctor_id = getDoctorId(getUserId());

// Get doctor info for display
$db->query("SELECT d.*, u.first_name, u.last_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor_info = $db->single();


// Get appointment details
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last, pt.dob, pt.blood_type,
                   d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization, dt.department
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE a.appointment_id = :appointment_id");
$db->bind(':appointment_id', $appointment_id);
$appointment = $db->single();

if (!$appointment) {
    header('Location: appointments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment - Hospital Management System</title>
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
                    <a href="appointments.php" class="nav-link active">
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
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Appointment Details</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Appointment Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Appointment #<?php echo str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <div>
                       <a href="appointments.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;"><i class="fas fa-arrow-left"></i> Back to List</a>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Patient Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <p><strong>Name:</strong> <?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></p>
                        <p><strong>Age:</strong> <?php echo calculateAge($appointment['dob']); ?> years</p>
                        <p><strong>Blood Type:</strong> <?php echo $appointment['blood_type'] ?: 'N/A'; ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Doctor Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <p><strong>Doctor:</strong> Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></p>
                        <p><strong>Specialization:</strong> <?php echo $appointment['specialization']; ?></p>
                        <p><strong>Department:</strong> <?php echo $appointment['department']; ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Appointment Details</h3>
                            <div class="card-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <p><strong>Date:</strong> <?php echo formatDate($appointment['appointment_date']); ?></p>
                        <p><strong>Time:</strong> <?php echo formatTime($appointment['appointment_time']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                <?php echo $appointment['status']; ?>
                            </span>
                        </p>
                        <p><strong>Created:</strong> <?php echo formatDateTime($appointment['created_at']); ?></p>
                    </div>
                </div>
                
                <?php if ($appointment['reason']): ?>
                <div class="form-group">
                    <label class="form-label">Reason for Visit</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($appointment['reason'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($appointment['diagnosis']): ?>
                <div class="form-group">
                    <label class="form-label">Diagnosis</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($appointment['diagnosis'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($appointment['notes']): ?>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>