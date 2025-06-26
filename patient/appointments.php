<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Get patient's appointments
$db->query("SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization, dt.department 
           FROM appointments a 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE a.patient_id = :patient_id 
           ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$db->bind(':patient_id', $patient_id);
$appointments = $db->resultset();

// Separate upcoming and past appointments
$upcoming_appointments = [];
$past_appointments = [];

foreach ($appointments as $appointment) {
    $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
    if (strtotime($appointment_datetime) >= time() && $appointment['status'] === 'Scheduled') {
        $upcoming_appointments[] = $appointment;
    } else {
        $past_appointments[] = $appointment;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Hospital Management System</title>
    <link rel="stylesheet" href="../css/patient.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="patient-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="patient-avatar">
                    <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                </div>
                <h2><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h2>
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
                    <h1>My Appointments</h1>
                    <p>View and manage your appointments</p>
                </div>
                <div class="header-right">
                    <a href="book_appointment.php" class="btn btn-primary">Book New Appointment</a>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Upcoming Appointments -->
            <div class="appointments-section">
                <div class="section-header">
                    <h3 class="section-title">Upcoming Appointments (<?php echo count($upcoming_appointments); ?>)</h3>
                </div>
                
                <div class="appointments-list">
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>No upcoming appointments</h4>
                                <p>You don't have any upcoming appointments scheduled.</p>
                                <p>Book a new appointment to see your doctor.</p>
                            </div>
                            <div class="appointment-actions">
                                <a href="book_appointment.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></h4>
                                <p><i class="fas fa-stethoscope"></i> <?php echo $appointment['specialization']; ?></p>
                                <p><i class="fas fa-building"></i> <?php echo $appointment['department']; ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo formatDate($appointment['appointment_date']); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo formatTime($appointment['appointment_time']); ?></p>
                                <?php if ($appointment['reason']): ?>
                                <p><i class="fas fa-notes-medical"></i> <?php echo $appointment['reason']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="appointment-actions">
                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                    <?php echo $appointment['status']; ?>
                                </span>
                                <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <a href="reschedule.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-warning">Reschedule</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Past Appointments -->
            <div class="appointments-section">
                <div class="section-header">
                    <h3 class="section-title">Past Appointments (<?php echo count($past_appointments); ?>)</h3>
                </div>
                
                <div class="appointments-list">
                    <?php if (empty($past_appointments)): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>No past appointments</h4>
                                <p>You don't have any appointment history yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($past_appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></h4>
                                <p><i class="fas fa-stethoscope"></i> <?php echo $appointment['specialization']; ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo formatDate($appointment['appointment_date']); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo formatTime($appointment['appointment_time']); ?></p>
                                <?php if ($appointment['diagnosis']): ?>
                                <p><i class="fas fa-diagnoses"></i> <?php echo $appointment['diagnosis']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="appointment-actions">
                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                    <?php echo $appointment['status']; ?>
                                </span>
                                <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <?php if ($appointment['status'] === 'Completed'): ?>
                                <a href="book_appointment.php?doctor_id=<?php echo $appointment['doctor_id']; ?>" class="btn btn-sm btn-secondary">Book Again</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>