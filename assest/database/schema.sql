-- Create Database
CREATE DATABASE IF NOT EXISTS barangay_178_emergency;
USE barangay_178_emergency;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    barangay_id VARCHAR(50) DEFAULT '178',
    role ENUM('admin', 'responder', 'user') NOT NULL DEFAULT 'user',
    contact_number VARCHAR(20),
    otp_code VARCHAR(255) NULL,
    otp_expiry DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Emergency Calls Table
CREATE TABLE emergency_calls (
    call_id INT PRIMARY KEY AUTO_INCREMENT,
    caller_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    emergency_type ENUM('Fire', 'Medical', 'Crime', 'Accident', 'Natural Disaster', 'Flood', 'Other') NOT NULL,
    incident_location VARCHAR(255) NOT NULL,
    barangay_area VARCHAR(100) DEFAULT 'Camarin North',
    incident_details TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    priority_level ENUM('Critical', 'High', 'Medium', 'Low') DEFAULT 'Medium',
    ai_priority_score DECIMAL(5,2),
    status ENUM('Pending', 'Dispatched', 'In-Progress', 'Resolved', 'Closed') DEFAULT 'Pending',
    assigned_responder INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_responder) REFERENCES users(user_id)
);

-- Responders Table
CREATE TABLE responders (
    responder_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    team_name VARCHAR(100),
    specialization VARCHAR(100),
    availability ENUM('Available', 'Busy', 'Off-Duty') DEFAULT 'Available',
    response_time_avg INT,
    current_location_lat DECIMAL(10,8),
    current_location_lng DECIMAL(11,8),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Dispatch History Table
CREATE TABLE dispatch_history (
    dispatch_id INT PRIMARY KEY AUTO_INCREMENT,
    call_id INT,
    responder_id INT,
    dispatched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    arrived_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (call_id) REFERENCES emergency_calls(call_id),
    FOREIGN KEY (responder_id) REFERENCES responders(responder_id)
);

-- Incident Reports Table
CREATE TABLE incident_reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    call_id INT,
    report_by INT,
    report_details TEXT,
    images TEXT,
    status ENUM('Draft', 'Submitted', 'Approved') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (call_id) REFERENCES emergency_calls(call_id),
    FOREIGN KEY (report_by) REFERENCES users(user_id)
);

-- AI Response Logs
CREATE TABLE ai_response_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    call_id INT,
    request_data JSON,
    response_data JSON,
    priority_score DECIMAL(5,2),
    reasoning TEXT,
    response_time_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (call_id) REFERENCES emergency_calls(call_id)
);

-- Insert Admin User (password: Admin@123)
INSERT INTO users (email, password, full_name, barangay_id, role, contact_number, is_active) 
VALUES ('admin@barangay178.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Barangay Administrator', '178', 'admin', '09123456788', 1);

-- Insert Sample Responders
INSERT INTO responders (user_id, team_name, specialization, availability) VALUES
(1, 'Alpha Team', 'Fire Rescue', 'Available'),
(1, 'Bravo Team', 'Medical Response', 'Available'),
(1, 'Charlie Team', 'Police Response', 'Available');

-- Insert Sample Emergency Contacts
INSERT INTO barangay_contacts (name, position, contact_number, email, department) VALUES
('Barangay Captain', 'Barangay Captain', '09123456789', 'captain@barangay178.gov.ph', 'Barangay Hall'),
('Barangay Secretary', 'Secretary', '09123456780', 'secretary@barangay178.gov.ph', 'Barangay Hall'),
('Barangay Health Center', 'Health Officer', '09123456782', 'health@barangay178.gov.ph', 'Health Services'),
('Barangay Police', 'Police Officer', '09123456783', 'police@barangay178.gov.ph', 'Peace and Order');