<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

// Get doctor information
$db->query("SELECT d.*, u.first_name, u.last_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.user_id = :user_id");
$db->bind(':user_id', getUserId());
$doctor_info = $db->single();

// Get today's appointments
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last, pt.dob, pt.blood_type 
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           WHERE a.doctor_id = :doctor_id AND DATE(a.appointment_date) = CURDATE() 
           ORDER BY a.appointment_time");
$db->bind(':doctor_id', $doctor_info['doctor_id']);
$today_appointments = $db->resultset();

// Get statistics
$db->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = :doctor_id AND DATE(appointment_date) = CURDATE()");
$db->bind(':doctor_id', $doctor_info['doctor_id']);
$today_count = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = :doctor_id AND status = 'Completed' AND MONTH(appointment_date) = MONTH(CURDATE())");
$db->bind(':doctor_id', $doctor_info['doctor_id']);
$monthly_patients = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM prescriptions WHERE doctor_id = :doctor_id AND status = 'Pending'");
$db->bind(':doctor_id', $doctor_info['doctor_id']);
$pending_prescriptions = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Hospital Management System</title>
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
                    <a href="dashboard.php" class="nav-link active">
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
                    <h1>Hello, Dr. <?php echo $doctor_info['last_name']; ?></h1>
                    <p>Today is <?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="header-right">
                    <div class="current-time" id="current-time"></div>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-number"><?php echo $today_count; ?></div>
                    <div class="stat-label">Today's Appointments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $monthly_patients; ?></div>
                    <div class="stat-label">Patients This Month</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <div class="stat-number"><?php echo $pending_prescriptions; ?></div>
                    <div class="stat-label">Pending Prescriptions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number">4.8</div>
                    <div class="stat-label">Patient Rating</div>
                </div>
            </div>
            
            <!-- Today's Schedule -->
            <div class="schedule-container">
                <div class="schedule-header">
                    <h3 class="schedule-title">Today's Schedule </h3>
                    <div class="schedule-date"><?php echo date('F j, Y'); ?></div>
                </div>
                
                <div class="appointment-list">
                    <?php if (empty($today_appointments)): ?>
                        <div class="appointment-card">
                            <div class="appointment-time">No appointments</div>
                            <div class="patient-info">
                                <div class="patient-name">You have no appointments scheduled for today.</div>
                                <div class="appointment-reason">Enjoy your free time!</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($today_appointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-time"><?php echo formatTime($appointment['appointment_time']); ?></div>
                            <div class="patient-info">
                                <div class="patient-name"><?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></div>
                                <div class="appointment-reason">
                                    <?php echo $appointment['reason'] ?: 'General Consultation'; ?> | 
                                    Age: <?php echo calculateAge($appointment['dob']); ?> | 
                                    Blood Type: <?php echo $appointment['blood_type'] ?: 'N/A'; ?>
                                </div>
                                <div class="appointment-actions">
                                    <a href="view_patient.php?id=<?php echo $appointment['patient_id']; ?>" class="btn btn-sm btn-primary">View Patient</a>
                                    <a href="consultation.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-success">Start Consultation</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Quick Actions</h3>
                </div>
                
                <div class="quick-stats">
                    <div class="stat-card" >
                        <div class="stat-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="stat-label">New Prescription</div>
                    </div>
                    
                    <div class="stat-card" onclick="location.href='patients.php'">
                        <div class="stat-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="stat-label">Search Patient</div>
                    </div>
                    
                    <div class="stat-card" >
                        <div class="stat-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="stat-label">Medical Records</div>
                    </div>
                    
                    <div class="stat-card" onclick="location.href='schedule.php'">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="stat-label">Update Schedule</div>
                    </div>
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
        
        // Add click functionality to stat cards
        document.querySelectorAll('.stat-card[onclick]').forEach(card => {
            card.style.cursor = 'pointer';
        });
    </script>
</body>
</html>