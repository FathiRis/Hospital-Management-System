<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Get all doctors
$db->query("SELECT d.doctor_id, u.first_name, u.last_name, d.specialization, d.department, d.consultation_fee 
           FROM doctors d 
           JOIN users u ON d.user_id = u.user_id 
           ORDER BY u.first_name");
$doctors = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = sanitizeInput($_POST['doctor_id']);
    $appointment_date = sanitizeInput($_POST['appointment_date']);
    $appointment_time = sanitizeInput($_POST['appointment_time']);
    $reason = sanitizeInput($_POST['reason']);
    
    // Validation
    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error_message = "All required fields must be filled.";
    } elseif (!isValidDate($appointment_date)) {
        $error_message = "Please enter a valid date.";
    } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $error_message = "Appointment date cannot be in the past.";
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
                $error_message = "Doctor already has an appointment at this time. Please choose a different time.";
            } else {
                // Insert appointment
                $db->query("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, 'Scheduled')");
                $db->bind(':patient_id', $patient_id);
                $db->bind(':doctor_id', $doctor_id);
                $db->bind(':appointment_date', $appointment_date);
                $db->bind(':appointment_time', $appointment_time);
                $db->bind(':reason', $reason);
                $db->execute();
                
                logActivity(getUserId(), 'Book Appointment', "Booked appointment with doctor ID: $doctor_id");
                
                header('Location: appointments.php?success=Appointment booked successfully');
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
    <title>Book Appointment - Hospital Management System</title>
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
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        My Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="book_appointment.php" class="nav-link active">
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
                    <h1>Book New Appointment</h1>
                    <p>Schedule an appointment with your preferred doctor</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Book Appointment Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Appointment Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="doctor_id" class="form-label">Select Doctor *</label>
                        <select id="doctor_id" name="doctor_id" class="form-control" required>
                            <option value="">Choose a doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['doctor_id']; ?>" data-fee="<?php echo $doctor['consultation_fee']; ?>">
                                    Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?> - 
                                    <?php echo $doctor['specialization']; ?> 
                                    (<?php echo $doctor['department']; ?>) - 
                                    Rs. <?php echo number_format($doctor['consultation_fee'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label">Preferred Date *</label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time" class="form-label">Preferred Time *</label>
                            <select id="appointment_time" name="appointment_time" class="form-control" required>
                                <option value="">Select time</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="09:30">09:30 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="14:30">02:30 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="15:30">03:30 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="16:30">04:30 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason" class="form-label">Reason for Visit</label>
                        <textarea id="reason" name="reason" class="form-control" rows="4" placeholder="Please describe your symptoms or reason for the visit"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                        <a href="dashboard.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
            
            <!-- Available Doctors -->
            <div class="appointments-section">
                <div class="section-header">
                    <h3 class="section-title">Available Doctors</h3>
                </div>
                
                <div class="appointments-list">
                    <?php foreach ($doctors as $doctor): ?>
                    <div class="appointment-item">
                        <div class="appointment-details">
                            <h4>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></h4>
                            <p><i class="fas fa-stethoscope"></i> <?php echo $doctor['specialization']; ?></p>
                            <p><i class="fas fa-building"></i> <?php echo $doctor['department']; ?></p>
                            <p><i class="fas fa-dollar-sign"></i> Consultation Fee: Rs. <?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                        </div>
                        <div class="appointment-actions">
                            <button onclick="selectDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn btn-sm btn-primary">Select Doctor</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectDoctor(doctorId) {
            document.getElementById('doctor_id').value = doctorId;
            document.getElementById('doctor_id').focus();
        }
        
        // Auto-scroll to form when doctor is selected
        document.getElementById('doctor_id').addEventListener('change', function() {
            if (this.value) {
                document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html>