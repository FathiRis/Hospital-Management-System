<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Get patient's prescriptions
$db->query("SELECT pr.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization,
                  COUNT(pi.item_id) as total_items
           FROM prescriptions pr 
           JOIN doctors dt ON pr.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           LEFT JOIN prescription_items pi ON pr.prescription_id = pi.prescription_id
           WHERE pr.patient_id = :patient_id 
           GROUP BY pr.prescription_id
           ORDER BY pr.prescription_date DESC");
$db->bind(':patient_id', $patient_id);
$prescriptions = $db->resultset();

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
    <title>Prescriptions - Hospital Management System</title>
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
                    <a href="prescriptions.php" class="nav-link active">
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
                    <h1>My Prescriptions</h1>
                    <p>View and track your prescriptions</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Prescriptions -->
            <div class="records-section">
                <div class="section-header">
                    <h3 class="section-title">Prescription History (<?php echo count($prescriptions); ?> prescriptions)</h3>
                </div>
                
                <div class="records-list">
                    <?php if (empty($prescriptions)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No prescriptions</div>
                                <div class="record-doctor">You don't have any prescriptions yet.</div>
                                <div class="record-diagnosis">Visit a doctor to get prescribed medications.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($prescriptions as $prescription): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">
                                    Prescription #<?php echo str_pad($prescription['prescription_id'], 6, '0', STR_PAD_LEFT); ?>
                                    <span class="status-badge status-<?php echo strtolower($prescription['status']); ?>">
                                        <?php echo $prescription['status']; ?>
                                    </span>
                                </div>
                                <div class="record-doctor">
                                    <i class="fas fa-user-md"></i> Dr. <?php echo $prescription['doctor_name'] . ' ' . $prescription['doctor_last']; ?> - <?php echo $prescription['specialization']; ?>
                                </div>
                                <div class="record-diagnosis">
                                    <p><strong>Date:</strong> <?php echo formatDate($prescription['prescription_date']); ?></p>
                                    <p><strong>Items:</strong> <?php echo $prescription['total_items']; ?> medication(s)</p>
                                    <?php if ($prescription['total_amount'] > 0): ?>
                                    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($prescription['total_amount'], 2); ?></p>
                                    <?php endif; ?>
                                    <?php if ($prescription['instructions']): ?>
                                    <p><strong>Instructions:</strong> <?php echo $prescription['instructions']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($prescription['dispensed_date']): ?>
                                    <p><strong>Dispensed:</strong> <?php echo formatDate($prescription['dispensed_date']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Prescription Summary -->
            <div class="health-summary">
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-prescription-bottle"></i>
                    </div>
                    <div class="health-value"><?php echo count($prescriptions); ?></div>
                    <div class="health-label">Total Prescriptions</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="health-value">
                        <?php 
                        $pending = array_filter($prescriptions, function($p) { return $p['status'] === 'Pending'; });
                        echo count($pending);
                        ?>
                    </div>
                    <div class="health-label">Pending</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="health-value">
                        <?php 
                        $filled = array_filter($prescriptions, function($p) { return $p['status'] === 'Filled'; });
                        echo count($filled);
                        ?>
                    </div>
                    <div class="health-label">Filled</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="health-value">
                        Rs. <?php 
                        $total = array_sum(array_column($prescriptions, 'total_amount'));
                        echo number_format($total, 2);
                        ?>
                    </div>
                    <div class="health-label">Total Cost</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>