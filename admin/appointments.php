<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$date_filter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.first_name LIKE :search OR p.last_name LIKE :search OR d.first_name LIKE :search OR d.last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(a.appointment_date) = :date";
    $params[':date'] = $date_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get all appointments
$query = "SELECT a.*, p.first_name as patient_name, p.last_name as patient_last, 
                 d.first_name as doctor_name, d.last_name as doctor_last, dt.specialization
          FROM appointments a 
          JOIN patients pt ON a.patient_id = pt.patient_id 
          JOIN users p ON pt.user_id = p.user_id 
          JOIN doctors dt ON a.doctor_id = dt.doctor_id 
          JOIN users d ON dt.user_id = d.user_id 
          $where_clause 
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$appointments = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Hospital Management System</title>
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
                    <a href="doctors.php" class="nav-link">
                        <i class="fas fa-user-md"></i>
                        Doctors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link active">
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
                <h1>Appointment Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search & Filter Appointments</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by patient or doctor name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Scheduled" <?php echo $status_filter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="No-Show" <?php echo $status_filter === 'No-Show' ? 'selected' : ''; ?>>No-Show</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Filter</button>
                        <a href="add_appointment.php" class="btn-primary" style="margin-left: 10px;">Add Appointment</a>
                    </div>
                </form>
            </div>
            
            <!-- Appointments Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Appointments (<?php echo count($appointments); ?>)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                No appointments found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>#<?php echo str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $appointment['patient_name'] . ' ' . $appointment['patient_last']; ?></td>
                                <td>Dr. <?php echo $appointment['doctor_name'] . ' ' . $appointment['doctor_last']; ?><br>
                                    <small><?php echo $appointment['specialization']; ?></small>
                                </td>
                                <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                <td><?php echo formatTime($appointment['appointment_time']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $appointment['reason'] ?: 'General consultation'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-view">View</a>
                                        <a href="edit_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <?php if ($appointment['status'] === 'Scheduled'): ?>
                                        <a href="cancel_appointment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                                        <?php endif; ?>
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