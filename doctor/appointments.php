<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

// Get doctor information
$doctor_id = getDoctorId(getUserId());

// Handle date filter
$date_filter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : date('Y-m-d');

// Get doctor's appointments for the selected date
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last, pt.dob, pt.blood_type, pt.phone 
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           WHERE a.doctor_id = :doctor_id AND DATE(a.appointment_date) = :date 
           ORDER BY a.appointment_time");
$db->bind(':doctor_id', $doctor_id);
$db->bind(':date', $date_filter);
$appointments = $db->resultset();

// Get doctor info for display
$db->query("SELECT d.*, u.first_name, u.last_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Hospital Management System</title>
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
                    <a href="prescriptions.php" class="nav-link">
                        <i class="fas fa-prescription-bottle"></i>
                        Prescriptions
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
                <div class="header-left">
                    <h1>My Appointments</h1>
                    <p>Manage your patient appointments</p>
                </div>
                <div class="header-right">
                    <div class="current-time" id="current-time"></div>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Date Filter -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Select Date</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                    </div>
                    <div class="form-group">
                        <a href="?date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">Today</a>
                        <a href="?date=<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="btn btn-secondary">Tomorrow</a>
                    </div>
                </form>
            </div>
            
            <!-- Appointments for Selected Date -->
            <div class="schedule-container">
                <div class="schedule-header">
                    <h3 class="schedule-title">Appointments for <?php echo formatDate($date_filter); ?></h3>
                    <div class="schedule-date"><?php echo count($appointments); ?> appointments</div>
                </div>
                
                <div class="appointment-list">
                    <?php if (empty($appointments)): ?>
                        <div class="appointment-card">
                            <div class="appointment-time">No appointments</div>
                            <div class="patient-info">
                                <div class="patient-name">You have no appointments scheduled for this date.</div>
                                <div class="appointment-reason">Enjoy your free time!</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-time"><?php echo formatTime($appointment['appointment_time']); ?></div>
                            <div class="patient-info">
                                <div class="patient-name">
                                    <?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </div>
                                <div class="appointment-reason">
                                    <?php echo $appointment['reason'] ?: 'General Consultation'; ?>
                                </div>
                                <div class="patient-details">
                                    Age: <?php echo calculateAge($appointment['dob']); ?> | 
                                    Blood Type: <?php echo $appointment['blood_type'] ?: 'N/A'; ?> | 
                                    Phone: <?php echo $appointment['phone'] ?: 'N/A'; ?>
                                </div>
                                <div class="appointment-actions">
                                    <a href="view_patient.php?id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-primary">View Patient</a>
                                    <?php if ($appointment['status'] === 'Scheduled'): ?>
                                    <a href="consultation.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-success">Start Consultation</a>
                                    <?php endif; ?>
                                    <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-secondary">Details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>