<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Get patient's bills
$db->query("SELECT b.*, a.appointment_date, a.appointment_time, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization
           FROM billing b 
           LEFT JOIN appointments a ON b.appointment_id = a.appointment_id
           LEFT JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           LEFT JOIN users d ON dt.user_id = d.user_id 
           WHERE b.patient_id = :patient_id 
           ORDER BY b.billing_date DESC");
$db->bind(':patient_id', $patient_id);
$bills = $db->resultset();

// Calculate totals
$total_amount = 0;
$paid_amount = 0;
$unpaid_amount = 0;

foreach ($bills as $bill) {
    $total_amount += $bill['total_amount'];
    if ($bill['status'] === 'Paid') {
        $paid_amount += $bill['total_amount'];
    } else {
        $unpaid_amount += $bill['total_amount'];
    }
}

// Get patient info
$db->query("SELECT p.*, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = :patient_id");
$db->bind(':patient_id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - Hospital Management System</title>
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
                    <h1>My Bills</h1>
                    <p>View and pay your medical bills</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Billing Summary -->
            <div class="health-summary">
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="health-value"><?php echo count($bills); ?></div>
                    <div class="health-label">Total Bills</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="health-value">$<?php echo number_format($total_amount, 2); ?></div>
                    <div class="health-label">Total Amount</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="health-value">$<?php echo number_format($paid_amount, 2); ?></div>
                    <div class="health-label">Paid Amount</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="health-value">$<?php echo number_format($unpaid_amount, 2); ?></div>
                    <div class="health-label">Outstanding</div>
                </div>
            </div>
            
            <!-- Bills -->
            <div class="records-section">
                <div class="section-header">
                    <h3 class="section-title">Billing History (<?php echo count($bills); ?> bills)</h3>
                </div>
                
                <div class="records-list">
                    <?php if (empty($bills)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No bills</div>
                                <div class="record-doctor">You don't have any bills yet.</div>
                                <div class="record-diagnosis">Bills will appear here after your appointments.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">
                                    Bill #<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?>
                                    <span class="status-badge status-<?php echo strtolower($bill['status']); ?>">
                                        <?php echo $bill['status']; ?>
                                    </span>
                                </div>
                                <div class="record-doctor">
                                    <?php if ($bill['doctor_name']): ?>
                                    <i class="fas fa-user-md"></i> Dr. <?php echo $bill['doctor_name'] . ' ' . $bill['doctor_last']; ?> - <?php echo $bill['specialization']; ?>
                                    <?php else: ?>
                                    <i class="fas fa-hospital"></i> Hospital Services
                                    <?php endif; ?>
                                </div>
                                <div class="record-diagnosis">
                                    <p><strong>Description:</strong> <?php echo $bill['description'] ?: 'Medical services'; ?></p>
                                    <p><strong>Amount:</strong> $<?php echo number_format($bill['amount'], 2); ?></p>
                                    <?php if ($bill['tax_amount'] > 0): ?>
                                    <p><strong>Tax:</strong> $<?php echo number_format($bill['tax_amount'], 2); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Total:</strong> $<?php echo number_format($bill['total_amount'], 2); ?></p>
                                    <p><strong>Billing Date:</strong> <?php echo formatDate($bill['billing_date']); ?></p>
                                    <p><strong>Due Date:</strong> <?php echo formatDate($bill['due_date']); ?></p>
                                    <?php if ($bill['payment_date']): ?>
                                    <p><strong>Paid Date:</strong> <?php echo formatDate($bill['payment_date']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($bill['payment_method']): ?>
                                    <p><strong>Payment Method:</strong> <?php echo $bill['payment_method']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($bill['appointment_date']): ?>
                                    <p><strong>Appointment:</strong> <?php echo formatDate($bill['appointment_date']) . ' at ' . formatTime($bill['appointment_time']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <a href="view_bill.php?id=<?php echo $bill['bill_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <a href="print_bill.php?id=<?php echo $bill['bill_id']; ?>" class="btn btn-sm btn-secondary" target="_blank">Print</a>
                                <?php if ($bill['status'] !== 'Paid'): ?>
                                <a href="pay_bill.php?id=<?php echo $bill['bill_id']; ?>" class="btn btn-sm btn-success">Pay Now</a>
                                <?php endif; ?>
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