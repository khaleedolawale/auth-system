-- SecureAuth Database Setup
-- Run this in phpMyAdmin before using the app

CREATE DATABASE IF NOT EXISTS secureauth;
USE secureauth;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS login_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    email VARCHAR(150),
    status ENUM('success','failed') NOT NULL,
    ip_address VARCHAR(45),
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin user: admin@secureauth.com / Admin@123
INSERT INTO users (full_name, email, password, role) VALUES
('System Admin', 'admin@secureauth.com', '$2y$10$TKh8H1.PfbuNIcml0EYzDOiXE40a4n3LJp3TfRIq6QMzFd0D1UYsS', 'admin');

-- Regular user: john@example.com / User@123
INSERT INTO users (full_name, email, password, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');
