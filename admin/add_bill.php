<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Get all patients
$db->query("SELECT p.patient_id, u.first_name, u.last_name FROM patients p JOIN users u ON p.user_id = u.user_id ORDER BY u.first_name");
$patients = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = sanitizeInput($_POST['patient_id']);
    $description = sanitizeInput($_POST['description']);
    $amount = sanitizeInput($_POST['amount']);
    $tax_amount = sanitizeInput($_POST['tax_amount']) ?: 0;
    $total_amount = $amount + $tax_amount;
    $billing_date = sanitizeInput($_POST['billing_date']);
    $due_date = sanitizeInput($_POST['due_date']);
    $status = sanitizeInput($_POST['status']);
    
    // Validation
    if (empty($patient_id) || empty($description) || empty($amount) || empty($billing_date) || empty($due_date)) {
        $error_message = "All required fields must be filled.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error_message = "Please enter a valid amount.";
    } else {
        try {
            // Insert bill
            $db->query("INSERT INTO billing (patient_id, description, amount, tax_amount, total_amount, billing_date, due_date, status) VALUES (:patient_id, :description, :amount, :tax_amount, :total_amount, :billing_date, :due_date, :status)");
            $db->bind(':patient_id', $patient_id);
            $db->bind(':description', $description);
            $db->bind(':amount', $amount);
            $db->bind(':tax_amount', $tax_amount);
            $db->bind(':total_amount', $total_amount);
            $db->bind(':billing_date', $billing_date);
            $db->bind(':due_date', $due_date);
            $db->bind(':status', $status);
            $db->execute();
            
            logActivity(getUserId(), 'Add Bill', "Created bill for patient ID: $patient_id");
            
            header('Location: billing.php?success=Bill created successfully');
            exit();
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bill - Hospital Management System</title>
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
                <h1>Create New Bill</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Bill Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Bill Details</h3>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="patient_id" class="form-label">Patient *</label>
                        <select id="patient_id" name="patient_id" class="form-control" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['patient_id']; ?>">
                                    <?php echo $patient['first_name'] . ' ' . $patient['last_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe the services provided" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount *</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="tax_amount" class="form-label">Tax Amount</label>
                            <input type="number" id="tax_amount" name="tax_amount" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="billing_date" class="form-label">Billing Date *</label>
                            <input type="date" id="billing_date" name="billing_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="due_date" class="form-label">Due Date *</label>
                            <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <?php foreach (getBillingStatuses() as $status): ?>
                                <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Create Bill</button>
                        <a href="billing.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate total amount
        document.getElementById('amount').addEventListener('input', calculateTotal);
        document.getElementById('tax_amount').addEventListener('input', calculateTotal);
        
        function calculateTotal() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
            const total = amount + taxAmount;
            
            // You can display the total somewhere if needed
            console.log('Total Amount: $' + total.toFixed(2));
        }
    </script>
</body>
</html>