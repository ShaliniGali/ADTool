-- Final SOCOM Seed Data Script
-- This script uses the structure from the sql folder files but with INSERT IGNORE
-- to avoid duplicate key errors

USE SOCOM_UI;

-- First, let's populate the main SOCOM tables with basic data
-- This matches what the PHP application expects

-- Populate LOOKUP_PROGRAM with basic program data (if empty)
INSERT IGNORE INTO LOOKUP_PROGRAM (PROGRAM_CODE, PROGRAM_NAME, PROGRAM_GROUP, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE) VALUES
('PROG001', 'Special Operations Research Program', 'RESEARCH', 'SORDAC', 'RESEARCH'),
('PROG002', 'Special Operations Acquisition Program', 'ACQUISITION', 'SORDAC', 'ACQUISITION'),
('PROG003', 'Joint Special Operations Program', 'JOINT', 'USSOCOM', 'RESEARCH'),
('PROG004', 'Special Operations Capability Program', 'CAPABILITY', 'USSOCOM', 'ACQUISITION'),
('PROG005', 'Technology Development Program', 'TECHNOLOGY', 'NSWC', 'RESEARCH');

-- Populate LOOKUP_STORM with basic storm data (if empty)
INSERT IGNORE INTO LOOKUP_STORM (TOTAL_SCORE) VALUES
(85.5),
(92.3),
(78.9),
(88.2),
(91.7);

-- Populate RESOURCE_CONSTRAINED_COA_2024 with basic data (if empty)
INSERT IGNORE INTO RESOURCE_CONSTRAINED_COA_2024 (
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE, 
    EXECUTION_MANAGER_CODE, 
    PROGRAM_CODE, 
    PROGRAM_GROUP, 
    RESOURCE_K, 
    POM_SPONSOR_CODE, 
    OSD_PROGRAM_ELEMENT_CODE, 
    PROGRAM_ID
) VALUES
('SORDAC', 'RESEARCH', 'SOF AT&L Program 1', 1500000.00, 2024, 'Special Operations Research Program', 'RESEARCH_DEV', 'EM001', 'PROG001', 'RESEARCH', 1500000.00, 'SOF_ATL', 'OSD001', 1),
('SORDAC', 'ACQUISITION', 'SOF AT&L Program 2', 2200000.00, 2024, 'Special Operations Acquisition Program', 'ACQUISITION', 'EM002', 'PROG002', 'ACQUISITION', 2200000.00, 'SOF_ATL', 'OSD002', 2),
('USSOCOM', 'RESEARCH', 'USSOCOM Program 1', 2800000.00, 2024, 'Joint Special Operations Program', 'RESEARCH_DEV', 'EM005', 'PROG003', 'JOINT', 2800000.00, 'USSOCOM_POM', 'OSD005', 3),
('USSOCOM', 'ACQUISITION', 'USSOCOM Program 2', 3200000.00, 2024, 'Special Operations Capability Program', 'ACQUISITION', 'EM006', 'PROG004', 'CAPABILITY', 3200000.00, 'USSOCOM_POM', 'OSD006', 4),
('NSWC', 'RESEARCH', 'NSWC Program 1', 1800000.00, 2024, 'Technology Development Program', 'TECHNOLOGY', 'EM007', 'PROG005', 'TECHNOLOGY', 1800000.00, 'NSWC_POM', 'OSD007', 5);

-- Populate ISS_SUMMARY_2024 with basic data (if empty)
INSERT IGNORE INTO ISS_SUMMARY_2024 (
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE, 
    EXECUTION_MANAGER_CODE, 
    PROGRAM_CODE, 
    PROGRAM_GROUP, 
    RESOURCE_K, 
    POM_SPONSOR_CODE, 
    OSD_PROGRAM_ELEMENT_CODE, 
    PROGRAM_ID
) VALUES
('SORDAC', 'RESEARCH', 'SOF AT&L Issue Event 1', 800000.00, 2024, 'Special Operations Issue Program', 'RESEARCH_DEV', 'EM001', 'PROG001', 'RESEARCH', 800000.00, 'SOF_ATL', 'OSD001', 1),
('USSOCOM', 'RESEARCH', 'USSOCOM Issue Event 1', 1100000.00, 2024, 'Joint Special Operations Issue Program', 'RESEARCH_DEV', 'EM005', 'PROG003', 'JOINT', 1100000.00, 'USSOCOM_POM', 'OSD005', 3);

-- Populate ZBT_SUMMARY_2024 with basic data (if empty)
INSERT IGNORE INTO ZBT_SUMMARY_2024 (
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE, 
    EXECUTION_MANAGER_CODE, 
    PROGRAM_CODE, 
    PROGRAM_GROUP, 
    RESOURCE_K, 
    POM_SPONSOR_CODE, 
    OSD_PROGRAM_ELEMENT_CODE, 
    PROGRAM_ID
) VALUES
('SORDAC', 'RESEARCH', 'SOF AT&L ZBT Event 1', 900000.00, 2024, 'Special Operations ZBT Program', 'RESEARCH_DEV', 'EM001', 'PROG001', 'RESEARCH', 900000.00, 'SOF_ATL', 'OSD001', 1),
('USSOCOM', 'RESEARCH', 'USSOCOM ZBT Event 1', 1000000.00, 2024, 'Joint Special Operations ZBT Program', 'RESEARCH_DEV', 'EM005', 'PROG003', 'JOINT', 1000000.00, 'USSOCOM_POM', 'OSD005', 3);

-- Now let's add some sample data to USR_DT_UPLOADS table
INSERT IGNORE INTO USR_DT_UPLOADS (
    TYPE, 
    CYCLE_ID, 
    S3_PATH, 
    FILE_NAME, 
    VERSION, 
    TITLE, 
    DESCRIPTION, 
    USER_ID, 
    FILE_STATUS, 
    FILE_SIZE, 
    MIME_TYPE
) VALUES
('DATABASE', 1, '/uploads/database/sample_data_2024.xlsx', 'sample_data_2024.xlsx', '1.0', 'FY2024 Sample Data', 'Sample database for FY2024 testing', 1, 'COMPLETED', 2048576, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
('PROGRAM_ALIGNMENT', 1, '/uploads/program/socom_programs_2024.xlsx', 'socom_programs_2024.xlsx', '1.0', 'SOCOM Programs 2024', 'Program alignment data for FY2024', 1, 'COMPLETED', 1536000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
('DOCUMENT', 1, '/uploads/documents/socom_planning_2024.pdf', 'socom_planning_2024.pdf', '1.0', 'SOCOM Planning Document 2024', 'Strategic planning document for FY2024', 1, 'COMPLETED', 5120000, 'application/pdf');

-- Add some sample data to processing_pipeline
INSERT IGNORE INTO processing_pipeline (upload_type, status) VALUES
('DATABASE', 'COMPLETED'),
('PROGRAM_ALIGNMENT', 'COMPLETED'),
('DOCUMENT', 'COMPLETED');

-- Add some sample data to git_tracking
INSERT IGNORE INTO git_tracking (data_type, reference_id, user_id, git_hash, git_message) VALUES
('UPLOAD_FILE', 1, 1, 'a1b2c3d4e5f6', 'Initial database upload'),
('PROCESSING_RESULT', 1, 1, 'f6e5d4c3b2a1', 'Database processing completed'),
('METADATA_UPDATE', 1, 1, '1234567890ab', 'Metadata updated for database');

-- Show final record counts for verification
SELECT 'LOOKUP_PROGRAM' as table_name, COUNT(*) as record_count FROM LOOKUP_PROGRAM
UNION ALL
SELECT 'LOOKUP_STORM' as table_name, COUNT(*) as record_count FROM LOOKUP_STORM
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2024' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2024
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2025' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2025
UNION ALL
SELECT 'ISS_SUMMARY_2024' as table_name, COUNT(*) as record_count FROM ISS_SUMMARY_2024
UNION ALL
SELECT 'ZBT_SUMMARY_2024' as table_name, COUNT(*) as record_count FROM ZBT_SUMMARY_2024
UNION ALL
SELECT 'USR_DT_UPLOADS' as table_name, COUNT(*) as record_count FROM USR_DT_UPLOADS
UNION ALL
SELECT 'processing_pipeline' as table_name, COUNT(*) as record_count FROM processing_pipeline
UNION ALL
SELECT 'git_tracking' as table_name, COUNT(*) as record_count FROM git_tracking;



