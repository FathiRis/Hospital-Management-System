<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hospital Management System</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="welcome-text">
                <h1>Join MediCare</h1>
                <p>Create your account to access our comprehensive healthcare management system and start your journey to better health.</p>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Create Account</h2>
                <p>Register for healthcare access</p>
            </div>
            
            <?php
            session_start();
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form class="login-form" action="register_process.php" method="POST">
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" id="patient" name="role" value="patient" checked required>
                        <label for="patient" class="role-label">
                            <i class="fas fa-user"></i><br>Patient
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="doctor" name="role" value="doctor" required>
                        <label for="doctor" class="role-label">
                            <i class="fas fa-user-md"></i><br>Doctor
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    Create Account
                </button>
            </form>
            
            <div class="register-link">
                Already have an account? <a href="../index.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.login-form').addEventListener('submit', function() {
            const btn = document.querySelector('.login-btn');
            btn.classList.add('loading');
            btn.textContent = 'Creating Account...';
        });
    </script>
</body>
</html>