<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: doctors.php');
    exit();
}

$doctor_id = $_GET['id'];

// Get doctor information
$db->query("SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.created_at 
           FROM doctors d 
           JOIN users u ON d.user_id = u.user_id 
           WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor = $db->single();

if (!$doctor) {
    header('Location: doctors.php');
    exit();
}

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

// Get statistics
$db->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$total_appointments = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = :doctor_id AND status = 'Completed'");
$db->bind(':doctor_id', $doctor_id);
$completed_appointments = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctor - Hospital Management System</title>
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
                <h1>Doctor Details</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Doctor Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Doctor Information</h3>
                    <a href="edit_doctor.php?id=<?php echo $doctor_id; ?>" class="btn-primary">Edit Doctor</a>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Personal Details</h3>
                            <div class="card-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <p><strong>Name:</strong> Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $doctor['email']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $doctor['phone'] ?: 'N/A'; ?></p>
                        <p><strong>Address:</strong> <?php echo $doctor['address'] ?: 'N/A'; ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Professional Details</h3>
                            <div class="card-icon">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                        </div>
                        <p><strong>Specialization:</strong> <?php echo $doctor['specialization']; ?></p>
                        <p><strong>Department:</strong> <?php echo $doctor['department']; ?></p>
                        <p><strong>License Number:</strong> <?php echo $doctor['license_number']; ?></p>
                        <p><strong>Consultation Fee:</strong> Rs. <?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Statistics</h3>
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <p><strong>Total Appointments:</strong> <?php echo $total_appointments; ?></p>
                        <p><strong>Completed:</strong> <?php echo $completed_appointments; ?></p>
                        <p><strong>Success Rate:</strong> <?php echo $total_appointments > 0 ? round(($completed_appointments / $total_appointments) * 100, 1) : 0; ?>%</p>
                        <p><strong>Joined:</strong> <?php echo formatDate($doctor['created_at']); ?></p>
                    </div>
                </div>
                
                <?php if ($doctor['bio']): ?>
                <div class="form-group">
                    <label class="form-label">Biography</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($doctor['bio'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Appointments -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Recent Appointments</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                No appointments found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                <td><?php echo formatTime($appointment['appointment_time']); ?></td>
                                <td><?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $appointment['reason'] ?: 'General consultation'; ?></td>
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