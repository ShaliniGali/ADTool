-- =====================================================
-- SOCOM Database - Complete Test Data
-- Test_Data_Complete.sql
-- =====================================================

USE SOCOM_UI;

-- =====================================================
-- RESOURCE CONSTRAINED COA 2024 DATA
-- =====================================================

INSERT INTO `RESOURCE_CONSTRAINED_COA_2024` (`CAPABILITY_SPONSOR_CODE`, `EVENT_NAME`, `ASSESSMENT_AREA_CODE`, `PROGRAM_NAME`, `PROGRAM_CODE`, `PROGRAM_GROUP`, `RESOURCE_CATEGORY_CODE`, `RESOURCE_K`, `POM_SPONSOR_CODE`, `EXECUTION_MANAGER_CODE`, `EOC_CODE`, `OSD_PROGRAM_ELEMENT_CODE`, `DELTA_AMT`, `FISCAL_YEAR`, `WEIGHTED_SCORE`, `STORM_SCORE`, `POM_CYCLE_OPTIMIZATION_TYPE`) VALUES
('SORDAC', 'Aviation Modernization Initiative', 'ACQUISITION', 'Special Operations Aviation', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 150000000.00, 'USSOCOM', 'USASOC', 'EOC001', 'PE123456', 375000000.00, 2024, 0.85, 0.75, 'Resource Constraining'),
('USSOCOM', 'Ground Forces Enhancement', 'OPERATIONS', 'Special Operations Ground Forces', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 200000000.00, 'USSOCOM', 'USASOC', 'EOC002', 'PE123457', 500000000.00, 2024, 0.90, 0.80, 'Resource Constraining'),
('NAVSPECWARCOM', 'Maritime Operations Upgrade', 'OPERATIONS', 'Special Operations Maritime', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 175000000.00, 'USSOCOM', 'NAVSPECWARCOM', 'EOC003', 'PE123458', 425000000.00, 2024, 0.88, 0.78, 'Resource Constraining'),
('AFSOC', 'Air Operations Enhancement', 'OPERATIONS', 'Special Operations Air', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 180000000.00, 'USSOCOM', 'AFSOC', 'EOC004', 'PE123459', 450000000.00, 2024, 0.87, 0.77, 'Issue Optimization'),
('USSOCOM', 'Intelligence Systems Modernization', 'INTELLIGENCE', 'Special Operations Intelligence', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 120000000.00, 'USSOCOM', 'USSOCOM', 'EOC005', 'PE123460', 300000000.00, 2024, 0.92, 0.82, 'Issue Optimization'),
('USSOCOM', 'Cybersecurity Enhancement', 'CYBER', 'Special Operations Cyber', 'PROG006', 'Resource Constrained Programs', 'CYBER', 100000000.00, 'USSOCOM', 'USSOCOM', 'EOC006', 'PE123461', 250000000.00, 2024, 0.95, 0.85, 'Issue Optimization'),
('USSOCOM', 'Training Infrastructure Upgrade', 'TRAINING', 'Special Operations Training', 'PROG007', 'Resource Constrained Programs', 'TRAINING', 80000000.00, 'USSOCOM', 'USSOCOM', 'EOC007', 'PE123462', 200000000.00, 2024, 0.80, 0.70, 'Resource Constraining'),
('USSOCOM', 'Logistics Network Enhancement', 'LOGISTICS', 'Special Operations Logistics', 'PROG008', 'Resource Constrained Programs', 'LOGISTICS', 90000000.00, 'USSOCOM', 'USSOCOM', 'EOC008', 'PE123463', 225000000.00, 2024, 0.82, 0.72, 'Resource Constraining'),
('SORDAC', 'Advanced R&D Initiative', 'RESEARCH_DEV', 'Special Operations R&D', 'PROG009', 'Resource Constrained Programs', 'RESEARCH_DEV', 110000000.00, 'USSOCOM', 'SORDAC', 'EOC009', 'PE123464', 275000000.00, 2024, 0.88, 0.78, 'Issue Optimization'),
('USSOCOM', 'Maintenance Systems Upgrade', 'MAINTENANCE', 'Special Operations Maintenance', 'PROG010', 'Resource Constrained Programs', 'MAINTENANCE', 70000000.00, 'USSOCOM', 'USSOCOM', 'EOC010', 'PE123465', 175000000.00, 2024, 0.75, 0.65, 'Resource Constraining');

-- =====================================================
-- RESOURCE CONSTRAINED COA 2025 DATA
-- =====================================================

INSERT INTO `RESOURCE_CONSTRAINED_COA_2025` (`CAPABILITY_SPONSOR_CODE`, `EVENT_NAME`, `ASSESSMENT_AREA_CODE`, `PROGRAM_NAME`, `PROGRAM_CODE`, `PROGRAM_GROUP`, `RESOURCE_CATEGORY_CODE`, `RESOURCE_K`, `POM_SPONSOR_CODE`, `EXECUTION_MANAGER_CODE`, `EOC_CODE`, `OSD_PROGRAM_ELEMENT_CODE`, `DELTA_AMT`, `FISCAL_YEAR`, `WEIGHTED_SCORE`, `STORM_SCORE`, `POM_CYCLE_OPTIMIZATION_TYPE`) VALUES
('SORDAC', 'Next-Gen Aviation Systems', 'ACQUISITION', 'Special Operations Aviation', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 160000000.00, 'USSOCOM', 'USASOC', 'EOC001', 'PE123456', 400000000.00, 2025, 0.87, 0.77, 'Resource Constraining'),
('USSOCOM', 'Advanced Ground Capabilities', 'OPERATIONS', 'Special Operations Ground Forces', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 210000000.00, 'USSOCOM', 'USASOC', 'EOC002', 'PE123457', 525000000.00, 2025, 0.92, 0.82, 'Resource Constraining'),
('NAVSPECWARCOM', 'Enhanced Maritime Operations', 'OPERATIONS', 'Special Operations Maritime', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 185000000.00, 'USSOCOM', 'NAVSPECWARCOM', 'EOC003', 'PE123458', 462500000.00, 2025, 0.90, 0.80, 'Resource Constraining'),
('AFSOC', 'Future Air Operations', 'OPERATIONS', 'Special Operations Air', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 190000000.00, 'USSOCOM', 'AFSOC', 'EOC004', 'PE123459', 475000000.00, 2025, 0.89, 0.79, 'Issue Optimization'),
('USSOCOM', 'Next-Gen Intelligence Systems', 'INTELLIGENCE', 'Special Operations Intelligence', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 130000000.00, 'USSOCOM', 'USSOCOM', 'EOC005', 'PE123460', 325000000.00, 2025, 0.94, 0.84, 'Issue Optimization');

-- =====================================================
-- PB COMPARISON DATA
-- =====================================================

INSERT INTO `DT_PB_COMPARISON` (`CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `PROGRAM_GROUP`, `RESOURCE_CATEGORY_CODE`, `FISCAL_YEAR`, `BUDGET_AMOUNT`) VALUES
('SORDAC', 'ACQUISITION', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 2024, 375000000.00),
('USSOCOM', 'OPERATIONS', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 2024, 500000000.00),
('NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 2024, 425000000.00),
('AFSOC', 'OPERATIONS', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 2024, 450000000.00),
('USSOCOM', 'INTELLIGENCE', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 2024, 300000000.00),
('USSOCOM', 'CYBER', 'PROG006', 'Resource Constrained Programs', 'CYBER', 2024, 250000000.00),
('USSOCOM', 'TRAINING', 'PROG007', 'Resource Constrained Programs', 'TRAINING', 2024, 200000000.00),
('USSOCOM', 'LOGISTICS', 'PROG008', 'Resource Constrained Programs', 'LOGISTICS', 2024, 225000000.00),
('SORDAC', 'RESEARCH_DEV', 'PROG009', 'Resource Constrained Programs', 'RESEARCH_DEV', 2024, 275000000.00),
('USSOCOM', 'MAINTENANCE', 'PROG010', 'Resource Constrained Programs', 'MAINTENANCE', 2024, 175000000.00),
('SORDAC', 'ACQUISITION', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 2025, 400000000.00),
('USSOCOM', 'OPERATIONS', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 2025, 525000000.00),
('NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 2025, 462500000.00),
('AFSOC', 'OPERATIONS', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 2025, 475000000.00),
('USSOCOM', 'INTELLIGENCE', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 2025, 325000000.00);

-- =====================================================
-- BUDGET EXECUTION DATA
-- =====================================================

INSERT INTO `DT_BUDGET_EXECUTION` (`CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `PROGRAM_GROUP`, `RESOURCE_CATEGORY_CODE`, `EOC_CODE`, `EXECUTION_MANAGER_CODE`, `OSD_PROGRAM_ELEMENT_CODE`, `FISCAL_YEAR`, `EXECUTED_AMOUNT`) VALUES
('SORDAC', 'ACQUISITION', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 'EOC001', 'USASOC', 'PE123456', 2024, 350000000.00),
('USSOCOM', 'OPERATIONS', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 'EOC002', 'USASOC', 'PE123457', 2024, 475000000.00),
('NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 'EOC003', 'NAVSPECWARCOM', 'PE123458', 2024, 400000000.00),
('AFSOC', 'OPERATIONS', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 'EOC004', 'AFSOC', 'PE123459', 2024, 425000000.00),
('USSOCOM', 'INTELLIGENCE', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 'EOC005', 'USSOCOM', 'PE123460', 2024, 280000000.00),
('USSOCOM', 'CYBER', 'PROG006', 'Resource Constrained Programs', 'CYBER', 'EOC006', 'USSOCOM', 'PE123461', 2024, 230000000.00),
('USSOCOM', 'TRAINING', 'PROG007', 'Resource Constrained Programs', 'TRAINING', 'EOC007', 'USSOCOM', 'PE123462', 2024, 185000000.00),
('USSOCOM', 'LOGISTICS', 'PROG008', 'Resource Constrained Programs', 'LOGISTICS', 'EOC008', 'USSOCOM', 'PE123463', 2024, 210000000.00),
('SORDAC', 'RESEARCH_DEV', 'PROG009', 'Resource Constrained Programs', 'RESEARCH_DEV', 'EOC009', 'SORDAC', 'PE123464', 2024, 260000000.00),
('USSOCOM', 'MAINTENANCE', 'PROG010', 'Resource Constrained Programs', 'MAINTENANCE', 'EOC010', 'USSOCOM', 'PE123465', 2024, 165000000.00),
('SORDAC', 'ACQUISITION', 'PROG001', 'Resource Constrained Programs', 'ACQUISITION', 'EOC001', 'USASOC', 'PE123456', 2025, 375000000.00),
('USSOCOM', 'OPERATIONS', 'PROG002', 'Resource Constrained Programs', 'OPERATIONS', 'EOC002', 'USASOC', 'PE123457', 2025, 500000000.00),
('NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 'Resource Constrained Programs', 'OPERATIONS', 'EOC003', 'NAVSPECWARCOM', 'PE123458', 2025, 437500000.00),
('AFSOC', 'OPERATIONS', 'PROG004', 'Resource Constrained Programs', 'OPERATIONS', 'EOC004', 'AFSOC', 'PE123459', 2025, 450000000.00),
('USSOCOM', 'INTELLIGENCE', 'PROG005', 'Resource Constrained Programs', 'INTELLIGENCE', 'EOC005', 'USSOCOM', 'PE123460', 2025, 305000000.00);

-- =====================================================
-- AMS FEM DATA
-- =====================================================

INSERT INTO `DT_AMS_FEM` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `APPN`, `ELEMENT_OF_COST`, `EXECUTION_MANAGER_CODE`, `PE`, `FISCAL_YEAR`, `AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'APPN001', 'Procurement', 'USASOC', 'PE123456', 2024, 150000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'APPN002', 'Operations', 'USASOC', 'PE123457', 2024, 200000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'APPN003', 'Operations', 'NAVSPECWARCOM', 'PE123458', 2024, 175000000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'APPN004', 'Operations', 'AFSOC', 'PE123459', 2024, 180000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'APPN005', 'Intelligence', 'USSOCOM', 'PE123460', 2024, 120000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'APPN006', 'Cybersecurity', 'USSOCOM', 'PE123461', 2024, 100000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'APPN007', 'Training', 'USSOCOM', 'PE123462', 2024, 80000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'APPN008', 'Logistics', 'USSOCOM', 'PE123463', 2024, 90000000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'APPN009', 'R&D', 'SORDAC', 'PE123464', 2024, 110000000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'APPN010', 'Maintenance', 'USSOCOM', 'PE123465', 2024, 70000000.00);

-- =====================================================
-- ISS EXTRACT 2024 DATA
-- =====================================================

INSERT INTO `DT_ISS_EXTRACT_2024` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2024, 375000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2024, 500000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2024, 425000000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2024, 450000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2024, 300000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2024, 250000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2024, 200000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2024, 225000000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2024, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2024, 175000000.00);

-- =====================================================
-- ISS EXTRACT 2025 DATA
-- =====================================================

INSERT INTO `DT_ISS_EXTRACT_2025` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2025, 400000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2025, 525000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2025, 462500000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2025, 475000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2025, 325000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2025, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2025, 220000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2025, 247500000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2025, 302500000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2025, 192500000.00);

-- =====================================================
-- ZBT EXTRACT 2024 DATA
-- =====================================================

INSERT INTO `DT_ZBT_EXTRACT_2024` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2024, 375000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2024, 500000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2024, 425000000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2024, 450000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2024, 300000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2024, 250000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2024, 200000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2024, 225000000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2024, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2024, 175000000.00);

-- =====================================================
-- ZBT EXTRACT 2025 DATA
-- =====================================================

INSERT INTO `DT_ZBT_EXTRACT_2025` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2025, 400000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2025, 525000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2025, 462500000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2025, 475000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2025, 325000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2025, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2025, 220000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2025, 247500000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2025, 302500000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2025, 192500000.00);

-- =====================================================
-- ISS SUMMARY 2024 DATA
-- =====================================================

INSERT INTO `ISS_SUMMARY_2024` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `TOTAL_AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2024, 375000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2024, 500000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2024, 425000000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2024, 450000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2024, 300000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2024, 250000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2024, 200000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2024, 225000000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2024, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2024, 175000000.00);

-- =====================================================
-- ISS SUMMARY 2025 DATA
-- =====================================================

INSERT INTO `ISS_SUMMARY_2025` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `TOTAL_AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2025, 400000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2025, 525000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2025, 462500000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2025, 475000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2025, 325000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2025, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2025, 220000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2025, 247500000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2025, 302500000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2025, 192500000.00);

-- =====================================================
-- ZBT SUMMARY 2024 DATA
-- =====================================================

INSERT INTO `ZBT_SUMMARY_2024` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `TOTAL_AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2024, 375000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2024, 500000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2024, 425000000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2024, 450000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2024, 300000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2024, 250000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2024, 200000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2024, 225000000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2024, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2024, 175000000.00);

-- =====================================================
-- ZBT SUMMARY 2025 DATA
-- =====================================================

INSERT INTO `ZBT_SUMMARY_2025` (`PROGRAM_GROUP`, `CAPABILITY_SPONSOR_CODE`, `ASSESSMENT_AREA_CODE`, `PROGRAM_CODE`, `FISCAL_YEAR`, `TOTAL_AMOUNT`) VALUES
('Resource Constrained Programs', 'SORDAC', 'ACQUISITION', 'PROG001', 2025, 400000000.00),
('Resource Constrained Programs', 'USSOCOM', 'OPERATIONS', 'PROG002', 2025, 525000000.00),
('Resource Constrained Programs', 'NAVSPECWARCOM', 'OPERATIONS', 'PROG003', 2025, 462500000.00),
('Resource Constrained Programs', 'AFSOC', 'OPERATIONS', 'PROG004', 2025, 475000000.00),
('Resource Constrained Programs', 'USSOCOM', 'INTELLIGENCE', 'PROG005', 2025, 325000000.00),
('Resource Constrained Programs', 'USSOCOM', 'CYBER', 'PROG006', 2025, 275000000.00),
('Resource Constrained Programs', 'USSOCOM', 'TRAINING', 'PROG007', 2025, 220000000.00),
('Resource Constrained Programs', 'USSOCOM', 'LOGISTICS', 'PROG008', 2025, 247500000.00),
('Resource Constrained Programs', 'SORDAC', 'RESEARCH_DEV', 'PROG009', 2025, 302500000.00),
('Resource Constrained Programs', 'USSOCOM', 'MAINTENANCE', 'PROG010', 2025, 192500000.00);

-- =====================================================
-- SCHEDULER DATA
-- =====================================================

INSERT INTO `USR_DT_SCHEDULER` (`CYCLE_ID`, `TYPE`, `CRON_STATUS`, `CRON_PROCESSED`, `ERRORS`, `WARNINGS`, `USER_ID`) VALUES
(1, 'PROGRAM_SCORE_UPLOAD', 0, 0, NULL, NULL, 1),
(2, 'PROGRAM_SCORE_UPLOAD', 1, 1, NULL, 'Minor data validation warnings', 1),
(3, 'PROGRAM_SCORE_UPLOAD', 0, 0, 'Data processing error occurred', NULL, 1);

-- =====================================================
-- SCHEDULER MAP DATA
-- =====================================================

INSERT INTO `USR_DT_SCHEDULER_MAP` (`TYPE`, `DT_SCHEDULER_ID`, `MAP_ID`) VALUES
('PROGRAM_SCORE_UPLOAD', 1, 1),
('PROGRAM_SCORE_UPLOAD', 2, 2),
('PROGRAM_SCORE_UPLOAD', 3, 3);

-- =====================================================
-- END OF TEST DATA
-- =====================================================


