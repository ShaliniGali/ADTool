-- =====================================================
-- Add Sample Data to Existing Columns
-- =====================================================
-- This script adds sample data to existing columns
-- Date: 2025-09-01
-- Purpose: Add sample PB23 data and verify existing columns

USE SOCOM_UI;

-- =====================================================
-- 1. Add sample data to PB23 column in POM table
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
-- 2. Verify the changes
-- =====================================================

-- Show PB23 data in POM table
SELECT 'POM Table with PB23 Data:' as info;
SELECT POM_ID, POM_NAME, POM_YEAR, PB23, POM_CATEGORY, POM_PRIORITY, POM_STATUS FROM POM ORDER BY POM_ID;

-- Show record counts
SELECT 'POM' as table_name, COUNT(*) as record_count FROM POM;

-- =====================================================
-- END OF SCRIPT
-- =====================================================
