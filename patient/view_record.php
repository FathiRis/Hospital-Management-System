<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: medical_records.php');
    exit();
}

$record_id = $_GET['id'];

// Get medical record details (ensure it belongs to this patient)
$db->query("SELECT mr.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization, dt.department
           FROM medical_records mr 
           JOIN doctors dt ON mr.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE mr.record_id = :record_id AND mr.patient_id = :patient_id");
$db->bind(':record_id', $record_id);
$db->bind(':patient_id', $patient_id);
$record = $db->single();

if (!$record) {
    header('Location: medical_records.php');
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
    <title>Medical Record Details - Hospital Management System</title>
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
                    <a href="medical_records.php" class="nav-link active">
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
                    <h1>Medical Record Details</h1>
                    <p>View your medical record information</p>
                </div>
                <div class="header-right">
                    <a href="medical_records.php" class="btn btn-secondary">Back to Records</a>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Medical Record Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Medical Record - <?php echo formatDate($record['visit_date']); ?></h3>
                </div>
                
                <div class="health-summary">
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="health-value">Dr. <?php echo $record['doctor_name'] . ' ' . $record['doctor_last']; ?></div>
                        <div class="health-label"><?php echo $record['specialization']; ?></div>
                    </div>
                    
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="health-value"><?php echo formatDate($record['visit_date']); ?></div>
                        <div class="health-label">Visit Date</div>
                    </div>
                    
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="health-value"><?php echo $record['department']; ?></div>
                        <div class="health-label">Department</div>
                    </div>
                    
                    <?php if ($record['follow_up_date']): ?>
                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="health-value"><?php echo formatDate($record['follow_up_date']); ?></div>
                        <div class="health-label">Follow-up Date</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($record['chief_complaint']): ?>
                <div class="form-group">
                    <label class="form-label">Chief Complaint</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($record['chief_complaint'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($record['diagnosis']): ?>
                <div class="form-group">
                    <label class="form-label">Diagnosis</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($record['treatment']): ?>
                <div class="form-group">
                    <label class="form-label">Treatment</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($record['treatment'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($record['prescription']): ?>
                <div class="form-group">
                    <label class="form-label">Prescription</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($record['prescription'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($record['notes']): ?>
                <div class="form-group">
                    <label class="form-label">Doctor's Notes</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($record['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Record Created</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo formatDateTime($record['created_at']); ?>
                    </div>
                </div>
                
                <div class="appointment-actions" style="margin-top: 30px; text-align: center;">
                    <?php if ($record['follow_up_date'] && strtotime($record['follow_up_date']) >= time()): ?>
                    <a href="book_appointment.php?doctor_id=<?php echo $record['doctor_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Book Follow-up
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>