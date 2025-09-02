-- =====================================================
-- SOCOM Database - Complete Lookup Tables Data
-- Tables_Complete.sql
-- =====================================================

USE SOCOM_UI;

-- =====================================================
-- LOOKUP SPONSOR DATA
-- =====================================================

INSERT INTO `LOOKUP_SPONSOR` (`SPONSOR_CODE`, `SPONSOR_TITLE`, `SPONSOR_DESCRIPTION`, `IS_ACTIVE`) VALUES
('USSOCOM', 'United States Special Operations Command', 'Primary command for special operations forces', 1),
('SORDAC', 'Special Operations Research, Development and Acquisition Center', 'Research and development for special operations', 1),
('AT&L', 'Acquisition, Technology and Logistics', 'Acquisition and logistics support', 1),
('JCS', 'Joint Chiefs of Staff', 'Military advisory body to the President', 1),
('OSD', 'Office of the Secretary of Defense', 'Civilian leadership of the Department of Defense', 1),
('USASOC', 'United States Army Special Operations Command', 'Army special operations forces', 1),
('NAVSPECWARCOM', 'Naval Special Warfare Command', 'Navy special operations forces', 1),
('AFSOC', 'Air Force Special Operations Command', 'Air Force special operations forces', 1),
('MARSOC', 'Marine Corps Forces Special Operations Command', 'Marine Corps special operations forces', 1);

-- =====================================================
-- LOOKUP ASSESSMENT AREA DATA
-- =====================================================

INSERT INTO `LOOKUP_ASSESSMENT_AREA` (`ASSESSMENT_AREA_CODE`, `ASSESSMENT_AREA`, `AREA_DESCRIPTION`, `IS_ACTIVE`) VALUES
('ACQUISITION', 'Acquisition', 'Acquisition and procurement activities', 1),
('MAINTENANCE', 'Maintenance', 'Maintenance and sustainment activities', 1),
('OPERATIONS', 'Operations', 'Operational activities and missions', 1),
('RESEARCH_DEV', 'Research and Development', 'Research and development activities', 1),
('TRAINING', 'Training', 'Training and education activities', 1),
('LOGISTICS', 'Logistics', 'Logistics and supply chain activities', 1),
('INTELLIGENCE', 'Intelligence', 'Intelligence gathering and analysis', 1),
('CYBER', 'Cybersecurity', 'Cybersecurity and information assurance', 1);

-- =====================================================
-- LOOKUP RESOURCE CATEGORY DATA
-- =====================================================

INSERT INTO `LOOKUP_RESOURCE_CATEGORY` (`CATEGORY_CODE`, `CATEGORY_NAME`, `CATEGORY_DESCRIPTION`, `IS_ACTIVE`) VALUES
('ACQUISITION', 'Acquisition', 'Acquisition and procurement resources', 1),
('MAINTENANCE', 'Maintenance', 'Maintenance and sustainment resources', 1),
('OPERATIONS', 'Operations', 'Operational resources and equipment', 1),
('RESEARCH_DEV', 'Research and Development', 'R&D resources and funding', 1),
('PERSONNEL', 'Personnel', 'Human resources and personnel costs', 1),
('TRAINING', 'Training', 'Training resources and facilities', 1),
('LOGISTICS', 'Logistics', 'Logistics and supply resources', 1),
('INTELLIGENCE', 'Intelligence', 'Intelligence resources and systems', 1),
('CYBER', 'Cybersecurity', 'Cybersecurity resources and tools', 1);

-- =====================================================
-- LOOKUP PROGRAM DATA
-- =====================================================

INSERT INTO `LOOKUP_PROGRAM` (`PROGRAM_CODE`, `PROGRAM_NAME`, `PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `POM_SPONSOR_CODE`, `EXECUTION_MANAGER_CODE`, `RESOURCE_CATEGORY_CODE`, `EOC_CODE`, `OSD_PROGRAM_ELEMENT_CODE`) VALUES
('PROG001', 'Special Operations Aviation', 'Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'USSOCOM', 'USASOC', 'ACQUISITION', 'EOC001', 'PE123456'),
('PROG002', 'Special Operations Ground Forces', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USASOC', 'OPERATIONS', 'EOC002', 'PE123457'),
('PROG003', 'Special Operations Maritime', 'Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'USSOCOM', 'NAVSPECWARCOM', 'OPERATIONS', 'EOC003', 'PE123458'),
('PROG004', 'Special Operations Air', 'Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'USSOCOM', 'AFSOC', 'OPERATIONS', 'EOC004', 'PE123459'),
('PROG005', 'Special Operations Intelligence', 'Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'USSOCOM', 'USSOCOM', 'INTELLIGENCE', 'EOC005', 'PE123460'),
('PROG006', 'Special Operations Cyber', 'Resource Constrained Programs', 'USSOCOM', 'CYBER', 'USSOCOM', 'USSOCOM', 'CYBER', 'EOC006', 'PE123461'),
('PROG007', 'Special Operations Training', 'Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'USSOCOM', 'USSOCOM', 'TRAINING', 'EOC007', 'PE123462'),
('PROG008', 'Special Operations Logistics', 'Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'USSOCOM', 'USSOCOM', 'LOGISTICS', 'EOC008', 'PE123463'),
('PROG009', 'Special Operations R&D', 'Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'USSOCOM', 'SORDAC', 'RESEARCH_DEV', 'EOC009', 'PE123464'),
('PROG010', 'Special Operations Maintenance', 'Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'USSOCOM', 'USSOCOM', 'MAINTENANCE', 'EOC010', 'PE123465'),
('PROG011', 'Joint Special Operations', 'Joint Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC011', 'PE123466'),
('PROG012', 'Special Operations Communications', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC012', 'PE123467'),
('PROG013', 'Special Operations Medical', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'PERSONNEL', 'EOC013', 'PE123468'),
('PROG014', 'Special Operations Psychological', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC014', 'PE123469'),
('PROG015', 'Special Operations Civil Affairs', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC015', 'PE123470'),
('PROG016', 'Special Operations Information', 'Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'USSOCOM', 'USSOCOM', 'INTELLIGENCE', 'EOC016', 'PE123471'),
('PROG017', 'Special Operations Counter-Terrorism', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC017', 'PE123472'),
('PROG018', 'Special Operations Counter-Proliferation', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC018', 'PE123473'),
('PROG019', 'Special Operations Unconventional Warfare', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC019', 'PE123474'),
('PROG020', 'Special Operations Foreign Internal Defense', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC020', 'PE123475'),
('PROG021', 'Special Operations Direct Action', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC021', 'PE123476'),
('PROG022', 'Special Operations Special Reconnaissance', 'Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'USSOCOM', 'USSOCOM', 'INTELLIGENCE', 'EOC022', 'PE123477'),
('PROG023', 'Special Operations Personnel Recovery', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC023', 'PE123478'),
('PROG024', 'Special Operations Counter-Insurgency', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC024', 'PE123479'),
('PROG025', 'Special Operations Security Force Assistance', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC025', 'PE123480'),
('PROG026', 'Special Operations Hostage Rescue', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC026', 'PE123481'),
('PROG027', 'Special Operations Counter-Narcotics', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC027', 'PE123482'),
('PROG028', 'Special Operations Humanitarian Assistance', 'Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'USSOCOM', 'USSOCOM', 'OPERATIONS', 'EOC028', 'PE123483');

-- =====================================================
-- LOOKUP POM POSITION DECREMENT DATA
-- =====================================================

INSERT INTO `LOOKUP_POM_POSITION_DECREMENT` (`POM_YEAR`, `POSITION`, `DECREMENT_VALUE`, `DESCRIPTION`, `IS_ACTIVE`) VALUES
(2024, 'FY24', 0.00, 'Current fiscal year 2024', 1),
(2025, 'FY25', 0.05, 'Fiscal year 2025 with 5% decrement', 1),
(2026, 'FY26', 0.10, 'Fiscal year 2026 with 10% decrement', 1),
(2027, 'FY27', 0.15, 'Fiscal year 2027 with 15% decrement', 1),
(2028, 'FY28', 0.20, 'Fiscal year 2028 with 20% decrement', 1),
(2029, 'FY29', 0.25, 'Fiscal year 2029 with 25% decrement', 1);

-- =====================================================
-- LOOKUP JCA DATA
-- =====================================================

INSERT INTO `LOOKUP_JCA` (`JCA_CODE`, `JCA_NAME`, `JCA_DESCRIPTION`, `IS_ACTIVE`) VALUES
('JCA001', 'Joint Capability Area 1', 'Primary joint capability area', 1),
('JCA002', 'Joint Capability Area 2', 'Secondary joint capability area', 1),
('JCA003', 'Joint Capability Area 3', 'Tertiary joint capability area', 1),
('JCA004', 'Joint Capability Area 4', 'Quaternary joint capability area', 1),
('JCA005', 'Joint Capability Area 5', 'Quinary joint capability area', 1);

-- =====================================================
-- LOOKUP TAG DATA
-- =====================================================

INSERT INTO `LOOKUP_TAG` (`TAG_NAME`, `TAG_DESCRIPTION`, `TAG_COLOR`, `IS_ACTIVE`) VALUES
('High Priority', 'High priority programs and initiatives', '#FF0000', 1),
('Critical', 'Critical programs requiring immediate attention', '#FF4500', 1),
('Standard', 'Standard priority programs', '#008000', 1),
('Low Priority', 'Low priority programs', '#808080', 1),
('Research', 'Research and development programs', '#0000FF', 1),
('Operational', 'Operational programs', '#800080', 1),
('Training', 'Training and education programs', '#FFA500', 1),
('Maintenance', 'Maintenance and sustainment programs', '#00FFFF', 1);

-- =====================================================
-- USER LOOKUP POM POSITION DATA
-- =====================================================

INSERT INTO `USR_LOOKUP_POM_POSITION` (`POM_YEAR`, `POSITION`, `IS_ACTIVE`) VALUES
(2024, 'FY24', 1),
(2025, 'FY25', 0),
(2026, 'FY26', 0),
(2027, 'FY27', 0),
(2028, 'FY28', 0),
(2029, 'FY29', 0);

-- =====================================================
-- USER LOOKUP SAVED COA DATA
-- =====================================================

INSERT INTO `USR_LOOKUP_SAVED_COA` (`COA_NAME`, `COA_DESCRIPTION`, `USER_ID`, `IS_SHARED`) VALUES
('Default COA', 'Default course of action for testing', 1, 1),
('Test COA 1', 'Test course of action 1', 1, 0),
('Test COA 2', 'Test course of action 2', 1, 1),
('Shared COA', 'Shared course of action for team use', 1, 1);

-- =====================================================
-- USER LOOKUP USER SAVED COA DATA
-- =====================================================

INSERT INTO `USR_LOOKUP_USER_SAVED_COA` (`USER_ID`, `COA_ID`, `COA_NAME`) VALUES
(1, 1, 'Default COA'),
(1, 2, 'Test COA 1'),
(1, 3, 'Test COA 2'),
(1, 4, 'Shared COA');

-- =====================================================
-- USER LOOKUP USER SHARED COA DATA
-- =====================================================

INSERT INTO `USR_LOOKUP_USER_SHARED_COA` (`USER_ID`, `COA_ID`, `SHARED_BY_USER_ID`) VALUES
(1, 1, 1),
(1, 3, 1),
(1, 4, 1);

-- =====================================================
-- USER OPTION SCORES DATA
-- =====================================================

INSERT INTO `USR_OPTION_SCORES` (`NAME`, `DESCRIPTION`, `PROGRAM_ID`, `SESSION`, `USER_ID`, `CRITERIA_NAME_ID`) VALUES
('Default Score', 'Default option score for testing', 1, 'session_001', 1, 1),
('High Priority Score', 'High priority option score', 2, 'session_001', 1, 2),
('Standard Score', 'Standard option score', 3, 'session_001', 1, 3),
('Low Priority Score', 'Low priority option score', 4, 'session_001', 1, 4);

-- =====================================================
-- END OF LOOKUP TABLES DATA
-- =====================================================


