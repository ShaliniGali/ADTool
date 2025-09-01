-- Fix SOCOM Sponsor Mapping Issue
-- This script creates the proper mapping between display names and database values

USE SOCOM_UI;

-- Create a sponsor mapping table to handle the display vs database value mismatch
CREATE TABLE IF NOT EXISTS SPONSOR_MAPPING (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    display_name VARCHAR(255) NOT NULL UNIQUE,
    database_value VARCHAR(255) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_display_name (display_name),
    INDEX idx_database_value (database_value)
);

-- Insert the mapping data
INSERT INTO SPONSOR_MAPPING (display_name, database_value, description) VALUES
('SOF AT&L', 'SORDAC', 'SOF AT&L capability sponsor'),
('USSOCOM', 'USSOCOM', 'USSOCOM capability sponsor'),
('JSOC', 'JSOC', 'JSOC capability sponsor'),
('NAVSPECWARCOM', 'NAVSPECWARCOM', 'NAVSPECWARCOM capability sponsor'),
('AFSOC', 'AFSOC', 'AFSOC capability sponsor')
ON DUPLICATE KEY UPDATE 
    database_value = VALUES(database_value),
    description = VALUES(description);

-- Create a view to handle the sponsor mapping automatically
CREATE OR REPLACE VIEW SPONSOR_DISPLAY_VIEW AS
SELECT 
    sm.display_name,
    sm.database_value,
    z.*
FROM ZBT_SUMMARY_2025 z
JOIN SPONSOR_MAPPING sm ON z.CAPABILITY_SPONSOR_CODE = sm.database_value
WHERE sm.active = TRUE

UNION ALL

SELECT 
    z.CAPABILITY_SPONSOR_CODE as display_name,
    z.CAPABILITY_SPONSOR_CODE as database_value,
    z.*
FROM ZBT_SUMMARY_2025 z
WHERE z.CAPABILITY_SPONSOR_CODE NOT IN (SELECT database_value FROM SPONSOR_MAPPING WHERE active = TRUE);

-- Verify the setup
SELECT 'SPONSOR_MAPPING' as table_name, COUNT(*) as record_count FROM SPONSOR_MAPPING;

-- Show the mapping
SELECT display_name, database_value, description FROM SPONSOR_MAPPING WHERE active = TRUE;
