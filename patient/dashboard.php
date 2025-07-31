<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('patient');

$db = new Database();

// Get patient information
$db->query("SELECT p.*, u.first_name, u.last_name, u.email, u.phone FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.user_id = :user_id");
$db->bind(':user_id', getUserId());
$patient_info = $db->single();

// Get upcoming appointments
$db->query("SELECT a.*, d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization 
           FROM appointments a 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE a.patient_id = :patient_id AND a.appointment_date >= CURDATE() 
           ORDER BY a.appointment_date, a.appointment_time LIMIT 3");
$db->bind(':patient_id', $patient_info['patient_id']);
$upcoming_appointments = $db->resultset();

// Get recent medical records
$db->query("SELECT mr.*, d.first_name as doctor_name, d.last_name as doctor_last 
           FROM medical_records mr 
           JOIN doctors dt ON mr.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           WHERE mr.patient_id = :patient_id 
           ORDER BY mr.visit_date DESC LIMIT 3");
$db->bind(':patient_id', $patient_info['patient_id']);
$recent_records = $db->resultset();

// Get pending bills
$db->query("SELECT COUNT(*) as total FROM billing WHERE patient_id = :patient_id AND status = 'Unpaid'");
$db->bind(':patient_id', $patient_info['patient_id']);
$pending_bills = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Hospital Management System</title>
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
                <p>Patient ID: #<?php echo str_pad($patient_info['patient_id'], 6, '0', STR_PAD_LEFT); ?></p>
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
                    <h1>Welcome back, <?php echo $patient_info['first_name']; ?>!</h1>
                    <p>Manage your health and appointments</p>
                </div>
                <div class="header-right">
                    <a class="emergency-btn">
                        <i class="fas fa-exclamation-triangle"></i>
                        Emergency
                    </a>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="location.href='book_appointment.php'">
                    <div class="action-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="action-title">Book Appointment</div>
                    <div class="action-description">Schedule a new appointment with your doctor</div>
                </div>
                
                <div class="action-card" onclick="location.href='prescriptions.php'">
                    <div class="action-icon">
                        <i class="fas fa-prescription-bottle"></i>
                    </div>
                    <div class="action-title">View Prescriptions</div>
                    <div class="action-description">Check your current and past prescriptions</div>
                </div>
                
                <div class="action-card" onclick="location.href='medical_records.php'">
                    <div class="action-icon">
                        <i class="fas fa-vial"></i>
                    </div>
                    <div class="action-title">Test Results</div>
                    <div class="action-description">View your latest lab test results</div>
                </div>
                
                <div class="action-card" onclick="location.href='billing.php'">
                    <div class="action-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="action-title">Pay Bills</div>
                    <div class="action-description">View and pay your medical bills</div>
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
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="health-value"><?php echo $pending_bills; ?></div>
                    <div class="health-label">Pending Bills</div>
                </div>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="appointments-section">
                <div class="section-header">
                    <h3 class="section-title">Upcoming Appointments</h3>
                    <a href="appointments.php" class="view-all-btn">View All</a>
                </div>
                
                <div class="appointments-list">
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>No upcoming appointments</h4>
                                <p>You don't have any appointments scheduled.</p>
                                <p>Book a new appointment to see your doctor.</p>
                            </div>
                            <div class="appointment-actions">
                                <a href="book_appointment.php" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div class="appointment-details">
                                <h4>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></h4>
                                <p><i class="fas fa-stethoscope"></i> <?php echo $appointment['specialization']; ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo formatDate($appointment['appointment_date']); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo formatTime($appointment['appointment_time']); ?></p>
                            </div>
                            <div class="appointment-actions">
                                <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Medical Records -->
            <div class="records-section">
                <div class="section-header">
                    <h3 class="section-title">Recent Medical Records</h3>
                    <a href="medical_records.php" class="view-all-btn">View All</a>
                </div>
                
                <div class="records-list">
                    <?php if (empty($recent_records)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No medical records</div>
                                <div class="record-doctor">You don't have any medical records yet.</div>
                                <div class="record-diagnosis">Visit a doctor to start building your medical history.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_records as $record): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date"><?php echo formatDate($record['visit_date']); ?></div>
                                <div class="record-doctor">Dr. <?php echo $record['doctor_name'] . ' ' . $record['doctor_last']; ?></div>
                                <div class="record-diagnosis"><?php echo $record['diagnosis'] ?: 'General consultation'; ?></div>
                            </div>
                            <a href="view_record.php?id=<?php echo $record['record_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add click functionality to action cards
        document.querySelectorAll('.action-card[onclick]').forEach(card => {
            card.style.cursor = 'pointer';
        });
        
        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.action-card, .health-card, .appointment-item');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in-up');
            });
        });
    </script>
</body>
</html>