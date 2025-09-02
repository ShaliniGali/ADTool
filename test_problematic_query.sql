-- Test the problematic query from SOCOM_model::get_program
-- This simulates what the PHP code is trying to do

-- First, let's see what table is being used
SELECT 'Testing table access' as info;
SELECT COUNT(*) as table_count FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024;

-- Test the basic SELECT that's failing
SELECT 'Testing basic SELECT with PROGRAM_GROUP' as info;
SELECT 
    A.PROGRAM_GROUP,
    A.PROGRAM_CODE,
    A.CAPABILITY_SPONSOR_CODE,
    A.RESOURCE_CATEGORY_CODE,
    A.ASSESSMENT_AREA_CODE
FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024 A
LIMIT 5;

-- Test the JOIN with LOOKUP_PROGRAM
SELECT 'Testing JOIN with LOOKUP_PROGRAM' as info;
SELECT 
    A.PROGRAM_GROUP,
    A.PROGRAM_CODE,
    A.CAPABILITY_SPONSOR_CODE,
    A.RESOURCE_CATEGORY_CODE,
    A.ASSESSMENT_AREA_CODE,
    LUT.PROGRAM_NAME,
    LUT.ID as PROGRAM_ID
FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024 A
JOIN SOCOM_UI.LOOKUP_PROGRAM LUT ON A.PROGRAM_CODE = LUT.PROGRAM_CODE
LIMIT 5;

-- Test the complex query structure (simplified version)
SELECT 'Testing complex query structure' as info;
SELECT 
    A.PROGRAM_GROUP,
    A.PROGRAM_CODE,
    A.CAPABILITY_SPONSOR_CODE,
    A.RESOURCE_CATEGORY_CODE,
    A.ASSESSMENT_AREA_CODE,
    A.FISCAL_YEAR,
    A.RESOURCE_K
FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024 A
WHERE A.ASSESSMENT_AREA_CODE IN ('A', 'B', 'C')
GROUP BY A.PROGRAM_GROUP, A.PROGRAM_CODE, A.CAPABILITY_SPONSOR_CODE, A.RESOURCE_CATEGORY_CODE, A.ASSESSMENT_AREA_CODE
LIMIT 10;

-- Check if there are any NULL values in key columns
SELECT 'Checking for NULL values in key columns' as info;
SELECT 
    COUNT(*) as total_rows,
    COUNT(PROGRAM_GROUP) as non_null_program_group,
    COUNT(PROGRAM_CODE) as non_null_program_code,
    COUNT(CAPABILITY_SPONSOR_CODE) as non_null_sponsor,
    COUNT(RESOURCE_CATEGORY_CODE) as non_null_category
FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024;

-- Test the exact column names that should exist
SELECT 'Column names in RESOURCE_CONSTRAINED_COA_2024' as info;
SHOW COLUMNS FROM SOCOM_UI.RESOURCE_CONSTRAINED_COA_2024;

-- Test if we can access the table with different case
SELECT 'Testing case sensitivity' as info;
SELECT COUNT(*) as count FROM SOCOM_UI.resource_constrained_coa_2024;
SELECT COUNT(*) as count FROM SOCOM_UI.`RESOURCE_CONSTRAINED_COA_2024`;

-- Check if there are any transaction issues
SELECT 'Checking transaction status' as info;
SHOW VARIABLES LIKE 'autocommit';
SELECT @@autocommit as autocommit_value;




