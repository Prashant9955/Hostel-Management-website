-- JBH Hostel Management System - Database Schema
-- Junior Boys Hostel | Dayalbagh Educational Institute
-- Run this in phpMyAdmin or MySQL CLI to create the database

CREATE DATABASE IF NOT EXISTS jbh_hostel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jbh_hostel;

-- Admins / Wardens table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'warden') DEFAULT 'warden',
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    room_number VARCHAR(20),
    block VARCHAR(20) DEFAULT 'A',
    room_type ENUM('single', 'double', 'triple', 'reserved') DEFAULT 'triple',
    course VARCHAR(50),
    year INT,
    department VARCHAR(50),
    guardian_name VARCHAR(100),
    guardian_phone VARCHAR(20),
    profile_photo VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Notices table
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    category ENUM('urgent', 'general', 'mess', 'sports', 'maintenance', 'admin') DEFAULT 'general',
    attachment VARCHAR(255),
    created_by INT,
    is_pinned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Mess bills table
CREATE TABLE mess_bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    month_year VARCHAR(7) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    days_stayed INT DEFAULT 30,
    due_date DATE,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    razorpay_order_id VARCHAR(100),
    razorpay_payment_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_month (student_id, month_year)
);

-- Payments log (for audit)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mess_bill_id INT,
    student_id INT,
    amount DECIMAL(10,2) NOT NULL,
    razorpay_order_id VARCHAR(100),
    razorpay_payment_id VARCHAR(100),
    status VARCHAR(50),
    payment_method VARCHAR(50) DEFAULT 'razorpay',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mess_bill_id) REFERENCES mess_bills(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
);

-- Complaints / Maintenance requests
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('electrical', 'plumbing', 'furniture', 'cleaning', 'other') DEFAULT 'other',
    room_location VARCHAR(50),
    status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Leave applications
CREATE TABLE leave_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- Mess menu (weekly)
CREATE TABLE mess_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week TINYINT NOT NULL,
    breakfast VARCHAR(255),
    lunch VARCHAR(255),
    dinner VARCHAR(255),
    snacks VARCHAR(255),
    week_start DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact form submissions (public)
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    query_type VARCHAR(50),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seat availability (can be updated by admin)
CREATE TABLE seat_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (password: password - CHANGE IN PRODUCTION!)
-- Bcrypt hash for 'password'
INSERT INTO admins (username, password, full_name, role, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hostel Admin', 'admin', 'admin@jbh.dei.ac.in'),
('warden', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chief Warden', 'warden', 'warden@jbh.dei.ac.in');

-- Insert sample student (password: password - CHANGE IN PRODUCTION!)
INSERT INTO students (student_id, password, full_name, email, phone, room_number, block, room_type, course, year, department) VALUES
('DEI-2K23-CS-042', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rahul Kumar', 'rahul@student.dei.ac.in', '9876543210', '114', 'A', 'triple', 'B.Sc CS', 2, 'Computer Science');

-- Insert sample notices
INSERT INTO notices (title, content, category, created_by) VALUES
('Mess Bill Payment Deadline Extended', 'Due to technical issues, the payment deadline has been extended to 20th March 2025.', 'urgent', 1),
('Annual Sports Day Registration Open', 'All boarders are invited to register for Annual Sports Day events by 20th March.', 'sports', 1),
('Water Supply Interruption – Block C', 'Water supply in Block C will be interrupted from 8 AM to 12 PM on 16th March for pipeline maintenance.', 'urgent', 1),
('New Mess Menu for April', 'Revised mess menu for April 2025 has been uploaded to the student portal. Feedback welcome.', 'mess', 1),
('Wi-Fi Upgrade Completed', 'Campus-wide Wi-Fi has been upgraded to 100 Mbps fiber. New passwords posted on notice boards.', 'general', 1);

-- Insert sample mess bills
INSERT INTO mess_bills (student_id, month_year, amount, days_stayed, due_date, status) VALUES
(1, '2025-01', 2750.00, 31, '2025-01-15', 'paid'),
(1, '2025-02', 2680.00, 28, '2025-02-15', 'paid'),
(1, '2025-03', 2850.00, 31, '2025-03-20', 'pending');

-- Insert seat availability
INSERT INTO seat_availability (room_type, total_seats, available_seats) VALUES
('Double Sharing', 80, 18),
('Triple Sharing', 120, 6),
('Single Room', 20, 0),
('Reserved Category', 20, 4);

-- Insert sample complaints for student 1
INSERT INTO complaints (student_id, subject, description, category, room_location, status, created_at) VALUES
(1, 'Bulb replacement – Room 114', 'One bulb in the room is fused and needs replacement.', 'electrical', 'Room 114', 'resolved', '2025-03-02 10:00:00'),
(1, 'Leaking tap – Bathroom 1', 'Tap in bathroom 1 is leaking continuously.', 'plumbing', 'Block A Bathroom', 'in_progress', '2025-03-08 14:30:00'),
(1, 'Fan not working – Room 114', 'Ceiling fan in room 114 is not rotating properly.', 'electrical', 'Room 114', 'pending', '2025-03-11 09:00:00');

-- Insert sample mess menu
INSERT INTO mess_menu (day_of_week, breakfast, lunch, dinner, snacks, week_start) VALUES
(1, 'Poha + Tea', 'Dal Tadka, Rice, Roti', 'Paneer Sabzi, Roti', 'Biscuits', '2025-03-10'),
(2, 'Upma + Juice', 'Chole, Rice, Salad', 'Aloo Matar, Roti', 'Banana', '2025-03-10'),
(3, 'Idli Sambhar', 'Rajma, Rice, Raita', 'Mix Veg, Roti', 'Milk', '2025-03-10'),
(4, 'Paratha + Curd', 'Dal Makhani, Rice', 'Kadhi, Rice, Roti', 'Chai', '2025-03-10'),
(5, 'Bread + Butter', 'Palak Paneer, Rice', 'Special Sabzi, Roti', 'Cake', '2025-03-10'),
(6, 'Puri + Halwa', 'Special Meal', 'Biryani + Raita', 'Snacks', '2025-03-10'),
(0, 'Aloo Paratha', 'Rajma Chawal', 'Pav Bhaji', 'Milk', '2025-03-10');
