<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = new Database();

// Get doctor details
$db->query("SELECT d.*, u.first_name, u.last_name, u.email, u.phone 
            FROM doctors d 
            JOIN users u ON d.user_id = u.user_id 
            WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor = $db->single();

// Initialize variables
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $specialization = sanitizeInput($_POST['specialization']);
    $department = sanitizeInput($_POST['department']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    $license_number = sanitizeInput($_POST['license_number'] ?? '');

    // Validate inputs
    if (empty($first_name)) {
        $errors['first_name'] = 'First name is required';
    }
    if (empty($last_name)) {
        $errors['last_name'] = 'Last name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    if (empty($specialization)) {
        $errors['specialization'] = 'Specialization is required';
    }
    if (empty($department)) {
        $errors['department'] = 'Department is required';
    }
    if ($consultation_fee <= 0) {
        $errors['consultation_fee'] = 'Consultation fee must be positive';
    }

    // Check if email already exists for another user
    $db->query("SELECT user_id FROM users WHERE email = :email AND user_id != :user_id");
    $db->bind(':email', $email);
    $db->bind(':user_id', $doctor['user_id']);
    $existing_user = $db->single();
    
    if ($existing_user) {
        $errors['email'] = 'Email already exists for another user';
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Update users table
            $db->query("UPDATE users SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        phone = :phone,
                        updated_at = NOW()
                        WHERE user_id = :user_id");
            
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':user_id', $doctor['user_id']);
            $db->execute();

            // Update doctors table
            $db->query("UPDATE doctors SET 
                        specialization = :specialization,
                        department = :department,
                        consultation_fee = :consultation_fee,
                        license_number = :license_number,
                        updated_at = NOW()
                        WHERE doctor_id = :doctor_id");
            
            $db->bind(':specialization', $specialization);
            $db->bind(':department', $department);
            $db->bind(':consultation_fee', $consultation_fee);
            $db->bind(':license_number', $license_number);
            $db->bind(':doctor_id', $doctor_id);
            $db->execute();

            $db->commit();

            $_SESSION['success_message'] = 'Doctor updated successfully';
            header("Location: view_doctor.php?id=$doctor_id");
            exit;
        } catch (PDOException $e) {
            $db->rollBack();
            $errors['database'] = 'Error updating doctor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-form-container {
            margin-top: 30px;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .form-title {
            color: var(--primary-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
        }
        
        .profile-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .profile-image {
            text-align: center;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 60px;
            margin: 0 auto 20px;
            border: 5px solid rgba(255,255,255,0.2);
        }
        
        .error-message {
            color: var(--danger-color);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .form-control.error {
            border-color: var(--danger-color);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .profile-section {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
                <h1>Edit Doctor</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="edit-form-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Error!</strong> Please correct the following issues:
                        <ul style="margin-top: 10px; margin-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="form-header">
                    <h2 class="form-title">Edit Doctor Profile</h2>
                    <div class="form-actions">
                        
                        <button type="submit" form="editDoctorForm" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="view_doctor.php?id=<?php echo $doctor_id; ?>" class="btn-primary" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <!-- <a href="view_doctor.php?id=<?php echo $doctor_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a> -->

                    </div>
                </div>
                
                <form id="editDoctorForm" method="POST">
                    <div class="profile-section">
                        <div class="profile-image">
                            <div class="profile-avatar">
                                <?php echo strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)); ?>
                            </div>
                            <p>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></p>
                            <p>ID: #<?php echo str_pad($doctor['doctor_id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        
                        <div class="form-fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'error' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? $doctor['first_name']); ?>" required>
                                    <?php if (isset($errors['first_name'])): ?>
                                        <span class="error-message"><?php echo $errors['first_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'error' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? $doctor['last_name']); ?>" required>
                                    <?php if (isset($errors['last_name'])): ?>
                                        <span class="error-message"><?php echo $errors['last_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? $doctor['email']); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="error-message"><?php echo $errors['email']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? $doctor['phone']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-container">
                        <h3>Professional Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" class="form-control <?php echo isset($errors['specialization']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['specialization'] ?? $doctor['specialization']); ?>" required>
                                <?php if (isset($errors['specialization'])): ?>
                                    <span class="error-message"><?php echo $errors['specialization']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control <?php echo isset($errors['department']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['department'] ?? $doctor['department']); ?>" required>
                                <?php if (isset($errors['department'])): ?>
                                    <span class="error-message"><?php echo $errors['department']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Consultation Fee ($)</label>
                                <input type="number" step="0.01" name="consultation_fee" class="form-control <?php echo isset($errors['consultation_fee']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($_POST['consultation_fee'] ?? $doctor['consultation_fee']); ?>" required>
                                <?php if (isset($errors['consultation_fee'])): ?>
                                    <span class="error-message"><?php echo $errors['consultation_fee']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['license_number'] ?? $doctor['license_number'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>