-- Test the exact query from SOCOM_model::get_program
-- This will help identify the specific issue

USE SOCOM_UI;

-- First, let's see what the dynamic table resolves to
SELECT 'Testing table resolution' as info;
SELECT 'RESOURCE_CONSTRAINED_COA_2024' as expected_table;

-- Test the basic table access
SELECT 'Testing basic table access' as info;
SELECT COUNT(*) as total_rows FROM RESOURCE_CONSTRAINED_COA_2024;

-- Test the specific columns that are being selected
SELECT 'Testing specific column selection' as info;
SELECT 
    PROGRAM_CODE,
    PROGRAM_GROUP,
    CAPABILITY_SPONSOR_CODE,
    RESOURCE_CATEGORY_CODE,
    ASSESSMENT_AREA_CODE,
    FISCAL_YEAR,
    RESOURCE_K
FROM RESOURCE_CONSTRAINED_COA_2024
LIMIT 5;

-- Test the JOIN with LOOKUP_PROGRAM
SELECT 'Testing JOIN with LOOKUP_PROGRAM' as info;
SELECT 
    LUT.ID as PROGRAM_ID,
    LUT.PROGRAM_NAME,
    LUT.PROGRAM_GROUP as LUT_PROGRAM_GROUP,
    POS.PROGRAM_CODE,
    POS.PROGRAM_GROUP as POS_PROGRAM_GROUP,
    POS.CAPABILITY_SPONSOR_CODE,
    POS.RESOURCE_CATEGORY_CODE,
    POS.ASSESSMENT_AREA_CODE
FROM (
    SELECT 
        ASSESSMENT_AREA_CODE,
        CAPABILITY_SPONSOR_CODE,
        FISCAL_YEAR,
        RESOURCE_K,
        PROGRAM_GROUP,
        PROGRAM_CODE,
        RESOURCE_CATEGORY_CODE
    FROM RESOURCE_CONSTRAINED_COA_2024
    WHERE ASSESSMENT_AREA_CODE IN ('A', 'B', 'C')
    GROUP BY PROGRAM_GROUP, PROGRAM_CODE, CAPABILITY_SPONSOR_CODE, RESOURCE_CATEGORY_CODE, ASSESSMENT_AREA_CODE
) AS POS
JOIN LOOKUP_PROGRAM LUT ON 
    POS.PROGRAM_CODE = LUT.PROGRAM_CODE AND 
    POS.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE AND 
    POS.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
WHERE LUT.EVENT_NAME IS NULL
LIMIT 10;

-- Test if there are any NULL values in PROGRAM_GROUP
SELECT 'Checking for NULL values in PROGRAM_GROUP' as info;
SELECT 
    COUNT(*) as total_rows,
    COUNT(PROGRAM_GROUP) as non_null_program_group,
    COUNT(CASE WHEN PROGRAM_GROUP IS NULL THEN 1 END) as null_program_group
FROM RESOURCE_CONSTRAINED_COA_2024;

-- Test the GROUP BY clause that's used in the query
SELECT 'Testing GROUP BY clause' as info;
SELECT 
    PROGRAM_GROUP,
    PROGRAM_CODE,
    CAPABILITY_SPONSOR_CODE,
    ASSESSMENT_AREA_CODE,
    COUNT(*) as row_count
FROM RESOURCE_CONSTRAINED_COA_2024
WHERE ASSESSMENT_AREA_CODE IN ('A', 'B', 'C')
GROUP BY PROGRAM_GROUP, PROGRAM_CODE, CAPABILITY_SPONSOR_CODE, ASSESSMENT_AREA_CODE
LIMIT 10;

-- Check if there are any case sensitivity issues
SELECT 'Testing case sensitivity' as info;
SELECT 
    'RESOURCE_CONSTRAINED_COA_2024' as table_name,
    COUNT(*) as count 
FROM RESOURCE_CONSTRAINED_COA_2024
UNION ALL
SELECT 
    'resource_constrained_coa_2024' as table_name,
    COUNT(*) as count 
FROM resource_constrained_coa_2024;

-- Test the exact column names with backticks
SELECT 'Testing with backticks' as info;
SELECT 
    `PROGRAM_GROUP`,
    `PROGRAM_CODE`,
    `CAPABILITY_SPONSOR_CODE`
FROM `RESOURCE_CONSTRAINED_COA_2024`
LIMIT 3;



