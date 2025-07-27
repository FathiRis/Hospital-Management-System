<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: patients.php');
    exit();
}

$patient_id = $_GET['id'];

// Get patient information
$db->query("SELECT p.*, u.first_name, u.last_name, u.email, u.phone FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient = $db->single();

if (!$patient) {
    header('Location: patients.php');
    exit();
}

// Get patient's appointments
$db->query("SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization 
           FROM appointments a 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE a.patient_id = :patient_id 
           ORDER BY a.appointment_date DESC");
$db->bind(':patient_id', $patient_id);
$appointments = $db->resultset();

// Get patient's medical records
$db->query("SELECT mr.*, d.first_name as doctor_name, d.last_name as doctor_last 
           FROM medical_records mr 
           JOIN doctors dt ON mr.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE mr.patient_id = :patient_id 
           ORDER BY mr.visit_date DESC");
$db->bind(':patient_id', $patient_id);
$medical_records = $db->resultset();

// Get patient's bills
$db->query("SELECT * FROM billing WHERE patient_id = :patient_id ORDER BY billing_date DESC");
$db->bind(':patient_id', $patient_id);
$bills = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient History - Hospital Management System</title>
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
                    <a href="patients.php" class="nav-link active">
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
                <h1>Patient History</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Patient Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title"><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?> - Complete History</h3>
                    <a href="patients.php" class="btn-primary" style="background: #6c757d;">Back to Patients</a>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Total Appointments</h3>
                            <div class="card-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo count($appointments); ?></div>
                        <div class="card-change">All time</div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Medical Records</h3>
                            <div class="card-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo count($medical_records); ?></div>
                        <div class="card-change">Total records</div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Total Bills</h3>
                            <div class="card-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo count($bills); ?></div>
                        <div class="card-change">Billing records</div>
                    </div>
                </div>
            </div>
            
            <!-- Appointments History -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Appointment History</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                No appointments found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                <td><?php echo formatTime($appointment['appointment_time']); ?></td>
                                <td>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $appointment['reason'] ?: 'General consultation'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-view">View</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Medical Records -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Medical Records</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Visit Date</th>
                            <th>Doctor</th>
                            <th>Diagnosis</th>
                            <th>Treatment</th>
                            <th>Follow-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medical_records)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                No medical records found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($medical_records as $record): ?>
                            <tr>
                                <td><?php echo formatDate($record['visit_date']); ?></td>
                                <td>Dr. <?php echo $record['doctor_name'] . ' ' . $record['doctor_last']; ?></td>
                                <td><?php echo $record['diagnosis'] ?: 'N/A'; ?></td>
                                <td><?php echo $record['treatment'] ?: 'N/A'; ?></td>
                                <td><?php echo $record['follow_up_date'] ? formatDate($record['follow_up_date']) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Billing History -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Billing History</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill ID</th>
                            <th>Amount</th>
                            <th>Billing Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                No billing records found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td>#<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>Rs. <?php echo number_format($bill['total_amount'], 2); ?></td>
                                <td><?php echo formatDate($bill['billing_date']); ?></td>
                                <td><?php echo formatDate($bill['due_date']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($bill['status']); ?>">
                                        <?php echo $bill['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $bill['payment_method'] ?: 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>