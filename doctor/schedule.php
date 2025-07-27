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
    $schedule_start = sanitizeInput($_POST['schedule_start']);
    $schedule_end = sanitizeInput($_POST['schedule_end']);
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    
    try {
        // Update doctor schedule
        $db->query("UPDATE doctors SET schedule_start = :schedule_start, schedule_end = :schedule_end, available_days = :available_days WHERE doctor_id = :doctor_id");
        $db->bind(':schedule_start', $schedule_start);
        $db->bind(':schedule_end', $schedule_end);
        $db->bind(':available_days', $available_days);
        $db->bind(':doctor_id', $doctor_id);
        $db->execute();
        
        logActivity(getUserId(), 'Update Schedule', "Updated doctor schedule");
        $success_message = "Schedule updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating schedule: " . $e->getMessage();
    }
}

// Get doctor info and current schedule
$db->query("SELECT d.*, u.first_name, u.last_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor_info = $db->single();

$available_days = explode(',', $doctor_info['available_days']);

// Get upcoming appointments
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last 
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           WHERE a.doctor_id = :doctor_id AND a.appointment_date >= CURDATE() AND a.status = 'Scheduled'
           ORDER BY a.appointment_date, a.appointment_time 
           LIMIT 10");
$db->bind(':doctor_id', $doctor_id);
$upcoming_appointments = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Hospital Management System</title>
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
                </li>
                <li class="nav-item">
                    <a href="schedule.php" class="nav-link active">
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
                    <h1>My Schedule</h1>
                    <p>Manage your availability and working hours</p>
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
            
            <!-- Current Schedule -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo formatTime($doctor_info['schedule_start']) . ' - ' . formatTime($doctor_info['schedule_end']); ?></div>
                    <div class="stat-label">Working Hours</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-number"><?php echo count($available_days); ?></div>
                    <div class="stat-label">Working Days</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?php echo count($upcoming_appointments); ?></div>
                    <div class="stat-label">Upcoming Appointments</div>
                </div>
            </div>
            
            <!-- Update Schedule Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Update Schedule</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="schedule_start" class="form-label">Start Time</label>
                            <input type="time" id="schedule_start" name="schedule_start" class="form-control" value="<?php echo $doctor_info['schedule_start']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="schedule_end" class="form-label">End Time</label>
                            <input type="time" id="schedule_end" name="schedule_end" class="form-control" value="<?php echo $doctor_info['schedule_end']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Available Days</label>
                        <div class="checkbox-group" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px;">
                            <?php 
                            $days = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];
                            foreach ($days as $short => $full): 
                            ?>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="available_days[]" value="<?php echo $short; ?>" <?php echo in_array($short, $available_days) ? 'checked' : ''; ?>>
                                <?php echo $full; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </div>
                </form>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="schedule-container">
                <div class="schedule-header">
                    <h3 class="schedule-title">Upcoming Appointments</h3>
                </div>
                
                <div class="appointment-list">
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="appointment-card">
                            <div class="appointment-time">No upcoming appointments</div>
                            <div class="patient-info">
                                <div class="patient-name">You have no appointments scheduled.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-time">
                                <?php echo formatDate($appointment['appointment_date']); ?><br>
                                <?php echo formatTime($appointment['appointment_time']); ?>
                            </div>
                            <div class="patient-info">
                                <div class="patient-name"><?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></div>
                                <div class="appointment-reason"><?php echo $appointment['reason'] ?: 'General consultation'; ?></div>
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