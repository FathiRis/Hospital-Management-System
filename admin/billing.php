<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Handle search and filters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.first_name LIKE :search OR p.last_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "b.status = :status";
    $params[':status'] = $status_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get all billing records
$query = "SELECT b.*, p.first_name as patient_name, p.last_name as patient_last
          FROM billing b 
          JOIN patients pt ON b.patient_id = pt.patient_id 
          JOIN users p ON pt.user_id = p.user_id 
          $where_clause 
          ORDER BY b.billing_date DESC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$bills = $db->resultset();

// Calculate totals
$total_amount = 0;
$paid_amount = 0;
$unpaid_amount = 0;

foreach ($bills as $bill) {
    $total_amount += $bill['amount'];
    if ($bill['status'] === 'Paid') {
        $paid_amount += $bill['amount'];
    } else {
        $unpaid_amount += $bill['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - Hospital Management System</title>
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
                    <a href="billing.php" class="nav-link active">
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
                <h1>Billing Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Billing Summary -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Total Revenue</h3>
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">Rs. <?php echo number_format($total_amount, 2); ?></div>
                    <div class="card-change">All time</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Paid Amount</h3>
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="card-value">Rs. <?php echo number_format($paid_amount, 2); ?></div>
                    <div class="card-change">Collected</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Outstanding</h3>
                        <div class="card-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="card-value">Rs. <?php echo number_format($unpaid_amount, 2); ?></div>
                    <div class="card-change">Pending payment</div>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Search & Filter Bills</h3>
                </div>
                
                <form method="GET" class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by patient name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Paid" <?php echo $status_filter === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Unpaid" <?php echo $status_filter === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                            <option value="Partial" <?php echo $status_filter === 'Partial' ? 'selected' : ''; ?>>Partial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Filter</button>
                        <a href="add_bill.php" class="btn-primary" style="margin-left: 10px;">Add Bill</a>
                    </div>
                </form>
            </div>
            
            <!-- Bills Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">All Bills (<?php echo count($bills); ?>)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill ID</th>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Billing Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                No bills found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td>#<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $bill['patient_name'] . ' ' . $bill['patient_last']; ?></td>
                                <td>Rs. <?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo formatDate($bill['billing_date']); ?></td>
                                <td><?php echo formatDate($bill['due_date']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($bill['status']); ?>">
                                        <?php echo $bill['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $bill['payment_method'] ?: 'N/A'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_bill.php?id=<?php echo $bill['bill_id']; ?>" class="btn-sm btn-view">View</a>
                                        <a href="edit_bill.php?id=<?php echo $bill['bill_id']; ?>" class="btn-sm btn-edit">Edit</a>
                                        <?php if ($bill['status'] !== 'Paid'): ?>
                                        <a href="mark_paid.php?id=<?php echo $bill['bill_id']; ?>" class="btn-sm btn-success">Mark Paid</a>
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