<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('staff');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: billing.php');
    exit();
}

$bill_id = $_GET['id'];

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
                <h1>Bill Details</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Bill Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Bill #<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <div>
                        <a href="edit_bill.php?id=<?php echo $bill_id; ?>" class="btn-primary">Edit Bill</a>
                        <a href="billing.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Back to List</a>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Patient Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <p><strong>Name:</strong> <?php echo $bill['patient_name'] . ' ' . $bill['patient_last']; ?></p>
                        <p><strong>Age:</strong> <?php echo calculateAge($bill['dob']); ?> years</p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
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
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>
                        <p><strong>Amount:</strong> Rs. <?php echo number_format($bill['amount'], 2); ?></p>
                        <p><strong>Tax:</strong> Rs. <?php echo number_format($bill['tax_amount'], 2); ?></p>
                        <p><strong>Total:</strong> Rs. <?php echo number_format($bill['total_amount'], 2); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo $bill['payment_method'] ?: 'N/A'; ?></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($bill['description'])); ?>
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
