<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

// Get doctor information
$doctor_id = getDoctorId(getUserId());

// Handle status filter
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$where_conditions = ['pr.doctor_id = :doctor_id'];
$params = [':doctor_id' => $doctor_id];

if (!empty($status_filter)) {
    $where_conditions[] = "pr.status = :status";
    $params[':status'] = $status_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get doctor's prescriptions
$query = "SELECT pr.*, p.first_name as patient_name, p.last_name as patient_last, pt.dob,
                 COUNT(pi.item_id) as total_items
          FROM prescriptions pr 
          JOIN patients pt ON pr.patient_id = pt.patient_id 
          JOIN users p ON pt.user_id = p.user_id 
          LEFT JOIN prescription_items pi ON pr.prescription_id = pi.prescription_id
          $where_clause 
          GROUP BY pr.prescription_id
          ORDER BY pr.prescription_date DESC, pr.created_at DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$prescriptions = $db->resultset();

// Get doctor info for display
$db->query("SELECT d.*, u.first_name, u.last_name FROM doctors d JOIN users u ON d.user_id = u.user_id WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Hospital Management System</title>
    <link rel="stylesheet" href="../css/doctor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="doctor-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="doctor-avatar">
                    <?php echo strtoupper(substr($doctor_info['first_name'], 0, 1)); ?>
                </div>
                <h2>Dr. <?php echo $doctor_info['first_name'] . ' ' . $doctor_info['last_name']; ?></h2>
                <p><?php echo $doctor_info['specialization']; ?></p>
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
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        My Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="prescriptions.php" class="nav-link active">
                        <i class="fas fa-prescription-bottle"></i>
                        Prescriptions
                    </a>
                </li>
                <li class="nav-item">
                    <a href="schedule.php" class="nav-link">
                        <i class="fas fa-clock"></i>
                        Schedule
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
                    <h1>Prescriptions</h1>
                    <p>Manage patient prescriptions</p>
                </div>
                <div class="header-right">
                    <a href="add_prescription.php" class="btn btn-primary">New Prescription</a>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Filter Prescriptions -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Filter Prescriptions</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <?php foreach (getPrescriptionStatuses() as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="prescriptions.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Prescriptions List -->
            <div class="patient-records">
                <div class="records-header">
                    <h3 class="records-title">My Prescriptions (<?php echo count($prescriptions); ?>)</h3>
                </div>
                
                <div class="records-list">
                    <?php if (empty($prescriptions)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No prescriptions found</div>
                                <div class="record-doctor">You haven't written any prescriptions yet.</div>
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
                                    <i class="fas fa-user"></i> <?php echo $prescription['patient_name'] . ' ' . $prescription['patient_last']; ?> |
                                    <i class="fas fa-birthday-cake"></i> Age: <?php echo calculateAge($prescription['dob']); ?> years
                                </div>
                                <div class="record-diagnosis">
                                    <i class="fas fa-calendar"></i> Date: <?php echo formatDate($prescription['prescription_date']); ?> |
                                    <i class="fas fa-pills"></i> Items: <?php echo $prescription['total_items']; ?> |
                                    <i class="fas fa-dollar-sign"></i> Total: Rs. <?php echo number_format($prescription['total_amount'], 2); ?>
                                    <?php if ($prescription['instructions']): ?>
                                    <br><i class="fas fa-notes-medical"></i> <?php echo $prescription['instructions']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <a href="view_prescription.php?id=<?php echo $prescription['prescription_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <?php if ($prescription['status'] === 'Pending'): ?>
                                <a href="edit_prescription.php?id=<?php echo $prescription['prescription_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                                <a href="print_prescription.php?id=<?php echo $prescription['prescription_id']; ?>" class="btn btn-sm btn-secondary" target="_blank">Print</a>
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