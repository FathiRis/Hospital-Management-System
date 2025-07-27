<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('staff');

$db = new Database();

// Get all patients
$db->query("SELECT p.patient_id, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.user_id ORDER BY u.first_name");
$patients = $db->resultset();

// Get all doctors
$db->query("SELECT d.doctor_id, u.first_name, u.last_name, d.specialization FROM doctors d JOIN users u ON d.user_id = u.user_id ORDER BY u.first_name");
$doctors = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = sanitizeInput($_POST['patient_id']);
    $doctor_id = sanitizeInput($_POST['doctor_id']);
    $appointment_date = sanitizeInput($_POST['appointment_date']);
    $appointment_time = sanitizeInput($_POST['appointment_time']);
    $reason = sanitizeInput($_POST['reason']);
    
    // Validation
    if (empty($patient_id) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error_message = "All required fields must be filled.";
    } elseif (!isValidDate($appointment_date)) {
        $error_message = "Please enter a valid date.";
    } elseif (!isValidTime($appointment_time)) {
        $error_message = "Please enter a valid time.";
    } else {
        try {
            // Check for conflicting appointments
            $db->query("SELECT appointment_id FROM appointments WHERE doctor_id = :doctor_id AND appointment_date = :appointment_date AND appointment_time = :appointment_time AND status != 'Cancelled'");
            $db->bind(':doctor_id', $doctor_id);
            $db->bind(':appointment_date', $appointment_date);
            $db->bind(':appointment_time', $appointment_time);
            $existing = $db->single();
            
            if ($existing) {
                $error_message = "Doctor already has an appointment at this time.";
            } else {
                // Insert appointment
                $db->query("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, 'Scheduled')");
                $db->bind(':patient_id', $patient_id);
                $db->bind(':doctor_id', $doctor_id);
                $db->bind(':appointment_date', $appointment_date);
                $db->bind(':appointment_time', $appointment_time);
                $db->bind(':reason', $reason);
                $db->execute();
                
                logActivity(getUserId(), 'Add Appointment', "Scheduled appointment for patient ID: $patient_id with doctor ID: $doctor_id");
                
                header('Location: appointments.php?success=Appointment scheduled successfully');
                exit();
            }
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
    <title>Add Appointment - Hospital Management System</title>
    <link rel="stylesheet" href="../css/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediCare Staff</h2>
                <p>Hospital Management</p>
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
                        Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Patients
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
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Schedule New Appointment</h1>
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
            
            <!-- Add Appointment Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Appointment Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select id="patient_id" name="patient_id" class="form-control" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['patient_id']; ?>">
                                        <?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="doctor_id" class="form-label">Doctor *</label>
                            <select id="doctor_id" name="doctor_id" class="form-control" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['doctor_id']; ?>">
                                        Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?> - <?php echo $doctor['specialization']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label">Appointment Date *</label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time" class="form-label">Appointment Time *</label>
                            <input type="time" id="appointment_time" name="appointment_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea id="reason" name="reason" class="form-control" rows="4" placeholder="Describe the reason for this appointment"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Schedule Appointment</button>
                        <a href="appointments.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>