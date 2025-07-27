<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient ID
$patient_id = getPatientId(getUserId());

// Get patient's medical records
$db->query("SELECT mr.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization 
           FROM medical_records mr 
           JOIN doctors dt ON mr.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE mr.patient_id = :patient_id 
           ORDER BY mr.visit_date DESC");
$db->bind(':patient_id', $patient_id);
$medical_records = $db->resultset();

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
    <title>Medical Records - Hospital Management System</title>
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
                    <a href="medical_records.php" class="nav-link active">
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
                    <h1>Medical Records</h1>
                    <p>View your complete medical history</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Medical Records -->
            <div class="records-section">
                <div class="section-header">
                    <h3 class="section-title">Medical History (<?php echo count($medical_records); ?> records)</h3>
                </div>
                
                <div class="records-list">
                    <?php if (empty($medical_records)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No medical records</div>
                                <div class="record-doctor">You don't have any medical records yet.</div>
                                <div class="record-diagnosis">Visit a doctor to start building your medical history.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($medical_records as $record): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date"><?php echo formatDate($record['visit_date']); ?></div>
                                <div class="record-doctor">
                                    <i class="fas fa-user-md"></i> Dr. <?php echo $record['doctor_name'] . ' ' . $record['doctor_last']; ?> - <?php echo $record['specialization']; ?>
                                </div>
                                <div class="record-diagnosis">
                                    <?php if ($record['chief_complaint']): ?>
                                    <p><strong>Chief Complaint:</strong> <?php echo $record['chief_complaint']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['diagnosis']): ?>
                                    <p><strong>Diagnosis:</strong> <?php echo $record['diagnosis']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['treatment']): ?>
                                    <p><strong>Treatment:</strong> <?php echo $record['treatment']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['prescription']): ?>
                                    <p><strong>Prescription:</strong> <?php echo $record['prescription']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['notes']): ?>
                                    <p><strong>Notes:</strong> <?php echo $record['notes']; ?></p>
                                    <?php endif; ?>
                                    <?php if ($record['follow_up_date']): ?>
                                    <p><strong>Follow-up:</strong> <?php echo formatDate($record['follow_up_date']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <a href="view_record.php?id=<?php echo $record['record_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Health Summary -->
            <div class="health-summary">
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="health-value"><?php echo $patient_info['blood_type'] ?: 'N/A'; ?></div>
                    <div class="health-label">Blood Type</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-weight"></i>
                    </div>
                    <div class="health-value"><?php echo $patient_info['weight'] ? $patient_info['weight'] . ' kg' : 'N/A'; ?></div>
                    <div class="health-label">Weight</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-ruler-vertical"></i>
                    </div>
                    <div class="health-value"><?php echo $patient_info['height'] ? $patient_info['height'] . ' cm' : 'N/A'; ?></div>
                    <div class="health-label">Height</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <div class="health-value"><?php echo calculateAge($patient_info['dob']); ?></div>
                    <div class="health-label">Age</div>
                </div>
                
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div class="health-value"><?php echo count($medical_records); ?></div>
                    <div class="health-label">Total Records</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>