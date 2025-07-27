<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $generic_name = sanitizeInput($_POST['generic_name']);
    $brand_name = sanitizeInput($_POST['brand_name']);
    $description = sanitizeInput($_POST['description']);
    $category = sanitizeInput($_POST['category']);
    $dosage_form = sanitizeInput($_POST['dosage_form']);
    $strength = sanitizeInput($_POST['strength']);
    $quantity = sanitizeInput($_POST['quantity']);
    $unit_price = sanitizeInput($_POST['unit_price']);
    $wholesale_price = sanitizeInput($_POST['wholesale_price']);
    $expiry_date = sanitizeInput($_POST['expiry_date']);
    $batch_number = sanitizeInput($_POST['batch_number']);
    $supplier = sanitizeInput($_POST['supplier']);
    $manufacturer = sanitizeInput($_POST['manufacturer']);
    $storage_conditions = sanitizeInput($_POST['storage_conditions']);
    
    // Validation
    if (empty($name) || empty($quantity) || empty($unit_price)) {
        $error_message = "Name, quantity, and unit price are required.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error_message = "Please enter a valid quantity.";
    } elseif (!is_numeric($unit_price) || $unit_price <= 0) {
        $error_message = "Please enter a valid unit price.";
    } else {
        try {
            // Insert medicine
            $db->query("INSERT INTO pharmacy (name, generic_name, brand_name, description, category, dosage_form, strength, quantity, unit_price, wholesale_price, expiry_date, batch_number, supplier, manufacturer, storage_conditions) VALUES (:name, :generic_name, :brand_name, :description, :category, :dosage_form, :strength, :quantity, :unit_price, :wholesale_price, :expiry_date, :batch_number, :supplier, :manufacturer, :storage_conditions)");
            $db->bind(':name', $name);
            $db->bind(':generic_name', $generic_name);
            $db->bind(':brand_name', $brand_name);
            $db->bind(':description', $description);
            $db->bind(':category', $category);
            $db->bind(':dosage_form', $dosage_form);
            $db->bind(':strength', $strength);
            $db->bind(':quantity', $quantity);
            $db->bind(':unit_price', $unit_price);
            $db->bind(':wholesale_price', $wholesale_price);
            $db->bind(':expiry_date', $expiry_date);
            $db->bind(':batch_number', $batch_number);
            $db->bind(':supplier', $supplier);
            $db->bind(':manufacturer', $manufacturer);
            $db->bind(':storage_conditions', $storage_conditions);
            $db->execute();
            
            logActivity(getUserId(), 'Add Medicine', "Added new medicine: $name");
            
            header('Location: pharmacy.php?success=Medicine added successfully');
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
    <title>Add Medicine - Hospital Management System</title>
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
                <h1>Add New Medicine</h1>
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
            
            <!-- Add Medicine Form -->
            <div class="form-container">
                <div class="form-header">
                    <h3 class="form-title">Medicine Information</h3>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">Medicine Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="generic_name" class="form-label">Generic Name</label>
                            <input type="text" id="generic_name" name="generic_name" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="brand_name" class="form-label">Brand Name</label>
                            <input type="text" id="brand_name" name="brand_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select Category</option>
                                <option value="Analgesic">Analgesic</option>
                                <option value="Antibiotic">Antibiotic</option>
                                <option value="Antidiabetic">Antidiabetic</option>
                                <option value="Antihypertensive">Antihypertensive</option>
                                <option value="Antihistamine">Antihistamine</option>
                                <option value="Antacid">Antacid</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dosage_form" class="form-label">Dosage Form</label>
                            <select id="dosage_form" name="dosage_form" class="form-control">
                                <option value="">Select Form</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Cream">Cream</option>
                                <option value="Ointment">Ointment</option>
                                <option value="Drops">Drops</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="strength" class="form-label">Strength</label>
                            <input type="text" id="strength" name="strength" class="form-control" placeholder="e.g., 500mg, 10ml">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_price" class="form-label">Unit Price *</label>
                            <input type="number" id="unit_price" name="unit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="wholesale_price" class="form-label">Wholesale Price</label>
                            <input type="number" id="wholesale_price" name="wholesale_price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="batch_number" class="form-label">Batch Number</label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="supplier" class="form-label">Supplier</label>
                            <input type="text" id="supplier" name="supplier" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="manufacturer" class="form-label">Manufacturer</label>
                        <input type="text" id="manufacturer" name="manufacturer" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="storage_conditions" class="form-label">Storage Conditions</label>
                        <textarea id="storage_conditions" name="storage_conditions" class="form-control" rows="2" placeholder="e.g., Store in cool, dry place"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Add Medicine</button>
                        <a href="pharmacy.php" class="btn-primary" style="background: #6c757d; margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>