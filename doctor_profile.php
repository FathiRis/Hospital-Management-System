<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();

// Get doctor ID from URL
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($doctor_id <= 0) {
    header('Location: home.php');
    exit();
}

// Get doctor information
$db->query("SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.address 
           FROM doctors d 
           JOIN users u ON d.user_id = u.user_id 
           WHERE d.doctor_id = :doctor_id");
$db->bind(':doctor_id', $doctor_id);
$doctor = $db->single();

if (!$doctor) {
    header('Location: home.php');
    exit();
}

// Get doctor's schedule (if available)
$db->query("SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
$db->bind(':doctor_id', $doctor_id);
$schedule = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?> - MediCare Hospital</title>
    <link rel="stylesheet" href="css/homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-hospital"></i>
                <span>MediCare</span>
            </div>
            <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li><a href="home.php#services" class="nav-link">Services</a></li>
                <li><a href="home.php#doctors" class="nav-link">Doctors</a></li>
                <li><a href="home.php#contact" class="nav-link">Contact</a></li>
                <li><a href="index.php" class="nav-link login-btn">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Doctor Profile Section -->
    <section class="doctor-profile" style="padding-top: 120px;">
        <div class="container">
            <div class="profile-content">
                <div class="profile-header">
                    <div class="doctor-image-large">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="doctor-details">
                        <h1>Dr. <?php echo $doctor['first_name'] . ' ' . $doctor['last_name']; ?></h1>
                        <div class="specialty"><?php echo $doctor['specialization']; ?></div>
                        <div class="department"><?php echo $doctor['department']; ?> Department</div>
                        <div class="experience"><?php echo $doctor['experience_years']; ?> years of experience</div>
                        <div class="consultation-fee">Consultation Fee: Rs. <?php echo number_format($doctor['consultation_fee'], 2); ?></div>
                    </div>
                </div>

                <div class="profile-sections">
                    <div class="profile-main">
                        <div class="section">
                            <h3>About Dr. <?php echo $doctor['last_name']; ?></h3>
                            <p><?php echo $doctor['bio'] ?: 'Experienced medical professional dedicated to providing quality healthcare.'; ?></p>
                        </div>

                        <?php if ($doctor['qualifications']): ?>
                        <div class="section">
                            <h3>Qualifications</h3>
                            <p><?php echo nl2br($doctor['qualifications']); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="section">
                            <h3>Contact Information</h3>
                            <div class="contact-info">
                                <?php if ($doctor['phone']): ?>
                                <p><i class="fas fa-phone"></i> <?php echo $doctor['phone']; ?></p>
                                <?php endif; ?>
                                <p><i class="fas fa-envelope"></i> <?php echo $doctor['email']; ?></p>
                                <p><i class="fas fa-building"></i> <?php echo $doctor['department']; ?> Department</p>
                            </div>
                        </div>
                    </div>

                    <div class="profile-sidebar">
                        <div class="appointment-booking">
                            <h3>Book Appointment</h3>
                            <p>Schedule a consultation with Dr. <?php echo $doctor['last_name']; ?></p>
                            <button class="btn btn-primary" onclick="bookAppointment(<?php echo $doctor_id; ?>)">
                                <i class="fas fa-calendar-plus"></i> Book Now
                            </button>
                        </div>

                        <?php if (!empty($schedule)): ?>
                        <div class="schedule-info">
                            <h3>Available Hours</h3>
                            <div class="schedule-list">
                                <?php foreach ($schedule as $day): ?>
                                <div class="schedule-item">
                                    <span class="day"><?php echo $day['day_of_week']; ?></span>
                                    <span class="time">
                                        <?php echo formatTime($day['start_time']) . ' - ' . formatTime($day['end_time']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="schedule-info">
                            <h3>Available Hours</h3>
                            <div class="schedule-list">
                                <div class="schedule-item">
                                    <span class="day">Mon - Fri</span>
                                    <span class="time"><?php echo formatTime($doctor['schedule_start']) . ' - ' . formatTime($doctor['schedule_end']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-hospital"></i>
                        <span>MediCare</span>
                    </div>
                    <p>Your trusted healthcare partner, providing comprehensive medical services with compassion and excellence.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="home.php#services">Services</a></li>
                        <li><a href="home.php#doctors">Doctors</a></li>
                        <li><a href="index.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Medical Center </p>
                        <p><i class="fas fa-phone"></i> +94 - 047 987 4563 </p>
                        <p><i class="fas fa-envelope"></i> info@medicare.com</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 MediCare Hospital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
    .doctor-profile {
        background: var(--light-gray);
        padding: 80px 0;
    }

    .profile-header {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 40px;
    }

    .doctor-image-large {
        width: 150px;
        height: 150px;
        background: var(--bg-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        color: var(--primary-color);
        flex-shrink: 0;
    }

    .doctor-details h1 {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .specialty {
        font-size: 1.3rem;
        color: var(--accent-color);
        font-weight: 600;
        margin-bottom: 5px;
    }

    .department {
        color: var(--dark-gray);
        margin-bottom: 10px;
    }

    .experience {
        color: var(--dark-gray);
        margin-bottom: 10px;
    }

    .consultation-fee {
        font-size: 1.2rem;
        color: var(--primary-color);
        font-weight: 600;
    }

    .profile-sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .profile-main .section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .profile-main .section h3 {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--bg-light);
        padding-bottom: 10px;
    }

    .profile-sidebar > div {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .appointment-booking {
        text-align: center;
    }

    .appointment-booking h3 {
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .appointment-booking p {
        color: var(--dark-gray);
        margin-bottom: 20px;
    }

    .schedule-info h3 {
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .schedule-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }

    .schedule-item:last-child {
        border-bottom: none;
    }

    .day {
        font-weight: 600;
        color: var(--text-dark);
    }

    .time {
        color: var(--dark-gray);
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .profile-sections {
            grid-template-columns: 1fr;
        }

        .doctor-details h1 {
            font-size: 2rem;
        }
    }
    </style>

    <script>
    function bookAppointment(doctorId) {
        // Check if user is logged in
        const isLoggedIn = false; // This should be determined by your session management
        
        if (!isLoggedIn) {
            showLoginModal();
        } else {
            window.location.href = `patient/book_appointment.php?doctor_id=${doctorId}`;
        }
    }

    function showLoginModal() {
        const modal = document.createElement('div');
        modal.className = 'login-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Login Required</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p>You need to register or login as a patient to book an appointment.</p>
                    <div class="modal-actions">
                        <a href="auth/register.php" class="btn btn-primary">Register as Patient</a>
                        <a href="index.php" class="btn btn-secondary">Login</a>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal functionality
        modal.querySelector('.close-modal').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    </script>
</body>
</html>