-- Complete Hospital Management System Database Schema
-- This file contains all necessary tables and columns for the entire system

DROP DATABASE IF EXISTS final_hospital_management_system;
CREATE DATABASE final_hospital_management_system;
USE final_hospital_management_system;

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
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
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
    allergies TEXT,
    current_medications TEXT,
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
    qualifications TEXT,
    experience_years INT DEFAULT 0,
    schedule_start TIME DEFAULT '09:00:00',
    schedule_end TIME DEFAULT '17:00:00',
    available_days VARCHAR(20) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Appointments Table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'No-Show', 'In-Progress') DEFAULT 'Scheduled',
    reason TEXT,
    diagnosis TEXT,
    prescription TEXT,
    notes TEXT,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- Medical Records Table
CREATE TABLE medical_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    visit_date DATE NOT NULL,
    chief_complaint TEXT,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    notes TEXT,
    vital_signs JSON,
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

-- Billing Table
CREATE TABLE billing (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT,
    description TEXT,
    amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    billing_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('Paid', 'Unpaid', 'Partial', 'Overdue') DEFAULT 'Unpaid',
    payment_method VARCHAR(50),
    payment_date DATE,
    insurance_claim BOOLEAN DEFAULT FALSE,
    insurance_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

-- Pharmacy Table
CREATE TABLE pharmacy (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    brand_name VARCHAR(100),
    description TEXT,
    category VARCHAR(50),
    dosage_form VARCHAR(50),
    strength VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    wholesale_price DECIMAL(10,2),
    expiry_date DATE,
    batch_number VARCHAR(50),
    supplier VARCHAR(100),
    manufacturer VARCHAR(100),
    storage_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Prescriptions Table
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    prescription_date DATE NOT NULL,
    instructions TEXT,
    status ENUM('Pending', 'Filled', 'Cancelled', 'Partial') DEFAULT 'Pending',
    total_amount DECIMAL(10,2) DEFAULT 0,
    dispensed_by INT,
    dispensed_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (dispensed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Prescription Items Table
CREATE TABLE prescription_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medicine_id INT NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    instructions TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES pharmacy(medicine_id) ON DELETE CASCADE
);

-- Activity Logs Table
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- System Settings Table
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctor Schedule Table
CREATE TABLE doctor_schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    max_appointments INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- Test Results Table
CREATE TABLE test_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    test_name VARCHAR(100) NOT NULL,
    test_type VARCHAR(50),
    test_date DATE NOT NULL,
    results TEXT,
    normal_range VARCHAR(100),
    status ENUM('Normal', 'Abnormal', 'Critical') DEFAULT 'Normal',
    notes TEXT,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE SET NULL
);

-- Insert Sample Data
INSERT INTO users (username, password, email, role, first_name, last_name, phone, address) VALUES
('admin', 'admin123', 'admin@hospital.com', 'admin', 'Admin', 'User', '1234567890', '123 Admin St'),
('dr_smith', 'doctor123', 'smith@hospital.com', 'doctor', 'John', 'Smith', '1234567891', '456 Doctor Ave'),
('dr_jones', 'doctor123', 'jones@hospital.com', 'doctor', 'Sarah', 'Jones', '1234567892', '789 Medical Blvd'),
('dr_wilson', 'doctor123', 'wilson@hospital.com', 'doctor', 'Michael', 'Wilson', '1234567893', '321 Health St'),
('staff1', 'staff123', 'staff@hospital.com', 'staff', 'Mike', 'Johnson', '1234567894', '321 Staff Rd'),
('staff2', 'staff123', 'staff2@hospital.com', 'staff', 'Lisa', 'Davis', '1234567895', '654 Staff Ave'),
('patient1', 'patient123', 'patient1@email.com', 'patient', 'Alice', 'Brown', '1234567896', '654 Patient Lane'),
('patient2', 'patient123', 'patient2@email.com', 'patient', 'Bob', 'Wilson', '1234567897', '987 Health St'),
('patient3', 'patient123', 'patient3@email.com', 'patient', 'Carol', 'Davis', '1234567898', '123 Wellness Rd');

INSERT INTO doctors (user_id, specialization, license_number, department, consultation_fee, bio, experience_years, qualifications) VALUES
(2, 'Cardiology', 'MD001', 'Cardiology', 150.00, 'Experienced cardiologist with 15 years of practice', 15, 'MD from Harvard Medical School, Board Certified in Cardiology'),
(3, 'Pediatrics', 'MD002', 'Pediatrics', 120.00, 'Specialist in child healthcare and development', 10, 'MD from Johns Hopkins, Pediatric Residency at Children\'s Hospital'),
(4, 'General Medicine', 'MD003', 'General Medicine', 100.00, 'Family medicine practitioner', 8, 'MD from Stanford University, Family Medicine Residency');

INSERT INTO patients (user_id, dob, gender, blood_type, height, weight, emergency_contact_name, emergency_contact_phone, allergies, current_medications, medical_history) VALUES
(7, '1985-06-15', 'Female', 'A+', 165.00, 60.00, 'John Brown', '9876543210', 'Penicillin allergy', 'Multivitamin daily', 'No significant medical history'),
(8, '1990-03-22', 'Male', 'O+', 175.00, 75.00, 'Mary Wilson', '9876543211', 'None known', 'None', 'Hypertension managed with medication'),
(9, '1988-12-10', 'Female', 'B+', 160.00, 55.00, 'David Davis', '9876543212', 'Shellfish allergy', 'Birth control pill', 'Asthma, well controlled');

INSERT INTO pharmacy (name, generic_name, brand_name, description, category, dosage_form, strength, quantity, unit_price, wholesale_price, expiry_date, supplier, manufacturer) VALUES
('Paracetamol', 'Acetaminophen', 'Tylenol', 'Pain reliever and fever reducer', 'Analgesic', 'Tablet', '500mg', 500, 5.00, 3.50, '2025-12-31', 'PharmaCorp', 'Johnson & Johnson'),
('Amoxicillin', 'Amoxicillin', 'Amoxil', 'Antibiotic for bacterial infections', 'Antibiotic', 'Capsule', '250mg', 200, 15.00, 10.00, '2025-06-30', 'MediSupply', 'GlaxoSmithKline'),
('Ibuprofen', 'Ibuprofen', 'Advil', 'Anti-inflammatory pain reliever', 'NSAID', 'Tablet', '200mg', 300, 8.00, 5.50, '2025-09-15', 'HealthMeds', 'Pfizer'),
('Aspirin', 'Acetylsalicylic acid', 'Bayer', 'Blood thinner and pain reliever', 'Antiplatelet', 'Tablet', '81mg', 400, 3.00, 2.00, '2025-11-20', 'PharmaCorp', 'Bayer'),
('Metformin', 'Metformin HCl', 'Glucophage', 'Diabetes medication', 'Antidiabetic', 'Tablet', '500mg', 250, 12.00, 8.00, '2025-08-30', 'DiabetesCare', 'Bristol Myers Squibb');

-- Sample appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, reason) VALUES
(1, 1, CURDATE(), '10:00:00', 'Scheduled', 'Regular checkup'),
(2, 2, CURDATE(), '14:00:00', 'Scheduled', 'Pediatric consultation'),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'Scheduled', 'Follow-up visit'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', 'Scheduled', 'General consultation');

-- Sample medical records
INSERT INTO medical_records (patient_id, doctor_id, visit_date, chief_complaint, diagnosis, treatment, notes) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Chest pain and shortness of breath', 'Hypertension', 'Prescribed medication and lifestyle changes', 'Patient responding well to treatment'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Fever and cough', 'Common cold', 'Rest and fluids', 'Symptoms resolved'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Annual physical examination', 'Annual checkup', 'Routine examination', 'All vitals normal');

-- Sample billing
INSERT INTO billing (patient_id, appointment_id, description, amount, tax_amount, total_amount, billing_date, due_date, status) VALUES
(1, 1, 'Consultation fee - Cardiology', 150.00, 15.00, 165.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Unpaid'),
(2, 2, 'Pediatric consultation', 120.00, 12.00, 132.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Paid'),
(3, 4, 'General consultation', 100.00, 10.00, 110.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Unpaid');

-- Sample prescriptions
INSERT INTO prescriptions (patient_id, doctor_id, prescription_date, instructions, status, total_amount) VALUES
(1, 1, CURDATE(), 'Take medication as prescribed. Follow up in 2 weeks.', 'Pending', 25.00),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Complete the full course of antibiotics.', 'Filled', 15.00);

-- Sample prescription items
INSERT INTO prescription_items (prescription_id, medicine_id, dosage, frequency, duration, quantity, unit_price, total_price, instructions) VALUES
(1, 1, '500mg', 'Twice daily', '7 days', 14, 5.00, 70.00, 'Take with food'),
(1, 4, '81mg', 'Once daily', '30 days', 30, 3.00, 90.00, 'Take in the morning'),
(2, 2, '250mg', 'Three times daily', '5 days', 15, 15.00, 225.00, 'Complete full course');

-- Sample system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('hospital_name', 'MediCare Hospital', 'Name of the hospital'),
('hospital_address', '123 Medical Center Drive, Healthcare City', 'Hospital address'),
('hospital_phone', '+1-555-MEDICAL', 'Hospital contact number'),
('appointment_duration', '30', 'Default appointment duration in minutes'),
('max_appointments_per_day', '20', 'Maximum appointments per doctor per day'),
('tax_rate', '10', 'Tax rate percentage for billing'),
('currency', 'USD', 'Currency used for billing');

-- Sample doctor schedules
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, is_available, max_appointments) VALUES
(1, 'Monday', '09:00:00', '17:00:00', TRUE, 8),
(1, 'Tuesday', '09:00:00', '17:00:00', TRUE, 8),
(1, 'Wednesday', '09:00:00', '17:00:00', TRUE, 8),
(1, 'Thursday', '09:00:00', '17:00:00', TRUE, 8),
(1, 'Friday', '09:00:00', '17:00:00', TRUE, 8),
(2, 'Monday', '08:00:00', '16:00:00', TRUE, 10),
(2, 'Tuesday', '08:00:00', '16:00:00', TRUE, 10),
(2, 'Wednesday', '08:00:00', '16:00:00', TRUE, 10),
(2, 'Thursday', '08:00:00', '16:00:00', TRUE, 10),
(2, 'Friday', '08:00:00', '16:00:00', TRUE, 10),
(3, 'Monday', '10:00:00', '18:00:00', TRUE, 6),
(3, 'Tuesday', '10:00:00', '18:00:00', TRUE, 6),
(3, 'Wednesday', '10:00:00', '18:00:00', TRUE, 6),
(3, 'Thursday', '10:00:00', '18:00:00', TRUE, 6),
(3, 'Friday', '10:00:00', '18:00:00', TRUE, 6);

-- Sample activity logs
INSERT INTO activity_logs (user_id, action, details) VALUES
(1, 'Login', 'Admin user logged in'),
(2, 'Login', 'Doctor logged in'),
(7, 'Book Appointment', 'Patient booked appointment with Dr. Smith'),
(1, 'Add Patient', 'Added new patient: Alice Brown');

-- Create indexes for better performance
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_doctor ON appointments(doctor_id);
CREATE INDEX idx_appointments_patient ON appointments(patient_id);
CREATE INDEX idx_medical_records_patient ON medical_records(patient_id);
CREATE INDEX idx_medical_records_doctor ON medical_records(doctor_id);
CREATE INDEX idx_billing_patient ON billing(patient_id);
CREATE INDEX idx_billing_status ON billing(status);
CREATE INDEX idx_prescriptions_patient ON prescriptions(patient_id);
CREATE INDEX idx_prescriptions_doctor ON prescriptions(doctor_id);
CREATE INDEX idx_pharmacy_name ON pharmacy(name);
CREATE INDEX idx_pharmacy_expiry ON pharmacy(expiry_date);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_date ON activity_logs(created_at);