-- Add ALL missing tables that are defined in the backup files but missing from the current database
-- This ensures all required tables exist for the SOCOM application

USE SOCOM_UI;

-- Create missing ISS_SUMMARY_2023 table
CREATE TABLE IF NOT EXISTS ISS_SUMMARY_2023 (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CAPABILITY_SPONSOR_CODE` varchar(255) NOT NULL,
  `EVENT_NAME` varchar(500) NOT NULL,
  `DELTA_AMT` decimal(15,2) DEFAULT '0.00',
  `FISCAL_YEAR` int DEFAULT '2023',
  `PROGRAM_NAME` varchar(500) DEFAULT NULL,
  `RESOURCE_CATEGORY_CODE` varchar(100) DEFAULT NULL,
  `EXECUTION_MANAGER_CODE` varchar(100) DEFAULT NULL,
  `PROGRAM_CODE` varchar(100) DEFAULT NULL,
  `EOC_CODE` varchar(100) DEFAULT NULL,
  `OSD_PE_CODE` varchar(100) DEFAULT NULL,
  `APPROVAL_STATUS` varchar(50) DEFAULT 'PENDING',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `idx_capability_sponsor` (`CAPABILITY_SPONSOR_CODE`),
  KEY `idx_event_name` (`EVENT_NAME`),
  KEY `idx_fiscal_year` (`FISCAL_YEAR`),
  KEY `idx_program_name` (`PROGRAM_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create missing LOOKUP_TAG table
CREATE TABLE IF NOT EXISTS LOOKUP_TAG (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TAG_NAME` varchar(100) NOT NULL,
  `TAG_DESCRIPTION` text,
  `TAG_CATEGORY` varchar(50) DEFAULT NULL,
  `IS_ACTIVE` tinyint(1) DEFAULT '1',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TAG_NAME` (`TAG_NAME`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create missing roles table
CREATE TABLE IF NOT EXISTS roles (
  `ID` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `role_description` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create missing user_roles table
CREATE TABLE IF NOT EXISTS user_roles (
  `ID` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `user_role_unique` (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create missing users table (this is critical!)
CREATE TABLE IF NOT EXISTS users (
  `ID` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create missing usr_dt_uploads table
CREATE TABLE IF NOT EXISTS usr_dt_uploads (
  `ID` int NOT NULL AUTO_INCREMENT,
  `upload_type` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `user_id` int NOT NULL,
  `upload_status` enum('PENDING','PROCESSING','COMPLETED','ERROR') DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `user_id` (`user_id`),
  KEY `upload_type` (`upload_type`),
  KEY `upload_status` (`upload_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verify all tables were created
SELECT 'ISS_SUMMARY_2023' as table_name, COUNT(*) as record_count FROM ISS_SUMMARY_2023
UNION ALL
SELECT 'LOOKUP_TAG' as table_name, COUNT(*) as record_count FROM LOOKUP_TAG
UNION ALL
SELECT 'roles' as table_name, COUNT(*) as record_count FROM roles
UNION ALL
SELECT 'user_roles' as table_name, COUNT(*) as record_count FROM user_roles
UNION ALL
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'usr_dt_uploads' as table_name, COUNT(*) as record_count FROM usr_dt_uploads;
