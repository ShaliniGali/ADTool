-- =====================================================
-- Add Missing Database Columns for SOCOM Application
-- =====================================================
-- This script adds missing columns identified from error logs
-- Date: 2025-09-01
-- Purpose: Fix DataTables Ajax errors and database schema issues

USE SOCOM_UI;

-- =====================================================
-- 1. Add COA_TYPE column to user saved COA tables
-- =====================================================

-- Add COA_TYPE to USR_LOOKUP_USER_SAVED_COA
ALTER TABLE USR_LOOKUP_USER_SAVED_COA 
ADD COLUMN COA_TYPE VARCHAR(50) DEFAULT 'STANDARD' COMMENT 'Type of COA (STANDARD, RESOURCE_CONSTRAINED, etc.)';

-- Add COA_TYPE to USR_LOOKUP_USER_SHARED_COA  
ALTER TABLE USR_LOOKUP_USER_SHARED_COA
ADD COLUMN COA_TYPE VARCHAR(50) DEFAULT 'STANDARD' COMMENT 'Type of COA (STANDARD, RESOURCE_CONSTRAINED, etc.)';

-- =====================================================
-- 2. Add PB23 column to POM table
-- =====================================================

-- Add PB23 column to POM table (assuming this is a POM position code)
ALTER TABLE POM
ADD COLUMN PB23 VARCHAR(50) DEFAULT NULL COMMENT 'PB23 Position Code';

-- =====================================================
-- 3. ID columns already exist in RESOURCE_CONSTRAINED_COA tables
-- =====================================================

-- Note: ID columns already exist in RESOURCE_CONSTRAINED_COA_2024 and RESOURCE_CONSTRAINED_COA_2025
-- The 'cr.ID' and 'n.ID' errors in logs are likely due to table alias issues in queries

-- =====================================================
-- 4. Add indexes for performance
-- =====================================================

-- Add index on COA_TYPE for faster queries
CREATE INDEX idx_coa_type_saved ON USR_LOOKUP_USER_SAVED_COA(COA_TYPE);
CREATE INDEX idx_coa_type_shared ON USR_LOOKUP_USER_SHARED_COA(COA_TYPE);

-- Add index on PB23 for faster queries
CREATE INDEX idx_pb23 ON POM(PB23);

-- =====================================================
-- 5. Update existing records with default values
-- =====================================================

-- Update existing COA records with default COA_TYPE
UPDATE USR_LOOKUP_USER_SAVED_COA 
SET COA_TYPE = 'STANDARD' 
WHERE COA_TYPE IS NULL;

UPDATE USR_LOOKUP_USER_SHARED_COA
SET COA_TYPE = 'STANDARD' 
WHERE COA_TYPE IS NULL;

-- =====================================================
-- 6. Add sample data to PB23 column
-- =====================================================

-- Add sample PB23 position codes to existing POM records
UPDATE POM SET PB23 = 'PB23-001' WHERE POM_ID = 1;
UPDATE POM SET PB23 = 'PB23-002' WHERE POM_ID = 2;
UPDATE POM SET PB23 = 'PB23-003' WHERE POM_ID = 3;
UPDATE POM SET PB23 = 'PB23-004' WHERE POM_ID = 4;

-- Add additional sample POM records with PB23 codes
INSERT INTO POM (POM_NAME, POM_DESCRIPTION, POM_CATEGORY, POM_PRIORITY, POM_STATUS, POM_YEAR, PB23) VALUES
('Personnel Training POM', 'Training and development for personnel', 'PERSONNEL', 'HIGH', 'APPROVED', 2024, 'PB23-005'),
('Equipment Procurement POM', 'New equipment acquisition', 'EQUIPMENT', 'CRITICAL', 'FUNDED', 2024, 'PB23-006'),
('Infrastructure Upgrade POM', 'Infrastructure modernization', 'INFRASTRUCTURE', 'MEDIUM', 'ANALYZING', 2025, 'PB23-007'),
('Technology Enhancement POM', 'Technology system upgrades', 'TECHNOLOGY', 'HIGH', 'IDENTIFIED', 2025, 'PB23-008');

-- =====================================================
-- 6. Verify the changes
-- =====================================================

-- Show the updated table structures
DESCRIBE USR_LOOKUP_USER_SAVED_COA;
DESCRIBE USR_LOOKUP_USER_SHARED_COA;
DESCRIBE POM;

-- Show record counts
SELECT 'USR_LOOKUP_USER_SAVED_COA' as table_name, COUNT(*) as record_count FROM USR_LOOKUP_USER_SAVED_COA
UNION ALL
SELECT 'USR_LOOKUP_USER_SHARED_COA' as table_name, COUNT(*) as record_count FROM USR_LOOKUP_USER_SHARED_COA
UNION ALL  
SELECT 'POM' as table_name, COUNT(*) as record_count FROM POM
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2024' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2024
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2025' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2025;

-- Show PB23 data in POM table
SELECT 'POM Table with PB23 Data:' as info;
SELECT POM_ID, POM_NAME, POM_YEAR, PB23, POM_CATEGORY, POM_PRIORITY, POM_STATUS FROM POM ORDER BY POM_ID;

-- =====================================================
-- END OF SCRIPT
-- =====================================================
