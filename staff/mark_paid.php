<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('staff');

$db = new Database();

// Check if bill ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No bill selected';
    header('Location: billing.php');
    exit();
}

$bill_id = sanitizeInput($_GET['id']);

// Get bill details
$db->query("SELECT b.*, p.first_name, p.last_name 
            FROM billing b 
            JOIN patients pt ON b.patient_id = pt.patient_id 
            JOIN users p ON pt.user_id = p.user_id 
            WHERE b.bill_id = :bill_id");
$db->bind(':bill_id', $bill_id);
$bill = $db->single();

if (!$bill) {
    $_SESSION['error'] = 'Bill not found';
    header('Location: billing.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitizeInput($_POST['payment_method']);
    $payment_date = sanitizeInput($_POST['payment_date']);
    
    // Validate inputs
    if (empty($payment_method)) {
        $_SESSION['error'] = 'Payment method is required';
    } else {
        try {
            $db->query("UPDATE billing 
                        SET status = 'Paid', 
                            payment_method = :payment_method, 
                            payment_date = :payment_date
                        WHERE bill_id = :bill_id");
            
            $db->bind(':payment_method', $payment_method);
            $db->bind(':payment_date', $payment_date);
            $db->bind(':bill_id', $bill_id);
            
            if ($db->execute()) {
                $_SESSION['success'] = 'Bill marked as paid successfully';
                header("Location: view_bill.php?id=$bill_id");
                exit();
            } else {
                $_SESSION['error'] = 'Failed to update bill status';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Bill as Paid - Hospital Management System</title>
    <link rel="stylesheet" href="../css/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>MediCare Staff</h2>
                <p>Hospital Management</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        Appointments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="patients.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Patients
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
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Mark Bill as Paid</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Display messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message-popup error">
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    <button onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Bill Summary -->
            <div class="dashboard-card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h3 class="card-title">Bill Summary</h3>
                    <div class="card-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="card-value">Rs. <?php echo number_format($bill['amount'], 2); ?></div>
                <div class="card-change">
                    <span class="status-badge status-<?php echo strtolower($bill['status']); ?>">
                        <?php echo $bill['status']; ?>
                    </span>
                    for <?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Payment Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Check">Check</option>
                                <option value="Insurance">Insurance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 15px;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check-circle"></i> Confirm Payment
                        </button>
                        <a href="view_bill.php?id=<?php echo $bill_id; ?>" class="btn-primary" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message-popup');
            messages.forEach(msg => {
                msg.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>