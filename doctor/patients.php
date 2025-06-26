<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('doctor');

$db = new Database();

// Get doctor information
$doctor_id = getDoctorId(getUserId());

// Handle search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$where_clause = '';
$params = [':doctor_id' => $doctor_id];

if (!empty($search)) {
    $where_clause = "AND (u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Get doctor's patients (patients who have had appointments with this doctor)
$query = "SELECT DISTINCT p.*, u.first_name, u.last_name, u.email, u.phone, u.created_at,
                 COUNT(a.appointment_id) as total_appointments,
                 MAX(a.appointment_date) as last_visit
          FROM patients p 
          JOIN users u ON p.user_id = u.user_id 
          JOIN appointments a ON p.patient_id = a.patient_id
          WHERE a.doctor_id = :doctor_id $where_clause
          GROUP BY p.patient_id
          ORDER BY last_visit DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$patients = $db->resultset();

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
    <title>My Patients - Hospital Management System</title>
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
                    <a href="patients.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        My Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a href="prescriptions.php" class="nav-link">
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
                    <h1>My Patients</h1>
                    <p>Manage your patient records and history</p>
                </div>
                <div class="header-right">
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Search Patients -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search Patients</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="patients.php" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Patients List -->
            <div class="patient-records">
                <div class="records-header">
                    <h3 class="records-title">My Patients (<?php echo count($patients); ?>)</h3>
                </div>
                
                <div class="records-list">
                    <?php if (empty($patients)): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date">No patients found</div>
                                <div class="record-doctor"><?php echo empty($search) ? 'You haven\'t seen any patients yet.' : 'No patients match your search criteria.'; ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                        <div class="record-item">
                            <div>
                                <div class="record-date"><?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?></div>
                                <div class="record-doctor">
                                    <i class="fas fa-envelope"></i> <?php echo $patient['email']; ?> | 
                                    <i class="fas fa-phone"></i> <?php echo $patient['phone'] ?: 'N/A'; ?>
                                </div>
                                <div class="record-diagnosis">
                                    <i class="fas fa-calendar"></i> Last visit: <?php echo formatDate($patient['last_visit']); ?> | 
                                    <i class="fas fa-history"></i> Total appointments: <?php echo $patient['total_appointments']; ?> |
                                    <i class="fas fa-birthday-cake"></i> Age: <?php echo calculateAge($patient['dob']); ?> years |
                                    <i class="fas fa-tint"></i> Blood Type: <?php echo $patient['blood_type'] ?: 'N/A'; ?>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <a href="patient_history.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-secondary">Medical History</a>
                                <a href="add_prescription.php?patient_id=<?php echo $patient['patient_id']; ?>" class="btn btn-sm btn-success">New Prescription</a>
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