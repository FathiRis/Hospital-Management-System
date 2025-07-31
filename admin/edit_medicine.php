<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_medicine'])) {
        // Update existing medicine
        $data = [
            'medicine_id' => intval($_POST['medicine_id']),
            'name' => sanitizeInput($_POST['name']),
            'generic_name' => sanitizeInput($_POST['generic_name']),
            'brand_name' => sanitizeInput($_POST['brand_name']),
            'description' => sanitizeInput($_POST['description']),
            'category' => sanitizeInput($_POST['category']),
            'dosage_form' => sanitizeInput($_POST['dosage_form']),
            'strength' => sanitizeInput($_POST['strength']),
            'quantity' => intval($_POST['quantity']),
            'unit_price' => floatval($_POST['unit_price']),
            'wholesale_price' => floatval($_POST['wholesale_price']),
            'expiry_date' => sanitizeInput($_POST['expiry_date']),
            'batch_number' => sanitizeInput($_POST['batch_number']),
            'supplier' => sanitizeInput($_POST['supplier']),
            'manufacturer' => sanitizeInput($_POST['manufacturer']),
            'storage_conditions' => sanitizeInput($_POST['storage_conditions'])
        ];
        
        $db->query("UPDATE pharmacy SET 
            name = :name,
            generic_name = :generic_name,
            brand_name = :brand_name,
            description = :description,
            category = :category,
            dosage_form = :dosage_form,
            strength = :strength,
            quantity = :quantity,
            unit_price = :unit_price,
            wholesale_price = :wholesale_price,
            expiry_date = :expiry_date,
            batch_number = :batch_number,
            supplier = :supplier,
            manufacturer = :manufacturer,
            storage_conditions = :storage_conditions,
            updated_at = NOW()
            WHERE medicine_id = :medicine_id");
        
        foreach ($data as $key => $value) {
            $db->bind(":$key", $value);
        }
        
        if ($db->execute()) {
            $_SESSION['success'] = "Medicine updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update medicine";
        }
    }
    elseif (isset($_POST['delete_medicine'])) {
        // Delete medicine
        $medicine_id = intval($_POST['medicine_id']);
        
        $db->query("DELETE FROM pharmacy WHERE medicine_id = :medicine_id");
        $db->bind(':medicine_id', $medicine_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = "Medicine deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete medicine";
        }
    }
    
    header("Location: pharmacy.php");
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Build query for medicines
$query = "SELECT * FROM pharmacy WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE :search OR generic_name LIKE :search OR brand_name LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category) && $category != 'all') {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

$query .= " ORDER BY name ASC";

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$medicines = $db->resultset();

// Get distinct categories for filter
$db->query("SELECT DISTINCT category FROM pharmacy ORDER BY category");
$categories = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management - Hospital System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .pharmacy-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-top: 20px;
        }
        
        .pharmacy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .search-box, .category-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .pharmacy-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .pharmacy-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        .pharmacy-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .pharmacy-table tr:hover {
            background: var(--bg-very-light);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .btn-edit {
            background: var(--warning-color);
            color: blue;
        }
        
        .btn-delete {
            background: var(--danger-color);
            color: red;
        }
        
        .low-stock {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .expired {
            color: var(--danger-color);
            text-decoration: line-through;
            font-weight: 600;
        }
        
        .expiring-soon {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 700px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .pharmacy-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <a href="dashboard.php" class="nav-link active">
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
                    <span>Welcome, <?php echo htmlspecialchars(getUserName()); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <div class="pharmacy-container">
                <div class="pharmacy-header">
                    <h2>Medicine Inventory</h2>
                    <div class="search-filter">
                        <form method="GET" class="search-box">
                            <input type="text" name="search" placeholder="Search medicines..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        
                        <form method="GET" class="category-filter">
                            <select name="category" onchange="this.form.submit()">
                                <option value="all">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php if ($category == $cat['category']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="form-group">
                        <a href="add_medicine.php" class="btn-primary" style="margin-left: 10px;">Add Medicine</a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="pharmacy-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Generic</th>
                                <th>Category</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medicines)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No medicines found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medicines as $med): 
                                    $expiry_class = '';
                                    $expiry_date = new DateTime($med['expiry_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($expiry_date);
                                    
                                    if ($expiry_date < $today) {
                                        $expiry_class = 'expired';
                                    } elseif ($interval->days <= 30) {
                                        $expiry_class = 'expiring-soon';
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($med['name']); ?></strong>
                                            <?php if ($med['brand_name']): ?>
                                                <br><small><?php echo htmlspecialchars($med['brand_name']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($med['generic_name']); ?></td>
                                        <td><?php echo htmlspecialchars($med['category']); ?></td>
                                        <td class="<?php echo $med['quantity'] < 10 ? 'low-stock' : ''; ?>">
                                            <?php echo $med['quantity']; ?>
                                            <?php if ($med['quantity'] < 10): ?>
                                                <i class="fas fa-exclamation-triangle" title="Low stock"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>Rs.<?php echo number_format($med['unit_price'], 2); ?></td>
                                        <td class="<?php echo $expiry_class; ?>">
                                            <?php echo date('M d, Y', strtotime($med['expiry_date'])); ?>
                                            <?php if ($expiry_class): ?>
                                                <i class="fas fa-exclamation-triangle"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick="showEditModal(
                                                    <?php echo $med['medicine_id']; ?>,
                                                    '<?php echo addslashes($med['name']); ?>',
                                                    '<?php echo addslashes($med['generic_name']); ?>',
                                                    '<?php echo addslashes($med['brand_name']); ?>',
                                                    '<?php echo addslashes($med['description']); ?>',
                                                    '<?php echo $med['category']; ?>',
                                                    '<?php echo $med['dosage_form']; ?>',
                                                    '<?php echo $med['strength']; ?>',
                                                    <?php echo $med['quantity']; ?>,
                                                    <?php echo $med['unit_price']; ?>,
                                                    <?php echo $med['wholesale_price']; ?>,
                                                    '<?php echo $med['expiry_date']; ?>',
                                                    '<?php echo $med['batch_number']; ?>',
                                                    '<?php echo addslashes($med['supplier']); ?>',
                                                    '<?php echo addslashes($med['manufacturer']); ?>',
                                                    '<?php echo addslashes($med['storage_conditions']); ?>'
                                                )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $med['medicine_id']; ?>)">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
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
    </div>
    
    <!-- Edit Medicine Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Medicine</h2>
            <form method="POST">
                <input type="hidden" name="medicine_id" id="edit_medicine_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_name">Medicine Name*</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_generic_name">Generic Name*</label>
                        <input type="text" id="edit_generic_name" name="generic_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_brand_name">Brand Name</label>
                        <input type="text" id="edit_brand_name" name="brand_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_category">Category*</label>
                        <input type="text" id="edit_category" name="category" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_dosage_form">Dosage Form</label>
                        <input type="text" id="edit_dosage_form" name="dosage_form">
                    </div>
                    <div class="form-group">
                        <label for="edit_strength">Strength</label>
                        <input type="text" id="edit_strength" name="strength">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_quantity">Quantity*</label>
                        <input type="number" id="edit_quantity" name="quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit_price">Unit Price*</label>
                        <input type="number" id="edit_unit_price" name="unit_price" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_wholesale_price">Wholesale Price</label>
                        <input type="number" id="edit_wholesale_price" name="wholesale_price" min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="edit_expiry_date">Expiry Date*</label>
                        <input type="date" id="edit_expiry_date" name="expiry_date" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_batch_number">Batch Number</label>
                        <input type="text" id="edit_batch_number" name="batch_number">
                    </div>
                    <div class="form-group">
                        <label for="edit_supplier">Supplier</label>
                        <input type="text" id="edit_supplier" name="supplier">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_manufacturer">Manufacturer</label>
                    <input type="text" id="edit_manufacturer" name="manufacturer">
                </div>
                
                <div class="form-group">
                    <label for="edit_storage_conditions">Storage Conditions</label>
                    <input type="text" id="edit_storage_conditions" name="storage_conditions">
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group"> 
                    <div class="form-actions">
                        <button type="button" class="btn-primary" onclick="closeModal('editModal')" style="background: #6c757d; margin-left: 10px;" >Cancel</button>
                        <button type="submit" name="update_medicine" class="btn btn-primary">Update Medicine</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete this medicine? This action cannot be undone.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="medicine_id" id="delete_medicine_id">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" name="delete_medicine" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Show modals
        function showAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function showEditModal(
            id, name, generic_name, brand_name, description, category, 
            dosage_form, strength, quantity, unit_price, wholesale_price, 
            expiry_date, batch_number, supplier, manufacturer, storage_conditions
        ) {
            document.getElementById('edit_medicine_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_generic_name').value = generic_name;
            document.getElementById('edit_brand_name').value = brand_name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_dosage_form').value = dosage_form;
            document.getElementById('edit_strength').value = strength;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_unit_price').value = unit_price;
            document.getElementById('edit_wholesale_price').value = wholesale_price;
            document.getElementById('edit_expiry_date').value = expiry_date;
            document.getElementById('edit_batch_number').value = batch_number;
            document.getElementById('edit_supplier').value = supplier;
            document.getElementById('edit_manufacturer').value = manufacturer;
            document.getElementById('edit_storage_conditions').value = storage_conditions;
            
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function confirmDelete(id) {
            document.getElementById('delete_medicine_id').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close when clicking outside modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>