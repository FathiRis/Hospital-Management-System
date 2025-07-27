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
    $amount = sanitizeInput($_POST['amount']);
    $status = sanitizeInput($_POST['status']);
    $billing_date = sanitizeInput($_POST['billing_date']);
    $due_date = sanitizeInput($_POST['due_date']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    
    // Validate inputs
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $_SESSION['error'] = 'Please enter a valid amount';
    } else {
        try {
            $db->query("UPDATE billing 
                        SET amount = :amount,
                            status = :status,
                            billing_date = :billing_date,
                            due_date = :due_date,
                            payment_method = :payment_method
                        WHERE bill_id = :bill_id");
            
            $db->bind(':amount', $amount);
            $db->bind(':status', $status);
            $db->bind(':billing_date', $billing_date);
            $db->bind(':due_date', $due_date);
            $db->bind(':payment_method', $payment_method);
            $db->bind(':bill_id', $bill_id);
            
            if ($db->execute()) {
                $_SESSION['success'] = 'Bill updated successfully';
                header("Location: view_bill.php?id=$bill_id");
                exit();
            } else {
                $_SESSION['error'] = 'Failed to update bill';
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
    <title>Edit Bill - Hospital Management System</title>
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
                <h1>Edit Bill #<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></h1>
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
            
            <!-- Edit Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Edit Bill Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Patient</label>
                            <input type="text" class="form-control" value="<?php echo $bill['first_name'] . ' ' . $bill['last_name']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Amount ($)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($bill['amount']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Billing Date</label>
                            <input type="date" name="billing_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($bill['billing_date']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($bill['due_date']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Paid" <?php echo $bill['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="Unpaid" <?php echo $bill['status'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="Partial" <?php echo $bill['status'] === 'Partial' ? 'selected' : ''; ?>>Partial</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Select Method</option>
                                <option value="Cash" <?php echo $bill['payment_method'] === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="Credit Card" <?php echo $bill['payment_method'] === 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                                <option value="Debit Card" <?php echo $bill['payment_method'] === 'Debit Card' ? 'selected' : ''; ?>>Debit Card</option>
                                <option value="Bank Transfer" <?php echo $bill['payment_method'] === 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="Insurance" <?php echo $bill['payment_method'] === 'Insurance' ? 'selected' : ''; ?>>Insurance</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 15px;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
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