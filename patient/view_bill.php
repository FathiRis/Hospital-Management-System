<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: billing.php');
    exit();
}

$patient_id = getPatientId(getUserId());

$bill_id = $_GET['id'];

$db->query("SELECT p.*, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient_info = $db->single();


// Get bill details
$db->query("SELECT b.*, p.first_name as patient_name, p.last_name as patient_last, pt.dob,
                   a.appointment_date, a.appointment_time, d.first_name as doctor_name, d.last_name as doctor_last
           FROM billing b 
           JOIN patients pt ON b.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           LEFT JOIN appointments a ON b.appointment_id = a.appointment_id
           LEFT JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           LEFT JOIN users d ON dt.user_id = d.user_id 
           WHERE b.bill_id = :bill_id");
$db->bind(':bill_id', $bill_id);
$bill = $db->single();

if (!$bill) {
    header('Location: billing.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bill - Hospital Management System</title>
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
                    <a href="billing.php" class="nav-link active">
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
                    <h1>Bill Details</h1>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Bill Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Bill #<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                </div>
                
                <div class="dashboard-grid">
                    
                    <div class="health-summary">
                        <div class="health-card">
                            <h3 class="card-title">Bill Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <p><strong>Billing Date:</strong> <?php echo formatDate($bill['billing_date']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo formatDate($bill['due_date']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge status-<?php echo strtolower($bill['status']); ?>">
                                <?php echo $bill['status']; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="health-summary">
                        <div class="health-card">
                            <h3 class="card-title">Payment Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>
                        <p><strong>Amount:</strong> Rs. <?php echo number_format($bill['amount'], 2); ?></p>
                        <p><strong>Tax:</strong> Rs. <?php echo number_format($bill['tax_amount'], 2); ?></p>
                        <p><strong>Total:</strong> Rs. <?php echo number_format($bill['total_amount'], 2); ?></p>
                        </div>
                </div>
            
                
                <?php if ($bill['appointment_date']): ?>
                <div class="form-group">
                    <label class="form-label">Related Appointment</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <p><strong>Date:</strong> <?php echo formatDate($bill['appointment_date']); ?></p>
                        <p><strong>Time:</strong> <?php echo formatTime($bill['appointment_time']); ?></p>
                        <?php if ($bill['doctor_name']): ?>
                        <p><strong>Doctor:</strong> Dr. <?php echo $bill['doctor_name'] . ' ' . $bill['doctor_last']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($bill['payment_date']): ?>
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo formatDate($bill['payment_date']); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
