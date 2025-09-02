-- Populate SOCOM Main Data Tables with Proper Seed Data
-- This script uses the correct data structure from the SQL folder

USE SOCOM_UI;

-- First, let's add data to RESOURCE_CONSTRAINED_COA_2024 (the main table for the program list)
INSERT INTO RESOURCE_CONSTRAINED_COA_2024 (
    CAPABILITY_SPONSOR_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE, 
    EXECUTION_MANAGER_CODE, 
    PROGRAM_CODE, 
    EOC_CODE, 
    OSD_PE_CODE, 
    APPROVAL_STATUS
) VALUES
-- SOF AT&L Programs
('SORDAC', 'SOF AT&L Program 1', 1500000.00, 2024, 'Special Operations Research Program', 'RESEARCH_DEV', 'EM001', 'PROG001', 'EOC001', 'OSD001', 'APPROVED'),
('SORDAC', 'SOF AT&L Program 2', 2200000.00, 2024, 'Special Operations Acquisition Program', 'ACQUISITION', 'EM002', 'PROG002', 'EOC002', 'OSD002', 'APPROVED'),
('SORDAC', 'SOF AT&L Program 3', 1800000.00, 2024, 'Special Operations Operations Program', 'OPERATIONS', 'EM003', 'PROG003', 'EOC003', 'OSD003', 'PENDING'),
('SORDAC', 'SOF AT&L Program 4', 950000.00, 2024, 'Special Operations Maintenance Program', 'MAINTENANCE', 'EM004', 'PROG004', 'EOC004', 'OSD004', 'APPROVED'),

-- USSOCOM Programs
('USSOCOM', 'USSOCOM Program 1', 2800000.00, 2024, 'Joint Special Operations Program', 'RESEARCH_DEV', 'EM005', 'PROG005', 'EOC005', 'OSD005', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 2', 3200000.00, 2024, 'Special Operations Capability Program', 'ACQUISITION', 'EM006', 'PROG006', 'EOC006', 'OSD006', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 3', 1950000.00, 2024, 'Special Operations Training Program', 'OPERATIONS', 'EM007', 'PROG007', 'EOC007', 'OSD007', 'PENDING'),
('USSOCOM', 'USSOCOM Program 4', 1250000.00, 2024, 'Special Operations Support Program', 'MAINTENANCE', 'EM008', 'PROG008', 'EOC008', 'OSD008', 'APPROVED'),

-- Additional Programs for variety
('SORDAC', 'SOF AT&L Program 5', 2100000.00, 2024, 'Advanced Technology Program', 'RESEARCH_DEV', 'EM009', 'PROG009', 'EOC009', 'OSD009', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 5', 1750000.00, 2024, 'Strategic Communications Program', 'OPERATIONS', 'EM010', 'PROG010', 'EOC010', 'OSD010', 'PENDING'),
('SORDAC', 'SOF AT&L Program 6', 1350000.00, 2024, 'Logistics Support Program', 'MAINTENANCE', 'EM011', 'PROG011', 'EOC011', 'OSD011', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 6', 2400000.00, 2024, 'Intelligence Program', 'RESEARCH_DEV', 'EM012', 'PROG012', 'EOC012', 'OSD012', 'APPROVED');

-- Now let's add data to RESOURCE_CONSTRAINED_COA_2025 as well
INSERT INTO RESOURCE_CONSTRAINED_COA_2025 (
    CAPABILITY_SPONSOR_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE, 
    EXECUTION_MANAGER_CODE, 
    PROGRAM_CODE, 
    EOC_CODE, 
    OSD_PE_CODE, 
    APPROVAL_STATUS
) VALUES
-- SOF AT&L Programs 2025
('SORDAC', 'SOF AT&L Program 1 FY25', 1600000.00, 2025, 'Special Operations Research Program FY25', 'RESEARCH_DEV', 'EM001', 'PROG001', 'EOC001', 'OSD001', 'APPROVED'),
('SORDAC', 'SOF AT&L Program 2 FY25', 2350000.00, 2025, 'Special Operations Acquisition Program FY25', 'ACQUISITION', 'EM002', 'PROG002', 'EOC002', 'OSD002', 'APPROVED'),
('SORDAC', 'SOF AT&L Program 3 FY25', 1900000.00, 2025, 'Special Operations Operations Program FY25', 'OPERATIONS', 'EM003', 'PROG003', 'EOC003', 'OSD003', 'PENDING'),

-- USSOCOM Programs 2025
('USSOCOM', 'USSOCOM Program 1 FY25', 2950000.00, 2025, 'Joint Special Operations Program FY25', 'RESEARCH_DEV', 'EM005', 'PROG005', 'EOC005', 'OSD005', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 2 FY25', 3350000.00, 2025, 'Special Operations Capability Program FY25', 'ACQUISITION', 'EM006', 'PROG006', 'EOC006', 'OSD006', 'APPROVED'),
('USSOCOM', 'USSOCOM Program 3 FY25', 2000000.00, 2025, 'Special Operations Training Program FY25', 'OPERATIONS', 'EM007', 'PROG007', 'EOC007', 'OSD007', 'PENDING');

-- Let's also add some data to ISS_SUMMARY_2024 for completeness
INSERT INTO ISS_SUMMARY_2024 (
    CAPABILITY_SPONSOR_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE
) VALUES
('SORDAC', 'SOF AT&L Issue Event 1 FY24', 800000.00, 2024, 'Special Operations Issue Program', 'RESEARCH_DEV'),
('SORDAC', 'SOF AT&L Issue Event 2 FY24', 1200000.00, 2024, 'Special Operations Issue Program 2', 'ACQUISITION'),
('USSOCOM', 'USSOCOM Issue Event 1 FY24', 1100000.00, 2024, 'Joint Special Operations Issue Program', 'RESEARCH_DEV'),
('USSOCOM', 'USSOCOM Issue Event 2 FY24', 1400000.00, 2024, 'Joint Special Operations Issue Program 2', 'OPERATIONS');

-- Let's also add some data to ZBT_SUMMARY_2024 for completeness
INSERT INTO ZBT_SUMMARY_2024 (
    CAPABILITY_SPONSOR_CODE, 
    EVENT_NAME, 
    DELTA_AMT, 
    FISCAL_YEAR, 
    PROGRAM_NAME, 
    RESOURCE_CATEGORY_CODE
) VALUES
('SORDAC', 'SOF AT&L ZBT Event 1 FY24', 900000.00, 2024, 'Special Operations ZBT Program', 'RESEARCH_DEV'),
('SORDAC', 'SOF AT&L ZBT Event 2 FY24', 1300000.00, 2024, 'Special Operations ZBT Program 2', 'ACQUISITION'),
('USSOCOM', 'USSOCOM ZBT Event 1 FY24', 1000000.00, 2024, 'Joint Special Operations ZBT Program', 'RESEARCH_DEV'),
('USSOCOM', 'USSOCOM ZBT Event 2 FY24', 1500000.00, 2024, 'Joint Special Operations ZBT Program 2', 'OPERATIONS');

-- Verify the data was inserted
SELECT 'RESOURCE_CONSTRAINED_COA_2024' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2024
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2025' as table_name, COUNT(*) as record_count FROM RESOURCE_CONSTRAINED_COA_2025
UNION ALL
SELECT 'ISS_SUMMARY_2024' as table_name, COUNT(*) as record_count FROM ISS_SUMMARY_2024
UNION ALL
SELECT 'ZBT_SUMMARY_2024' as table_name, COUNT(*) as record_count FROM ZBT_SUMMARY_2024;

-- Show sample data from the main table
SELECT 'Sample RESOURCE_CONSTRAINED_COA_2024 Data:' as info;
SELECT CAPABILITY_SPONSOR_CODE, EVENT_NAME, PROGRAM_NAME, RESOURCE_CATEGORY_CODE, DELTA_AMT 
FROM RESOURCE_CONSTRAINED_COA_2024 
LIMIT 5;


