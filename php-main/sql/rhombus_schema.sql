-- Rhombus Project Database Schema
-- This file creates the necessary tables for the SOCOM application

-- Create the main database
CREATE DATABASE IF NOT EXISTS rhombus_db;
USE rhombus_db;

-- Create SOCOM schema
CREATE SCHEMA IF NOT EXISTS SOCOM_UI;

-- Users table
CREATE TABLE IF NOT EXISTS SOCOM_UI.users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User roles table
CREATE TABLE IF NOT EXISTS SOCOM_UI.user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cycles table for SOCOM cycles
CREATE TABLE IF NOT EXISTS SOCOM_UI.cycles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cycle_name VARCHAR(100) NOT NULL,
    cycle_year INT NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT FALSE,
    status ENUM('PLANNING', 'ACTIVE', 'COMPLETED', 'ARCHIVED') DEFAULT 'PLANNING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- File uploads table
CREATE TABLE IF NOT EXISTS SOCOM_UI.usr_dt_uploads (
    USR_DT_UPLOADS_ID INT AUTO_INCREMENT PRIMARY KEY,
    TYPE ENUM('DOCUMENT', 'DATABASE', 'PROGRAM_ALIGNMENT', 'FUNDING', 'METADATA') NOT NULL,
    CYCLE_ID INT NOT NULL,
    S3_PATH VARCHAR(500) NOT NULL,
    FILE_NAME VARCHAR(255) NOT NULL,
    VERSION VARCHAR(20) DEFAULT '1.0',
    TITLE VARCHAR(255),
    DESCRIPTION TEXT,
    USER_ID INT NOT NULL,
    FILE_STATUS ENUM('NEW', 'PROCESSING', 'COMPLETED', 'ERROR', 'CANCELLED', 'DELETED') DEFAULT 'NEW',
    FILE_SIZE BIGINT,
    MIME_TYPE VARCHAR(100),
    CREATED_TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UPDATED_TIMESTAMP TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CYCLE_ID) REFERENCES cycles(id),
    FOREIGN KEY (USER_ID) REFERENCES users(id),
    INDEX idx_cycle_type (CYCLE_ID, TYPE),
    INDEX idx_user_status (USER_ID, FILE_STATUS),
    INDEX idx_created (CREATED_TIMESTAMP)
);

-- Processing pipeline table
CREATE TABLE IF NOT EXISTS SOCOM_UI.processing_pipeline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_type ENUM('DOCUMENT', 'DATABASE', 'PROGRAM_ALIGNMENT', 'FUNDING', 'METADATA') NOT NULL,
    status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Pipeline mapping table
CREATE TABLE IF NOT EXISTS SOCOM_UI.pipeline_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pipeline_id INT NOT NULL,
    upload_id INT NOT NULL,
    upload_type ENUM('DOCUMENT', 'DATABASE', 'PROGRAM_ALIGNMENT', 'FUNDING', 'METADATA') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pipeline_id) REFERENCES processing_pipeline(id),
    FOREIGN KEY (upload_id) REFERENCES usr_dt_uploads(USR_DT_UPLOADS_ID)
);

-- Git tracking table
CREATE TABLE IF NOT EXISTS SOCOM_UI.git_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_type ENUM('UPLOAD_FILE', 'PROCESSING_RESULT', 'METADATA_UPDATE') NOT NULL,
    reference_id INT NOT NULL,
    user_id INT NOT NULL,
    git_hash VARCHAR(40),
    git_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Document metadata table
CREATE TABLE IF NOT EXISTS SOCOM_UI.document_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    metadata_key VARCHAR(100) NOT NULL,
    metadata_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES usr_dt_uploads(USR_DT_UPLOADS_ID) ON DELETE CASCADE,
    INDEX idx_upload_key (upload_id, metadata_key)
);

-- Processing logs table
CREATE TABLE IF NOT EXISTS SOCOM_UI.processing_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_id INT NOT NULL,
    log_level ENUM('DEBUG', 'INFO', 'WARNING', 'ERROR') DEFAULT 'INFO',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES usr_dt_uploads(USR_DT_UPLOADS_ID) ON DELETE CASCADE,
    INDEX idx_upload_level (upload_id, log_level),
    INDEX idx_created (created_at)
);

-- Insert default data
INSERT INTO SOCOM_UI.users (username, email, password_hash, first_name, last_name) VALUES
('admin', 'admin@rhombus.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator'),
('testuser', 'test@rhombus.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User');

INSERT INTO SOCOM_UI.user_roles (user_id, role_name) VALUES
(1, 'ADMIN'),
(1, 'USER'),
(2, 'USER');

INSERT INTO SOCOM_UI.cycles (cycle_name, cycle_year, start_date, end_date, is_active, status) VALUES
('FY2024 SOCOM Cycle', 2024, '2024-01-01', '2024-12-31', TRUE, 'ACTIVE'),
('FY2025 SOCOM Cycle', 2025, '2025-01-01', '2025-12-31', FALSE, 'PLANNING');

-- Create indexes for better performance
CREATE INDEX idx_users_username ON SOCOM_UI.users(username);
CREATE INDEX idx_users_email ON SOCOM_UI.users(email);
CREATE INDEX idx_cycles_active ON SOCOM_UI.cycles(is_active);
CREATE INDEX idx_cycles_status ON SOCOM_UI.cycles(status);
CREATE INDEX idx_uploads_type_status ON SOCOM_UI.usr_dt_uploads(TYPE, FILE_STATUS);
CREATE INDEX idx_uploads_cycle_user ON SOCOM_UI.usr_dt_uploads(CYCLE_ID, USER_ID);

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON rhombus_db.* TO 'rhombus_user'@'%';
-- FLUSH PRIVILEGES;
