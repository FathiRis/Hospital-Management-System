<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Handle search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR d.specialization LIKE :search";
    $params[':search'] = "%$search%";
}

// Get all doctors
$query = "SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.created_at 
          FROM doctors d 
          JOIN users u ON d.user_id = u.user_id 
          $where_clause 
          ORDER BY u.created_at DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$doctors = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors - Hospital Management System</title>
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
                <h1>Doctor Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Search and Add Doctor -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search Doctors</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or specialization..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Search</button>
                        <a href="add_doctor.php" class="btn-primary" style="margin-left: 10px;">Add New Doctor</a>
                    </div>
                </form>
            </div>
            
            <!-- Doctors Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Doctors (<?php echo count($doctors); ?>)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Doctor ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Specialization</th>
                            <th>Department</th>
                            <th>Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($doctors)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <?php echo empty($search) ? 'No doctors found' : 'No doctors match your search criteria'; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td>#<?php echo str_pad($doctor['doctor_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></td>
                                <td><?php echo $doctor['email']; ?></td>
                                <td><?php echo $doctor['phone']; ?></td>
                                <td><?php echo $doctor['specialization']; ?></td>
                                <td><?php echo $doctor['department']; ?></td>
                                <td>$<?php echo number_format($doctor['consultation_fee'], 2); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_doctor.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn-sm btn-view">View</a>
                                        <a href="edit_doctor.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="doctor_schedule.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn-sm btn-primary">Schedule</a>
                                    </div>
                                </td>
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