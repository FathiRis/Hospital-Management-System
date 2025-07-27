// Homepage JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }));
    
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Navbar background change on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        }
    });
    
    // Load doctors data
    loadDoctors();
    
    // Doctor filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            filterDoctors(filter);
        });
    });
    
    // Appointment form handling
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAppointmentRequest();
        });
    }
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.tip-card, .service-card, .doctor-card, .emergency-card').forEach(el => {
        observer.observe(el);
    });
});

// Sample doctors data
const doctorsData = [
    {
        id: 1,
        name: 'Dr. John Smith',
        specialty: 'Cardiology',
        department: 'cardiology',
        experience: '15 years',
        description: 'Experienced cardiologist specializing in heart disease prevention and treatment.',
        fee: 1500
    },
    {
        id: 2,
        name: 'Dr. Sarah Jones',
        specialty: 'Pediatrics',
        department: 'pediatrics',
        experience: '10 years',
        description: 'Dedicated pediatrician providing comprehensive care for children and adolescents.',
        fee: 1200
    },
    {
        id: 3,
        name: 'Dr. Michael Wilson',
        specialty: 'Neurology',
        department: 'neurology',
        experience: '12 years',
        description: 'Neurologist specializing in brain and nervous system disorders.',
        fee: 1000
    },
    {
        id: 4,
        name: 'Dr. Emily Davis',
        specialty: 'Orthopedics',
        department: 'orthopedics',
        experience: '8 years',
        description: 'Orthopedic surgeon expert in bone, joint, and musculoskeletal treatments.',
        fee: 1500
    },
    {
        id: 5,
        name: 'Dr. Robert Brown',
        specialty: 'General Medicine',
        department: 'general',
        experience: '20 years',
        description: 'Family medicine practitioner providing comprehensive primary healthcare.',
        fee: 1600
    },
    {
        id: 6,
        name: 'Dr. Lisa Anderson',
        specialty: 'Cardiology',
        department: 'cardiology',
        experience: '14 years',
        description: 'Interventional cardiologist specializing in minimally invasive procedures.',
        fee: 1500
    }
];

function loadDoctors() {
    const doctorsGrid = document.getElementById('doctorsGrid');
    if (!doctorsGrid) return;
    
    doctorsGrid.innerHTML = '';
    
    doctorsData.forEach(doctor => {
        const doctorCard = createDoctorCard(doctor);
        doctorsGrid.appendChild(doctorCard);
    });
}

function createDoctorCard(doctor) {
    const card = document.createElement('div');
    card.className = 'doctor-card';
    card.setAttribute('data-department', doctor.department);
    
    card.innerHTML = `
        <div class="doctor-image">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="doctor-info">
            <h3>${doctor.name}</h3>
            <div class="specialty">${doctor.specialty}</div>
            <p>${doctor.description}</p>
            <p><strong>Experience:</strong> ${doctor.experience}</p>
            <p><strong>Consultation Fee:</strong> Rs. ${doctor.fee}.00</p>
            <div class="doctor-actions">
                <button class="btn btn-primary btn-sm" onclick="bookAppointment(${doctor.id})">Book Appointment</button>
                <button class="btn btn-secondary btn-sm" onclick="viewProfile(${doctor.id})">View Profile</button>
            </div>
        </div>
    `;
    
    return card;
}

function filterDoctors(filter) {
    const doctorCards = document.querySelectorAll('.doctor-card');
    
    doctorCards.forEach(card => {
        if (filter === 'all' || card.getAttribute('data-department') === filter) {
            card.style.display = 'block';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

function bookAppointment(doctorId) {
    // Check if user is logged in (this would be handled by your backend)
    const isLoggedIn = false; // This should be determined by your session management
    
    if (!isLoggedIn) {
        // Show modal or redirect to login/register
        showLoginModal();
    } else {
        // Redirect to appointment booking page
        window.location.href = `patient/book_appointment.php?doctor_id=${doctorId}`;
    }
}

function viewProfile(doctorId) {
    // Redirect to doctor profile page
    window.location.href = `doctor_profile.php?id=${doctorId}`;
}

function showLoginModal() {
    // Create a simple modal
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
    
    // Add modal styles
    const modalStyles = `
        <style>
        .login-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
        }
        .modal-body {
            padding: 30px;
            text-align: center;
        }
        .modal-body p {
            margin-bottom: 25px;
            color: var(--dark-gray);
        }
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        </style>
    `;
    
    document.head.insertAdjacentHTML('beforeend', modalStyles);
    
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

function handleAppointmentRequest() {
    const form = document.getElementById('appointmentForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Processing...';
    submitBtn.disabled = true;
    
    // Simulate form submission (replace with actual backend call)
    setTimeout(() => {
        // Show success message
        showNotification('Appointment request submitted! You will be redirected to complete registration.', 'success');
        
        // Reset form
        form.reset();
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // Redirect to registration after a delay
        setTimeout(() => {
            window.location.href = 'auth/register.php?role=patient';
        }, 2000);
    }, 1500);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add notification styles
    const notificationStyles = `
        <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            max-width: 400px;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification-success {
            border-left: 4px solid var(--success-color);
        }
        .notification-content {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .notification-content i {
            font-size: 20px;
            color: var(--success-color);
        }
        </style>
    `;
    
    if (!document.querySelector('.notification-styles')) {
        const styleElement = document.createElement('style');
        styleElement.className = 'notification-styles';
        styleElement.textContent = notificationStyles.replace(/<\/?style>/g, '');
        document.head.appendChild(styleElement);
    }
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add scroll animations
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.hero-image');
    
    if (parallax) {
        const speed = scrolled * 0.5;
        parallax.style.transform = `translateY(${speed}px)`;
    }
});