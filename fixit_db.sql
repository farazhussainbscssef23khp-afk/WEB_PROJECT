-- FixIt Database Schema
-- Public Problem Reporting System
-- Developed by Faraz Hussain & Ali Raza - Sukkur IBA University

-- Create database
CREATE DATABASE IF NOT EXISTS fixit_db;
USE fixit_db;

-- Create reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    image VARCHAR(255),
    location VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert comprehensive sample data
INSERT INTO reports (description, image, location, status, created_at) VALUES
('Broken streetlight on main road near the university gate. Very dark at night and poses safety risk for students and staff.', 'assets/images/uploads/report_1.jpg', 'Main Road, Sukkur IBA University', 'Pending', '2024-11-15 08:30:00'),
('Large pothole in front of the shopping center. Several vehicles have been damaged and it is causing traffic congestion.', 'assets/images/uploads/report_2.jpg', 'Shopping Center Road, Sukkur', 'In Progress', '2024-11-14 14:15:00'),
('Garbage overflow near residential area. Bad smell and health hazard for nearby residents. Animals are spreading the waste.', 'assets/images/uploads/report_3.jpg', 'Block 5, Sukkur', 'Resolved', '2024-11-13 10:45:00'),
('Broken water pipeline causing flooding on the street. Water is being wasted and creating muddy conditions.', 'assets/images/uploads/report_4.jpg', 'Near City Hospital, Sukkur', 'Pending', '2024-11-12 16:20:00'),
('Traffic signal not working at busy intersection. Causing traffic jams and safety concerns for pedestrians.', 'assets/images/uploads/report_5.jpg', 'Main Chowk, Sukkur', 'In Progress', '2024-11-11 09:15:00'),
('Fallen tree blocking the road after heavy rain. Vehicles cannot pass and it is creating a safety hazard.', 'assets/images/uploads/report_6.jpg', 'Garden Road, Sukkur', 'Resolved', '2024-11-10 11:30:00'),
('Street dogs causing problems in residential area. Residents are afraid to walk outside, especially children.', NULL, 'Block 8, Sukkur', 'Pending', '2024-11-09 07:45:00'),
('Illegal construction blocking the footpath. Pedestrians are forced to walk on the road, creating safety risks.', 'assets/images/uploads/report_8.jpg', 'Market Area, Sukkur', 'In Progress', '2024-11-08 13:10:00'),
('Drainage system blocked causing water accumulation. Mosquito breeding and bad smell affecting nearby homes.', 'assets/images/uploads/report_9.jpg', 'Residential Area, Block 3', 'Resolved', '2024-11-07 15:25:00'),
('Street lights not working in several areas. Complete darkness at night making it unsafe for residents.', NULL, 'Multiple locations, Sukkur', 'Pending', '2024-11-06 18:40:00'),
('Road damaged with multiple potholes. Difficult for vehicles to drive and causing accidents.', 'assets/images/uploads/report_11.jpg', 'Industrial Area Road, Sukkur', 'In Progress', '2024-11-05 12:55:00'),
('Public park equipment broken and unsafe for children. Swings and slides need immediate repair.', 'assets/images/uploads/report_12.jpg', 'City Park, Sukkur', 'Resolved', '2024-11-04 10:15:00');

-- Create admin users table (optional feature)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Instructions for setting up the database:
-- 1. Import this SQL file in phpMyAdmin or run: mysql -u root -p < fixit_db.sql
-- 2. Make sure MySQL is running on your XAMPP/WAMP server
-- 3. Update database connection details in backend/db_connect.php