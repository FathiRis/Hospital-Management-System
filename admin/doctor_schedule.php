<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = new Database();

// Get doctor details
$db->query("SELECT d.*, u.first_name, u.last_name 
            FROM doctors d 
            JOIN users u ON d.user_id = u.user_id 
            WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor = $db->single();

// Get doctor's schedule
$db->query("SELECT * FROM appointments 
            WHERE doctor_id = :doctor_id 
            AND appointment_date >= CURDATE() 
            ORDER BY appointment_date, appointment_time");
$db->bind(':doctor_id', $doctor_id);
$appointments = $db->resultset();

// Get doctor's appointments
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last 
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           WHERE a.doctor_id = :doctor_id 
           ORDER BY a.appointment_date DESC, a.appointment_time DESC 
           LIMIT 10");
$db->bind(':doctor_id', $doctor_id);
$appointments = $db->resultset();

// Handle cancel appointment
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = intval($_POST['appointment_id']);
    
    $db->query("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = :id");
    $db->bind(':id', $appointment_id);
    if ($db->execute()) {
        $_SESSION['success'] = "Appointment cancelled successfully";
    } else {
        $_SESSION['error'] = "Failed to cancel appointment";
    }
    header("Location: doctor_schedule.php?id=$doctor_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedule - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--bg-light);
        }
        
        .doctor-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .doctor-avatar {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 32px;
            border: 3px solid rgba(255,255,255,0.2);
        }
        
        .schedule-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .calendar-view {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 25px;
            margin-top: 20px;
        }
        
        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        
        .calendar-day {
            min-height: 100px;
            border: 1px solid #eee;
            padding: 10px;
        }
        
        .day-header {
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .appointment-item {
            background: var(--bg-very-light);
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .calendar-body {
                grid-template-columns: 1fr;
            }
            
            .calendar-header {
                display: none;
            }
            
            .day-header {
                text-align: left;
                background: var(--bg-light);
                padding: 5px;
                border-radius: 5px;
            }
        }

        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .modal-body p {
            margin-bottom: 25px;
            color: var(--dark-gray);
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-dangerb {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer; 
        }

        .btn-danger {
            background: transparent;
            color: var(--danger-color, #dc3545);
            border: 2px solid var(--danger-color, #dc3545);
        }

        .btn-danger:hover {
            background: var(--danger-color, #dc3545);
            color: white;
            border-color: var(--danger-color, #dc3545);
        }

        .btn-secondaryd {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondaryd:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(3, 4, 94, 0.3);
        }

    </style>
</head>
<body>
    <div class="admin-container">
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
                    <a href="doctors.php" class="nav-link active">
                        <i class="fas fa-user-md"></i>
                        Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
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
                <h1>Doctor Schedule</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="doctor-info">
                <div class="doctor-avatar">
                    <?php echo strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <h2>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></h2>
                    <p><?php echo $doctor['specialization']; ?></p>
                </div>
            </div>
            
            <div class="schedule-actions">
                <a href="add_appointment.php?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Appointment
                </a>
                <a href="view_doctor.php?id=<?php echo $doctor_id; ?>" class="btn btn-secondary">
                    <a href="doctors.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">
                    <i class="fas fa-arrow-left"></i> Back to Profile </a>
                </a>
            </div>
            
            <div class="calendar-view">
                <h3>Upcoming Appointments</h3>
                
                <?php if (empty($appointments)): ?>
                    <div class="no-appointments">
                        <p>No upcoming appointments found for this doctor.</p>
                    </div>
                <?php else: ?>
                    <div class="appointment-list">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-time">
                                    <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                    at <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                </div>
                                <div class="patient-info">
                                    <div>
                                        <span class="patient-name">Patient: <?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></span>
                                        <div class="appointment-reason">Reason: <?php echo $appointment['reason']; ?></div>
                                    </div>
                                    <div class="appointment-actions">
                                        <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>"  
                                            class="btn-sm btn-view" 
                                            onclick="viewAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                            View
                                        </a>
                                        <a href="edit_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" 
                                            class="btn-sm btn-edit">
                                            Edit
                                        </a>
                                        <form method="POST" class="cancel-form" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                            <button type="button" class="btn-sm btn-dangerb" onclick="showCancelModal(<?php echo $appointment['appointment_id']; ?>)">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="cancelModal" class="login-modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Cancellation</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment?</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="confirmCancellation()">Yes, Cancel</button>
                    <button type="button" class="btn btn-secondaryd" onclick="closeCancelModal()">No, Keep It</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentCancelForm = null;

        function showCancelModal(appointmentId) {
            currentCancelForm = document.querySelector(`form.cancel-form input[value="${appointmentId}"]`).parentNode;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            currentCancelForm = null;
        }

        function confirmCancellation() {
            if (currentCancelForm) {
                // Create a hidden input for the submit action
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cancel_appointment';
                input.value = '1';
                currentCancelForm.appendChild(input);
                
                // Submit the form
                currentCancelForm.submit();
            }
            closeCancelModal();
        }

        // Close modal when clicking outside or on X
        document.querySelector('.login-modal').addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('close-modal')) {
                closeCancelModal();
            }
        });
    </script>

</body>
</html>