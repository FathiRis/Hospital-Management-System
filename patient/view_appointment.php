<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit();
}

$appointment_id = $_GET['id'];

// Get appointment details (ensure it belongs to this patient)
$db->query("SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization, dt.department, dt.consultation_fee
           FROM appointments a 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE a.appointment_id = :appointment_id AND a.patient_id = :patient_id");
$db->bind(':appointment_id', $appointment_id);
$db->bind(':patient_id', $patient_id);
$appointment = $db->single();

if (!$appointment) {
    header('Location: appointments.php');
    exit();
}

// Get patient info
$db->query("SELECT p.*, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details - Hospital Management System</title>
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
                    <a href="appointments.php" class="nav-link active">
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
                <div class="header-left">
                    <h1>Appointment Details</h1>
                    <p>View your appointment information</p>
                </div>
                <div class="header-right">
                    <a href="appointments.php" class="btn btn-secondary">Back to Appointments</a>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Appointment Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Appointment #<?php echo str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                </div>
                
                <div class="health-summary">
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="health-value">Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></div>
                        <div class="health-label"><?php echo $appointment['specialization']; ?></div>
                    </div>
                    
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="health-value"><?php echo formatDate($appointment['appointment_date']); ?></div>
                        <div class="health-label">Appointment Date</div>
                    </div>
                    
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="health-value"><?php echo formatTime($appointment['appointment_time']); ?></div>
                        <div class="health-label">Time</div>
                    </div>
                    
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="health-value">
                            <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                <?php echo $appointment['status']; ?>
                            </span>
                        </div>
                        <div class="health-label">Status</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo $appointment['department']; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Consultation Fee</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        Rs. <?php echo number_format($appointment['consultation_fee'], 2); ?>
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
                    <label class="form-label">Doctor's Notes</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Appointment Created</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo formatDateTime($appointment['created_at']); ?>
                    </div>
                </div>
                
                <?php if ($appointment['status'] === 'Scheduled' && strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']) > time()): ?>
                <div class="appointment-actions" style="margin-top: 30px; text-align: center;">
                    <a href="cancel_appointment.php?id=<?php echo $appointment_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel Appointment</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>