-- Comprehensive SOCOM Database Population Script
-- This script populates all necessary tables with proper seed data

USE SOCOM_UI;

-- First, let's populate the core lookup tables

-- Populate LOOKUP_SPONSOR table
INSERT IGNORE INTO LOOKUP_SPONSOR (SPONSOR_CODE, SPONSOR_TITLE, SPONSOR_TYPE) VALUES
('SORDAC', 'Special Operations Research, Development and Acquisition Center', 'CAPABILITY'),
('USSOCOM', 'United States Special Operations Command', 'COMMAND'),
('NSWC', 'Naval Surface Warfare Center', 'NAVAL'),
('MARSOC', 'Marine Forces Special Operations Command', 'MARINE'),
('AFSOC', 'Air Force Special Operations Command', 'AIR_FORCE'),
('ARSOF', 'Army Special Operations Forces', 'ARMY'),
('JSOC', 'Joint Special Operations Command', 'JOINT'),
('TSOC', 'Theater Special Operations Command', 'THEATER'),
('SOFIC', 'Special Operations Forces Industry Conference', 'INDUSTRY');

-- Populate LOOKUP_ASSESSMENT_AREA table
INSERT IGNORE INTO LOOKUP_ASSESSMENT_AREA (ASSESSMENT_AREA_CODE, ASSESSMENT_AREA, AREA_NAME, AREA_DESCRIPTION, AREA_CATEGORY) VALUES
('A', 'Acquisition', 'Acquisition', 'Acquisition and procurement related activities', 'ACQUISITION'),
('B', 'Budget', 'Budget', 'Budget and financial management', 'FINANCIAL'),
('C', 'Capability', 'Capability', 'Capability development and sustainment', 'CAPABILITY'),
('D', 'Development', 'Development', 'Research and development activities', 'RESEARCH'),
('E', 'Execution', 'Execution', 'Program execution and management', 'MANAGEMENT'),
('F', 'Force', 'Force', 'Force structure and readiness', 'FORCE'),
('G', 'Governance', 'Governance', 'Policy and governance activities', 'POLICY'),
('H', 'Human Capital', 'Human Capital', 'Personnel and training', 'PERSONNEL'),
('I', 'Infrastructure', 'Infrastructure', 'Facilities and infrastructure', 'INFRASTRUCTURE'),
('J', 'Joint', 'Joint', 'Joint operations and interoperability', 'JOINT');

-- Populate LOOKUP_PROGRAM table
INSERT IGNORE INTO LOOKUP_PROGRAM (PROGRAM_NAME, PROGRAM_CODE, PROGRAM_DESCRIPTION, PROGRAM_STATUS, STORM_ID) VALUES
('Special Operations Aviation', 'SOA001', 'Special Operations Aviation Program', 'ACTIVE', 1),
('Special Operations Ground', 'SOG001', 'Special Operations Ground Forces Program', 'ACTIVE', 1),
('Special Operations Maritime', 'SOM001', 'Special Operations Maritime Program', 'ACTIVE', 1),
('Special Operations Intelligence', 'SOI001', 'Special Operations Intelligence Program', 'ACTIVE', 1),
('Special Operations Communications', 'SOC001', 'Special Operations Communications Program', 'ACTIVE', 1),
('Special Operations Medical', 'SOM001', 'Special Operations Medical Program', 'ACTIVE', 1),
('Special Operations Training', 'SOT001', 'Special Operations Training Program', 'ACTIVE', 1),
('Special Operations Logistics', 'SOL001', 'Special Operations Logistics Program', 'ACTIVE', 1),
('Special Operations Technology', 'SOT001', 'Special Operations Technology Program', 'ACTIVE', 1),
('Special Operations Research', 'SOR001', 'Special Operations Research Program', 'ACTIVE', 1);

-- Populate LOOKUP_RESOURCE_CATEGORY table
INSERT IGNORE INTO LOOKUP_RESOURCE_CATEGORY (RESOURCE_CATEGORY_CODE, RESOURCE_CATEGORY, CATEGORY_NAME, CATEGORY_DESCRIPTION, CATEGORY_TYPE, CATEGORY_PRIORITY) VALUES
('PERS', 'PERSONNEL', 'Personnel', 'Human resources and personnel costs', 'PERSONNEL', 'HIGH'),
('EQUIP', 'EQUIPMENT', 'Equipment', 'Military equipment and hardware', 'EQUIPMENT', 'HIGH'),
('FAC', 'FACILITY', 'Facilities', 'Buildings, infrastructure, and facilities', 'FACILITY', 'MEDIUM'),
('TECH', 'TECHNOLOGY', 'Technology', 'Information technology and systems', 'TECHNOLOGY', 'HIGH'),
('BUDG', 'BUDGET', 'Budget', 'Budget and financial resources', 'BUDGET', 'CRITICAL'),
('TRAIN', 'TRAINING', 'Training', 'Training and education programs', 'PERSONNEL', 'MEDIUM'),
('MAINT', 'MAINTENANCE', 'Maintenance', 'Equipment and facility maintenance', 'EQUIPMENT', 'MEDIUM'),
('R&D', 'RESEARCH', 'Research & Development', 'Research and development activities', 'TECHNOLOGY', 'HIGH'),
('OPS', 'OPERATIONS', 'Operations', 'Operational activities and support', 'PERSONNEL', 'CRITICAL'),
('ADMIN', 'ADMINISTRATIVE', 'Administrative', 'Administrative and support functions', 'PERSONNEL', 'LOW');

-- Populate LOOKUP_STORM table
INSERT IGNORE INTO LOOKUP_STORM (STORM_NAME, STORM_DESCRIPTION, IS_ACTIVE) VALUES
('STORM_ALPHA', 'Primary Special Operations STORM', 1),
('STORM_BRAVO', 'Secondary Special Operations STORM', 1),
('STORM_CHARLIE', 'Tertiary Special Operations STORM', 1);

-- Now populate the main data tables

-- Populate RESOURCE_CONSTRAINED_COA_2024 with comprehensive data
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
    EOC_CODE, 
    OSD_PE_CODE, 
    APPROVAL_STATUS, 
    PROGRAM_GROUP, 
    RESOURCE_K, 
    POM_SPONSOR_CODE, 
    OSD_PROGRAM_ELEMENT_CODE, 
    PROGRAM_ID
) VALUES
-- Aviation Programs
('SORDAC', 'A', 'EVENT_001', 5000000.00, 2024, 'Special Operations Aviation', 'EQUIP', 'EM001', 'SOA001', 'EOC001', 'PE0600000A', 'APPROVED', 'AVIATION', 5000000.00, 'SORDAC', 'PE0600000A', 1),
('SORDAC', 'A', 'EVENT_002', 3000000.00, 2024, 'Special Operations Aviation', 'TECH', 'EM001', 'SOA001', 'EOC001', 'PE0600000A', 'APPROVED', 'AVIATION', 3000000.00, 'SORDAC', 'PE0600000A', 1),
('SORDAC', 'A', 'EVENT_003', 2000000.00, 2024, 'Special Operations Aviation', 'MAINT', 'EM001', 'SOA001', 'EOC001', 'PE0600000A', 'APPROVED', 'AVIATION', 2000000.00, 'SORDAC', 'PE0600000A', 1),

-- Ground Forces Programs
('USSOCOM', 'C', 'EVENT_004', 4000000.00, 2024, 'Special Operations Ground', 'EQUIP', 'EM002', 'SOG001', 'EOC002', 'PE0600000B', 'APPROVED', 'GROUND', 4000000.00, 'USSOCOM', 'PE0600000B', 2),
('USSOCOM', 'C', 'EVENT_005', 2500000.00, 2024, 'Special Operations Ground', 'PERS', 'EM002', 'SOG001', 'EOC002', 'PE0600000B', 'APPROVED', 'GROUND', 2500000.00, 'USSOCOM', 'PE0600000B', 2),
('USSOCOM', 'C', 'EVENT_006', 1500000.00, 2024, 'Special Operations Ground', 'TRAIN', 'EM002', 'SOG001', 'EOC002', 'PE0600000B', 'APPROVED', 'GROUND', 1500000.00, 'USSOCOM', 'PE0600000B', 2),

-- Maritime Programs
('NSWC', 'C', 'EVENT_007', 3500000.00, 2024, 'Special Operations Maritime', 'EQUIP', 'EM003', 'SOM001', 'EOC003', 'PE0600000C', 'APPROVED', 'MARITIME', 3500000.00, 'NSWC', 'PE0600000C', 3),
('NSWC', 'C', 'EVENT_008', 2000000.00, 2024, 'Special Operations Maritime', 'FAC', 'EM003', 'SOM001', 'EOC003', 'PE0600000C', 'APPROVED', 'MARITIME', 2000000.00, 'NSWC', 'PE0600000C', 3),
('NSWC', 'C', 'EVENT_009', 1000000.00, 2024, 'Special Operations Maritime', 'TECH', 'EM003', 'SOM001', 'EOC003', 'PE0600000C', 'APPROVED', 'MARITIME', 1000000.00, 'NSWC', 'PE0600000C', 3),

-- Intelligence Programs
('USSOCOM', 'I', 'EVENT_010', 6000000.00, 2024, 'Special Operations Intelligence', 'TECH', 'EM004', 'SOI001', 'EOC004', 'PE0600000D', 'APPROVED', 'INTELLIGENCE', 6000000.00, 'USSOCOM', 'PE0600000D', 4),
('USSOCOM', 'I', 'EVENT_011', 4000000.00, 2024, 'Special Operations Intelligence', 'PERS', 'EM004', 'SOI001', 'EOC004', 'PE0600000D', 'APPROVED', 'INTELLIGENCE', 4000000.00, 'USSOCOM', 'PE0600000D', 4),
('USSOCOM', 'I', 'EVENT_012', 2000000.00, 2024, 'Special Operations Intelligence', 'EQUIP', 'EM004', 'SOI001', 'EOC004', 'PE0600000D', 'APPROVED', 'INTELLIGENCE', 2000000.00, 'USSOCOM', 'PE0600000D', 4),

-- Communications Programs
('SORDAC', 'T', 'EVENT_013', 4500000.00, 2024, 'Special Operations Communications', 'TECH', 'EM005', 'SOC001', 'EOC005', 'PE0600000E', 'APPROVED', 'COMMUNICATIONS', 4500000.00, 'SORDAC', 'PE0600000E', 5),
('SORDAC', 'T', 'EVENT_014', 3000000.00, 2024, 'Special Operations Communications', 'EQUIP', 'EM005', 'SOC001', 'EOC005', 'PE0600000E', 'APPROVED', 'COMMUNICATIONS', 3000000.00, 'SORDAC', 'PE0600000E', 5),
('SORDAC', 'T', 'EVENT_015', 1500000.00, 2024, 'Special Operations Communications', 'MAINT', 'EM005', 'SOC001', 'EOC005', 'PE0600000E', 'APPROVED', 'COMMUNICATIONS', 1500000.00, 'SORDAC', 'PE0600000E', 5);

-- Populate ISS_SUMMARY_2024 with data
INSERT IGNORE INTO ISS_SUMMARY_2024 (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2024, 10000000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2024, 8000000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2024, 6500000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2024, 12000000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2024, 9000000.00, 'COMMUNICATIONS');

-- Populate ISS_SUMMARY_2025 with data
INSERT IGNORE INTO ISS_SUMMARY_2025 (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2025, 11000000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2025, 8800000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2025, 7150000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2025, 13200000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2025, 9900000.00, 'COMMUNICATIONS');

-- Populate ZBT_SUMMARY_2024 with data
INSERT IGNORE INTO ZBT_SUMMARY_2024 (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2024, 5000000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2024, 4000000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2024, 3250000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2024, 6000000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2024, 4500000.00, 'COMMUNICATIONS');

-- Populate ZBT_SUMMARY_2025 with data
INSERT IGNORE INTO ZBT_SUMMARY_2025 (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2025, 5500000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2025, 4400000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2025, 3575000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2025, 6600000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2025, 4950000.00, 'COMMUNICATIONS');

-- Populate DT_PB_COMPARISON with data
INSERT IGNORE INTO DT_PB_COMPARISON (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2024, 10000000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2024, 8000000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2024, 6500000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2024, 12000000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2024, 9000000.00, 'COMMUNICATIONS');

-- Populate DT_BUDGET_EXECUTION with data
INSERT IGNORE INTO DT_BUDGET_EXECUTION (
    PROGRAM_CODE, 
    PROGRAM_NAME, 
    CAPABILITY_SPONSOR_CODE, 
    ASSESSMENT_AREA_CODE, 
    FISCAL_YEAR, 
    DELTA_AMT, 
    PROGRAM_GROUP
) VALUES
('SOA001', 'Special Operations Aviation', 'SORDAC', 'A', 2024, 9500000.00, 'AVIATION'),
('SOG001', 'Special Operations Ground', 'USSOCOM', 'C', 2024, 7600000.00, 'GROUND'),
('SOM001', 'Special Operations Maritime', 'NSWC', 'C', 2024, 6175000.00, 'MARITIME'),
('SOI001', 'Special Operations Intelligence', 'USSOCOM', 'I', 2024, 11400000.00, 'INTELLIGENCE'),
('SOC001', 'Special Operations Communications', 'SORDAC', 'T', 2024, 8550000.00, 'COMMUNICATIONS');

-- Verify the data was inserted
SELECT 'Data Population Complete' as status;
SELECT 'LOOKUP_SPONSOR count:' as table_name, COUNT(*) as count FROM LOOKUP_SPONSOR;
SELECT 'LOOKUP_PROGRAM count:' as table_name, COUNT(*) as count FROM LOOKUP_PROGRAM;
SELECT 'LOOKUP_RESOURCE_CATEGORY count:' as table_name, COUNT(*) as count FROM LOOKUP_RESOURCE_CATEGORY;
SELECT 'RESOURCE_CONSTRAINED_COA_2024 count:' as table_name, COUNT(*) as count FROM RESOURCE_CONSTRAINED_COA_2024;
SELECT 'ISS_SUMMARY_2024 count:' as table_name, COUNT(*) as count FROM ISS_SUMMARY_2024;
SELECT 'ZBT_SUMMARY_2024 count:' as table_name, COUNT(*) as count FROM ZBT_SUMMARY_2024;
