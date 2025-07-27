<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database();

// Get monthly statistics
$current_month = date('Y-m');
$last_month = date('Y-m', strtotime('-1 month'));

// Current month stats
$db->query("SELECT COUNT(*) as total FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m') = :month");
$db->bind(':month', $current_month);
$current_month_appointments = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient' AND DATE_FORMAT(created_at, '%Y-%m') = :month");
$db->bind(':month', $current_month);
$new_patients_this_month = $db->single()['total'];

$db->query("SELECT SUM(total_amount) as total FROM billing WHERE status = 'Paid' AND DATE_FORMAT(billing_date, '%Y-%m') = :month");
$db->bind(':month', $current_month);
$revenue_this_month = $db->single()['total'] ?: 0;

// Last month stats for comparison
$db->query("SELECT COUNT(*) as total FROM appointments WHERE DATE_FORMAT(appointment_date, '%Y-%m') = :month");
$db->bind(':month', $last_month);
$last_month_appointments = $db->single()['total'];

// Department wise appointments
$db->query("SELECT dt.department, COUNT(a.appointment_id) as total_appointments 
           FROM appointments a 
           JOIN doctors dt ON a.doctor_id = dt.doctor_id 
           WHERE DATE_FORMAT(a.appointment_date, '%Y-%m') = :month 
           GROUP BY dt.department 
           ORDER BY total_appointments DESC");
$db->bind(':month', $current_month);
$department_stats = $db->resultset();

// Top doctors by appointments
$db->query("SELECT u.first_name, u.last_name, d.specialization, COUNT(a.appointment_id) as total_appointments 
           FROM appointments a 
           JOIN doctors d ON a.doctor_id = d.doctor_id 
           JOIN users u ON d.user_id = u.user_id 
           WHERE DATE_FORMAT(a.appointment_date, '%Y-%m') = :month 
           GROUP BY a.doctor_id 
           ORDER BY total_appointments DESC 
           LIMIT 5");
$db->bind(':month', $current_month);
$top_doctors = $db->resultset();

// Monthly revenue trend (last 6 months)
$revenue_trend = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $db->query("SELECT SUM(total_amount) as total FROM billing WHERE status = 'Paid' AND DATE_FORMAT(billing_date, '%Y-%m') = :month");
    $db->bind(':month', $month);
    $revenue = $db->single()['total'] ?: 0;
    $revenue_trend[] = [
        'month' => date('M Y', strtotime($month . '-01')),
        'revenue' => $revenue
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Hospital Management System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h1>Reports & Analytics</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <span>Welcome, <?php echo getUserName(); ?></span>
                    <a href="../auth/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
            
            <!-- Monthly Overview -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">This Month Appointments</h3>
                        <div class="card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $current_month_appointments; ?></div>
                    <div class="card-change">
                        <?php 
                        $change = $last_month_appointments > 0 ? (($current_month_appointments - $last_month_appointments) / $last_month_appointments) * 100 : 0;
                        echo ($change >= 0 ? '+' : '') . number_format($change, 1) . '% from last month';
                        ?>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">New Patients</h3>
                        <div class="card-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo $new_patients_this_month; ?></div>
                    <div class="card-change">This month</div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Revenue</h3>
                        <div class="card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">Rs. <?php echo number_format($revenue_this_month, 2); ?></div>
                    <div class="card-change">This month</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Revenue Trend (Last 6 Months)</h3>
                </div>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
            
            <!-- Department Statistics -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Department Performance (This Month)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Total Appointments</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($department_stats)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px;">
                                No data available for this month
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($department_stats as $dept): ?>
                            <tr>
                                <td><?php echo $dept['department']; ?></td>
                                <td><?php echo $dept['total_appointments']; ?></td>
                                <td>
                                    <?php 
                                    $percentage = $current_month_appointments > 0 ? ($dept['total_appointments'] / $current_month_appointments) * 100 : 0;
                                    echo number_format($percentage, 1) . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Top Doctors -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Top Performing Doctors (This Month)</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Appointments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_doctors)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px;">
                                No data available for this month
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($top_doctors as $doctor): ?>
                            <tr>
                                <td>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></td>
                                <td><?php echo $doctor['specialization']; ?></td>
                                <td><?php echo $doctor['total_appointments']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenue_trend, 'month')); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode(array_column($revenue_trend, 'revenue')); ?>,
                    borderColor: '#00B4D8',
                    backgroundColor: 'rgba(0, 180, 216, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>