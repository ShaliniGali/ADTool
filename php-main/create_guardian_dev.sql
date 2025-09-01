-- Create GUARDIAN_DEV database and users table
CREATE DATABASE IF NOT EXISTS GUARDIAN_DEV;
USE GUARDIAN_DEV;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Insert a test user
INSERT INTO users (name, email, status) 
VALUES ('Test User', 'test@example.com', 'active') 
ON DUPLICATE KEY UPDATE name = 'Test User', status = 'active';
