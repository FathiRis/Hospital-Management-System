<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit();
}

$appointment_id = $_GET['id'];

// Get appointment details
$db->query("SELECT * FROM appointments WHERE appointment_id = :appointment_id");
$db->bind(':appointment_id', $appointment_id);
$appointment = $db->single();

if (!$appointment) {
    header('Location: appointments.php');
    exit();
}

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
    $status = sanitizeInput($_POST['status']);
    $reason = sanitizeInput($_POST['reason']);
    $diagnosis = sanitizeInput($_POST['diagnosis']);
    $notes = sanitizeInput($_POST['notes']);
    
    try {
        $db->query("UPDATE appointments SET patient_id = :patient_id, doctor_id = :doctor_id, appointment_date = :appointment_date, appointment_time = :appointment_time, status = :status, reason = :reason, diagnosis = :diagnosis, notes = :notes WHERE appointment_id = :appointment_id");
        $db->bind(':patient_id', $patient_id);
        $db->bind(':doctor_id', $doctor_id);
        $db->bind(':appointment_date', $appointment_date);
        $db->bind(':appointment_time', $appointment_time);
        $db->bind(':status', $status);
        $db->bind(':reason', $reason);
        $db->bind(':diagnosis', $diagnosis);
        $db->bind(':notes', $notes);
        $db->bind(':appointment_id', $appointment_id);
        $db->execute();
        
        logActivity(getUserId(), 'Edit Appointment', "Updated appointment ID: $appointment_id");
        
        header('Location: view_appointment.php?id=' . $appointment_id . '&success=Appointment updated successfully');
        exit();
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediCare Admin</h2>
                <p>Hospital Management</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="doctors.php" class="nav-link">
                        <i class="fas fa-user-md"></i>
                        Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link active">
                        <i class="fas fa-calendar-alt"></i>
                        Appointments
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
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Edit Appointment</h1>
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
            
            <!-- Edit Appointment Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Edit Appointment Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select id="patient_id" name="patient_id" class="form-control" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['patient_id']; ?>" <?php echo $appointment['patient_id'] == $patient['patient_id'] ? 'selected' : ''; ?>>
                                        <?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="doctor_id" class="form-label">Doctor *</label>
                            <select id="doctor_id" name="doctor_id" class="form-control" required>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['doctor_id']; ?>" <?php echo $appointment['doctor_id'] == $doctor['doctor_id'] ? 'selected' : ''; ?>>
                                        Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?> - <?php echo $doctor['specialization']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label">Appointment Date *</label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-control" value="<?php echo $appointment['appointment_date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time" class="form-label">Appointment Time *</label>
                            <input type="time" id="appointment_time" name="appointment_time" class="form-control" value="<?php echo $appointment['appointment_time']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <?php foreach (getAppointmentStatuses() as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $appointment['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3"><?php echo htmlspecialchars($appointment['reason']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnosis" class="form-label">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" class="form-control" rows="3"><?php echo htmlspecialchars($appointment['diagnosis']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Update Appointment</button>
                        <a href="view_appointment.php?id=<?php echo $appointment_id; ?>" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>