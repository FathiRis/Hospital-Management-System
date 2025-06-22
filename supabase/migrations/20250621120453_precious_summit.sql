-- Hospital Management System Database
-- Run this script in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS hospital_management_system;
USE hospital_management_system;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'doctor', 'staff', 'patient') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patients Table
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    insurance_provider VARCHAR(100),
    insurance_number VARCHAR(50),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    medical_history TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Doctors Table
CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    department VARCHAR(100) NOT NULL,
    consultation_fee DECIMAL(10,2) NOT NULL,
    bio TEXT,
    schedule_start TIME DEFAULT '09:00:00',
    schedule_end TIME DEFAULT '17:00:00',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Appointments Table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'No-Show') DEFAULT 'Scheduled',
    reason TEXT,
    diagnosis TEXT,
    prescription TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- Medical Records Table
CREATE TABLE medical_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    visit_date DATE NOT NULL,
    diagnosis TEXT,
    treatment TEXT,
    notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- Billing Table
CREATE TABLE billing (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT,
    amount DECIMAL(10,2) NOT NULL,
    billing_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('Paid', 'Unpaid', 'Partial') DEFAULT 'Unpaid',
    payment_method VARCHAR(50),
    insurance_claim BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

-- Pharmacy Table
CREATE TABLE pharmacy (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    expiry_date DATE,
    supplier VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Prescriptions Table
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    prescription_date DATE NOT NULL,
    instructions TEXT,
    status ENUM('Pending', 'Filled', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

-- Prescription Items Table
CREATE TABLE prescription_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    notes TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES pharmacy(medicine_id) ON DELETE CASCADE
);

-- Insert Sample Data
INSERT INTO users (username, password, email, role, first_name, last_name, phone, address) VALUES
('admin', 'admin123', 'admin@hospital.com', 'admin', 'Admin', 'User', '1234567890', '123 Admin St'),
('dr_smith', 'doctor123', 'smith@hospital.com', 'doctor', 'John', 'Smith', '1234567891', '456 Doctor Ave'),
('dr_jones', 'doctor123', 'jones@hospital.com', 'doctor', 'Sarah', 'Jones', '1234567892', '789 Medical Blvd'),
('staff1', 'staff123', 'staff@hospital.com', 'staff', 'Mike', 'Johnson', '1234567893', '321 Staff Rd'),
('patient1', 'patient123', 'patient1@email.com', 'patient', 'Alice', 'Brown', '1234567894', '654 Patient Lane'),
('patient2', 'patient123', 'patient2@email.com', 'patient', 'Bob', 'Wilson', '1234567895', '987 Health St');

INSERT INTO doctors (user_id, specialization, license_number, department, consultation_fee, bio) VALUES
(2, 'Cardiology', 'MD001', 'Cardiology', 150.00, 'Experienced cardiologist with 15 years of practice'),
(3, 'Pediatrics', 'MD002', 'Pediatrics', 120.00, 'Specialist in child healthcare and development');

INSERT INTO patients (user_id, dob, gender, blood_type, height, weight, emergency_contact_name, emergency_contact_phone) VALUES
(5, '1985-06-15', 'Female', 'A+', 165.00, 60.00, 'John Brown', '9876543210'),
(6, '1990-03-22', 'Male', 'O+', 175.00, 75.00, 'Mary Wilson', '9876543211');

INSERT INTO pharmacy (name, description, quantity, price, expiry_date, supplier) VALUES
('Paracetamol', 'Pain reliever and fever reducer', 500, 5.00, '2025-12-31', 'PharmaCorp'),
('Amoxicillin', 'Antibiotic for bacterial infections', 200, 15.00, '2025-06-30', 'MediSupply'),
('Ibuprofen', 'Anti-inflammatory pain reliever', 300, 8.00, '2025-09-15', 'HealthMeds'),
('Aspirin', 'Blood thinner and pain reliever', 400, 3.00, '2025-11-20', 'PharmaCorp');