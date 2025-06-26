<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="welcome-text">
                <h1>Welcome to MediCare</h1>
                <p>Your trusted healthcare management system. Providing comprehensive medical services with cutting-edge technology and compassionate care.</p>
                <div style="margin-top: 20px;">
                    <a href="home.php" class="btn btn-secondary" style="display: inline-block; padding: 12px 24px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 25px; transition: all 0.3s ease;">
                        <i class="fas fa-home"></i> Visit Homepage
                    </a>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Sign In</h2>
                <p>Access your healthcare dashboard</p>
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
            if (isset($_GET['message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
            }
            ?>
            
            <form class="login-form" action="auth/login_process.php" method="POST">
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
                        <input type="radio" id="admin" name="role" value="admin" required>
                        <label for="admin" class="role-label">
                            <i class="fas fa-user-shield"></i><br>Admin
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="doctor" name="role" value="doctor" required>
                        <label for="doctor" class="role-label">
                            <i class="fas fa-user-md"></i><br>Doctor
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="staff" name="role" value="staff" required>
                        <label for="staff" class="role-label">
                            <i class="fas fa-user-nurse"></i><br>Staff
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="patient" name="role" value="patient" required>
                        <label for="patient" class="role-label">
                            <i class="fas fa-user"></i><br>Patient
                        </label>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="auth/forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-btn">
                    Sign In
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="auth/register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        // Add loading state to login button
        document.querySelector('.login-form').addEventListener('submit', function() {
            const btn = document.querySelector('.login-btn');
            btn.classList.add('loading');
            btn.textContent = 'Signing In...';
        });

        // Auto-focus first input
        document.getElementById('username').focus();
    </script>
</body>
</html>