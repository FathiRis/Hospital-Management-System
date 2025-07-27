<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // First get user_id for this doctor
        $db->query("SELECT user_id FROM doctors WHERE doctor_id = :doctor_id");
        $db->bind(':doctor_id', $doctor_id);
        $user_id = $db->single()['user_id'];
        
        // Delete from doctors table
        $db->query("DELETE FROM doctors WHERE doctor_id = :doctor_id");
        $db->bind(':doctor_id', $doctor_id);
        $db->execute();
        
        // Delete from users table
        $db->query("DELETE FROM users WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $db->execute();
        
        // Commit transaction
        $db->commit();
        
        $_SESSION['success_message'] = 'Doctor deleted successfully';
        header('Location: doctors.php');
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error_message'] = 'Error deleting doctor: ' . $e->getMessage();
        header("Location: view_doctor.php?id=$doctor_id");
        exit;
    }
} else {
    // Show confirmation page
    $db = new Database();
    $db->query("SELECT d.*, u.first_name, u.last_name 
                FROM doctors d 
                JOIN users u ON d.user_id = u.user_id 
                WHERE d.doctor_id = :doctor_id");
    $db->bind(':doctor_id', $doctor_id);
    $doctor = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Doctor - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 30px;
            text-align: center;
        }
        
        .warning-icon {
            font-size: 60px;
            color: var(--danger-color);
            margin-bottom: 20px;
        }
        
        .confirmation-message {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .doctor-info {
            background: var(--bg-very-light);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .confirmation-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .confirmation-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
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
                <h1>Delete Doctor</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="confirmation-container">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2>Confirm Deletion</h2>
                
                <div class="confirmation-message">
                    <p>Are you sure you want to permanently delete this doctor record?</p>
                    <p>This action cannot be undone and will remove all associated data.</p>
                </div>
                
                <div class="doctor-info">
                    <h4>Doctor Information</h4>
                    <p><strong>Name:</strong> Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></p>
                    <p><strong>Specialization:</strong> <?php echo $doctor['specialization']; ?></p>
                    <p><strong>Department:</strong> <?php echo $doctor['department']; ?></p>
                </div>
                
                <form method="POST" class="confirmation-actions">
                    <a href="view_doctor.php?id=<?php echo $doctor_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php } ?>