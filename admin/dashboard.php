<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Get dashboard statistics
$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient'");
$total_patients = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'doctor'");
$total_doctors = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM appointments WHERE DATE(appointment_date) = CURDATE()");
$today_appointments = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM billing WHERE status = 'Unpaid'");
$pending_bills = $db->single()['total'];

// Get recent appointments
$db->query("SELECT a.*, p.first_name as patient_name, p.last_name as patient_last, d.first_name as doctor_name, d.last_name as doctor_last 
           FROM appointments a 
           JOIN patients pt ON a.patient_id = pt.patient_id 
           JOIN users p ON pt.user_id = p.user_id 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           JOIN users d ON dt.user_id = d.user_id 
           ORDER BY a.created_at DESC LIMIT 5");
$recent_appointments = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital Management System</title>
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
                    <a href="dashboard.php" class="nav-link active">
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
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Patients</h3>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_patients; ?></div>
                    <div class="card-change">+12% from last month</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Doctors</h3>
                        <div class="card-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_doctors; ?></div>
                    <div class="card-change">+2 new this month</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Appointments</h3>
                        <div class="card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $today_appointments; ?></div>
                    <div class="card-change">Scheduled for today</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Bills</h3>
                        <div class="card-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $pending_bills; ?></div>
                    <div class="card-change">Require attention</div>
                </div>
            </div>
            
            <!-- Recent Appointments Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Recent Appointments</h3>
                    <a href="appointments.php" class="btn-primary">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_appointments as $appointment): ?>
                        <tr>
                            <td><?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></td>
                            <td>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?></td>
                            <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                            <td><?php echo formatTime($appointment['appointment_time']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                    <?php echo $appointment['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-view">View</a>
                                    <a href="edit_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>