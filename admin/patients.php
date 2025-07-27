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
    $where_clause = "WHERE u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search";
    $params[':search'] = "%$search%";
}

// Get all patients
$query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.created_at 
          FROM patients p 
          JOIN users u ON p.user_id = u.user_id 
          $where_clause 
          ORDER BY u.created_at DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$patients = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Hospital Management System</title>
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
                <h1>Patient Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Search and Add Patient -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search Patients</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Search</button>
                        <a href="add_patient.php" class="btn-primary" style="margin-left: 10px;">Add New Patient</a>
                    </div>
                </form>
            </div>
            
            <!-- Patients Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Patients (<?php echo count($patients); ?>)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Age</th>
                            <th>Blood Type</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <?php echo empty($search) ? 'No patients found' : 'No patients match your search criteria'; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td>#<?php echo str_pad($patient['patient_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></td>
                                <td><?php echo $patient['email']; ?></td>
                                <td><?php echo $patient['phone']; ?></td>
                                <td><?php echo calculateAge($patient['dob']); ?> years</td>
                                <td><?php echo $patient['blood_type'] ?: 'N/A'; ?></td>
                                <td><?php echo formatDate($patient['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" class="btn-sm btn-view">View</a>
                                        <a href="edit_patient.php?id=<?php echo $patient['patient_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="patient_history.php?id=<?php echo $patient['patient_id']; ?>" class="btn-sm btn-primary">History</a>
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