<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitizeInput($_POST['first_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        
        $db->query("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address WHERE user_id = :user_id");
        $db->bind(':first_name', $first_name);
        $db->bind(':last_name', $last_name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':address', $address);
        $db->bind(':user_id', getUserId());
        
        if ($db->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Failed to update profile.";
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $db->query("SELECT password FROM users WHERE user_id = :user_id");
        $db->bind(':user_id', getUserId());
        $user = $db->single();
        
        if ($current_password === $user['password']) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $db->query("UPDATE users SET password = :password WHERE user_id = :user_id");
                    $db->bind(':password', $new_password);
                    $db->bind(':user_id', getUserId());
                    
                    if ($db->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Failed to change password.";
                    }
                } else {
                    $error_message = "New password must be at least 6 characters long.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Get current user info
$db->query("SELECT * FROM users WHERE user_id = :user_id");
$db->bind(':user_id', getUserId());
$user_info = $db->single();

// Get system statistics
$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient'");
$total_patients = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'doctor'");
$total_doctors = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM appointments");
$total_appointments = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM activity_logs WHERE DATE(created_at) = CURDATE()");
$today_activities = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Hospital Management System</title>
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
                    <a href="settings.php" class="nav-link active">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>System Settings</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- System Overview -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Patients</h3>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_patients; ?></div>
                    <div class="card-change">Registered users</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Doctors</h3>
                        <div class="card-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_doctors; ?></div>
                    <div class="card-change">Active doctors</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Appointments</h3>
                        <div class="card-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_appointments; ?></div>
                    <div class="card-change">All time</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Activity</h3>
                        <div class="card-icon">
                            <i class="fas fa-activity"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $today_activities; ?></div>
                    <div class="card-change">System activities</div>
                </div>
            </div>
            
            <!-- Profile Settings -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Profile Settings</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_info['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_info['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_info['phone']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user_info['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Password Change -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Change Password</h3>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                </form>
            </div>
            
            <!-- System Actions -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">System Actions</h3>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card" onclick="backupDatabase()" style="cursor: pointer;">
                        <div class="card-header">
                            <h3 class="card-title">Backup Database</h3>
                            <div class="card-icon">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                        <p>Create a backup of the system database</p>
                    </div>
                    
                    <div class="dashboard-card" onclick="clearLogs()" style="cursor: pointer;">
                        <div class="card-header">
                            <h3 class="card-title">Clear Activity Logs</h3>
                            <div class="card-icon">
                                <i class="fas fa-trash"></i>
                            </div>
                        </div>
                        <p>Remove old system activity logs</p>
                    </div>
                    
                    <div class="dashboard-card" onclick="systemMaintenance()" style="cursor: pointer;">
                        <div class="card-header">
                            <h3 class="card-title">System Maintenance</h3>
                            <div class="card-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <p>Run system optimization and cleanup</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function backupDatabase() {
            if (confirm('Are you sure you want to create a database backup?')) {
                alert('Database backup functionality would be implemented here.');
                // In a real application, this would trigger a backup script
            }
        }
        
        function clearLogs() {
            if (confirm('Are you sure you want to clear activity logs? This action cannot be undone.')) {
                alert('Clear logs functionality would be implemented here.');
                // In a real application, this would clear old logs
            }
        }
        
        function systemMaintenance() {
            if (confirm('Are you sure you want to run system maintenance? This may take a few minutes.')) {
                alert('System maintenance functionality would be implemented here.');
                // In a real application, this would run maintenance tasks
            }
        }
    </script>
</body>
</html>