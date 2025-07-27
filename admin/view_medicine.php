<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pharmacy.php');
    exit();
}

$medicine_id = $_GET['id'];

// Get medicine details
$db->query("SELECT * FROM pharmacy WHERE medicine_id = :medicine_id");
$db->bind(':medicine_id', $medicine_id);
$medicine = $db->single();

if (!$medicine) {
    header('Location: pharmacy.php');
    exit();
}

// Check if medicine is low stock or expired
$is_low_stock = $medicine['quantity'] < 50;
$is_expired = $medicine['expiry_date'] && strtotime($medicine['expiry_date']) < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medicine - Hospital Management System</title>
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
                    <a href="pharmacy.php" class="nav-link active">
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
                <h1>Medicine Details</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Medicine Information -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title"><?php echo $medicine['name']; ?></h3>
                    <div>
                        <a href="edit_medicine.php?id=<?php echo $medicine_id; ?>" class="btn-primary">Edit Medicine</a>
                        <a href="pharmacy.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Back to List</a>
                    </div>
                </div>
                
                <?php if ($is_low_stock || $is_expired): ?>
                <div class="alert" style="background: <?php echo $is_expired ? '#ffebee' : '#fff3e0'; ?>; color: <?php echo $is_expired ? '#c62828' : '#f57c00'; ?>; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php if ($is_expired): ?>
                        <i class="fas fa-exclamation-triangle"></i> This medicine has expired!
                    <?php elseif ($is_low_stock): ?>
                        <i class="fas fa-exclamation-triangle"></i> Low stock alert! Only <?php echo $medicine['quantity']; ?> units remaining.
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <p><strong>Name:</strong> <?php echo $medicine['name']; ?></p>
                        <p><strong>Generic Name:</strong> <?php echo $medicine['generic_name'] ?: 'N/A'; ?></p>
                        <p><strong>Brand Name:</strong> <?php echo $medicine['brand_name'] ?: 'N/A'; ?></p>
                        <p><strong>Category:</strong> <?php echo $medicine['category'] ?: 'N/A'; ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Dosage Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-prescription-bottle"></i>
                            </div>
                        </div>
                        <p><strong>Dosage Form:</strong> <?php echo $medicine['dosage_form'] ?: 'N/A'; ?></p>
                        <p><strong>Strength:</strong> <?php echo $medicine['strength'] ?: 'N/A'; ?></p>
                        <p><strong>Quantity:</strong> <?php echo $medicine['quantity']; ?> units</p>
                        <?php if ($is_low_stock): ?>
                        <p style="color: #f57c00;"><strong>Status:</strong> Low Stock</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Pricing</h3>
                            <div class="card-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <p><strong>Unit Price:</strong> Rs. <?php echo number_format($medicine['unit_price'], 2); ?></p>
                        <p><strong>Wholesale Price:</strong> Rs. <?php echo number_format($medicine['wholesale_price'], 2); ?></p>
                        <p><strong>Total Value:</strong> Rs. <?php echo number_format($medicine['quantity'] * $medicine['unit_price'], 2); ?></p>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Supply Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                        <p><strong>Supplier:</strong> <?php echo $medicine['supplier'] ?: 'N/A'; ?></p>
                        <p><strong>Manufacturer:</strong> <?php echo $medicine['manufacturer'] ?: 'N/A'; ?></p>
                        <p><strong>Batch Number:</strong> <?php echo $medicine['batch_number'] ?: 'N/A'; ?></p>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Expiry Information</h3>
                            <div class="card-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                        </div>
                        <p><strong>Expiry Date:</strong> <?php echo $medicine['expiry_date'] ? formatDate($medicine['expiry_date']) : 'N/A'; ?></p>
                        <?php if ($medicine['expiry_date']): ?>
                        <p><strong>Days Until Expiry:</strong> 
                            <?php 
                            $days_until_expiry = floor((strtotime($medicine['expiry_date']) - time()) / (60 * 60 * 24));
                            if ($days_until_expiry < 0) {
                                echo '<span style="color: #c62828;">Expired ' . abs($days_until_expiry) . ' days ago</span>';
                            } elseif ($days_until_expiry < 30) {
                                echo '<span style="color: #f57c00;">' . $days_until_expiry . ' days</span>';
                            } else {
                                echo $days_until_expiry . ' days';
                            }
                            ?>
                        </p>
                        <?php endif; ?>
                        <p><strong>Added:</strong> <?php echo formatDate($medicine['created_at']); ?></p>
                    </div>
                </div>
                
                <?php if ($medicine['description']): ?>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($medicine['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($medicine['storage_conditions']): ?>
                <div class="form-group">
                    <label class="form-label">Storage Conditions</label>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <?php echo nl2br(htmlspecialchars($medicine['storage_conditions'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>