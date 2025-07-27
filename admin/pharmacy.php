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
    $where_clause = "WHERE name LIKE :search OR supplier LIKE :search";
    $params[':search'] = "%$search%";
}

// Get all medicines
$query = "SELECT * FROM pharmacy $where_clause ORDER BY name";
$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$medicines = $db->resultset();

// Calculate inventory stats
$total_medicines = count($medicines);
$low_stock = 0;
$expired = 0;
$total_value = 0;

foreach ($medicines as $medicine) {
    $total_value += $medicine['quantity'] * $medicine['unit_price'];
    if ($medicine['quantity'] < 50) {
        $low_stock++;
    }
    if ($medicine['expiry_date'] && strtotime($medicine['expiry_date']) < time()) {
        $expired++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Hospital Management System</title>
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
                <h1>Pharmacy Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Pharmacy Stats -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Medicines</h3>
                        <div class="card-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $total_medicines; ?></div>
                    <div class="card-change">In inventory</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Low Stock</h3>
                        <div class="card-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $low_stock; ?></div>
                    <div class="card-change">Below 50 units</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Expired</h3>
                        <div class="card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $expired; ?></div>
                    <div class="card-change">Need removal</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Value</h3>
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">Rs. <?php echo number_format($total_value, 2); ?></div>
                    <div class="card-change">Inventory worth</div>
                </div>
            </div>
            
            <!-- Search and Add Medicine -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search Medicines</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by medicine name or supplier..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Search</button>
                        <a href="add_medicine.php" class="btn-primary" style="margin-left: 10px;">Add Medicine</a>
                    </div>
                </form>
            </div>
            
            <!-- Medicines Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Medicine Inventory (<?php echo count($medicines); ?>)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Expiry Date</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medicines)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <?php echo empty($search) ? 'No medicines found' : 'No medicines match your search criteria'; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($medicines as $medicine): ?>
                            <?php 
                            $is_low_stock = $medicine['quantity'] < 50;
                            $is_expired = $medicine['expiry_date'] && strtotime($medicine['expiry_date']) < time();
                            $row_class = '';
                            if ($is_expired) $row_class = 'style="background-color: #ffebee;"';
                            elseif ($is_low_stock) $row_class = 'style="background-color: #fff3e0;"';
                            ?>
                            <tr <?php echo $row_class; ?>>
                                <td>#<?php echo str_pad($medicine['medicine_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $medicine['name']; ?></td>
                                <td><?php echo $medicine['description'] ?: 'N/A'; ?></td>
                                <td>
                                    <?php echo $medicine['quantity']; ?>
                                    <?php if ($is_low_stock): ?>
                                        <span class="status-badge" style="background: #ff9800; color: white; font-size: 10px;">LOW</span>
                                    <?php endif; ?>
                                </td>
                                <td>Rs. <?php echo number_format($medicine['unit_price'], 2); ?></td>
                                <td>
                                    <?php echo $medicine['expiry_date'] ? formatDate($medicine['expiry_date']) : 'N/A'; ?>
                                    <?php if ($is_expired): ?>
                                        <span class="status-badge" style="background: #f44336; color: white; font-size: 10px;">EXPIRED</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $medicine['supplier'] ?: 'N/A'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn-sm btn-view">View</a>
                                        <a href="edit_medicine.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="update_stock.php?id=<?php echo $medicine['medicine_id']; ?>" class="btn-sm btn-primary">Stock</a>
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