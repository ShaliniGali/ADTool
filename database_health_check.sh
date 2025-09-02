#!/bin/bash

# Database Health Check Script
# This script documents the current state of the SOCOM database

echo "=== SOCOM Database Health Check ==="
echo "Date: $(date)"
echo ""

# Database connection details
DB_HOST="localhost"
DB_USER="root"
DB_PASSWORD="rhombus_root_password"
DB_NAME="SOCOM_UI"

echo "=== Database Connection Test ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT VERSION();" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ Database connection successful"
else
    echo "✗ Database connection failed"
    exit 1
fi
echo ""

echo "=== All Tables in SOCOM_UI ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SHOW TABLES FROM $DB_NAME;" 2>/dev/null
echo ""

echo "=== Tables with PROGRAM_GROUP Column ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE COLUMN_NAME LIKE '%PROGRAM_GROUP%' 
AND TABLE_SCHEMA = '$DB_NAME'
ORDER BY TABLE_NAME;" 2>/dev/null
echo ""

echo "=== Lookup Tables Status ==="
echo "--- LOOKUP_PROGRAM ---"
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) as count, 'LOOKUP_PROGRAM' as table_name FROM $DB_NAME.LOOKUP_PROGRAM UNION ALL SELECT COUNT(*) as count, 'LOOKUP_SPONSOR' as table_name FROM $DB_NAME.LOOKUP_SPONSOR UNION ALL SELECT COUNT(*) as count, 'LOOKUP_RESOURCE_CATEGORY' as table_name FROM $DB_NAME.LOOKUP_RESOURCE_CATEGORY;" 2>/dev/null
echo ""

echo "=== Sample Data from Lookup Tables ==="
echo "--- LOOKUP_PROGRAM (first 5 rows) ---"
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT * FROM $DB_NAME.LOOKUP_PROGRAM LIMIT 5;" 2>/dev/null
echo ""

echo "--- LOOKUP_SPONSOR (first 5 rows) ---"
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT * FROM $DB_NAME.LOOKUP_SPONSOR LIMIT 5;" 2>/dev/null
echo ""

echo "--- LOOKUP_RESOURCE_CATEGORY (first 5 rows) ---"
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT * FROM $DB_NAME.LOOKUP_RESOURCE_CATEGORY LIMIT 5;" 2>/dev/null
echo ""

echo "=== RESOURCE_CONSTRAINED_COA_2024 Table Structure ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "DESCRIBE $DB_NAME.RESOURCE_CONSTRAINED_COA_2024;" 2>/dev/null
echo ""

echo "=== RESOURCE_CONSTRAINED_COA_2024 Data ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT * FROM $DB_NAME.RESOURCE_CONSTRAINED_COA_2024;" 2>/dev/null
echo ""

echo "=== Database Size Information ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "
SELECT 
    table_schema AS 'Database',
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = '$DB_NAME'
ORDER BY (data_length + index_length) DESC;" 2>/dev/null
echo ""

echo "=== Table Row Counts ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "
SELECT 
    'LOOKUP_PROGRAM' as table_name, COUNT(*) as row_count FROM $DB_NAME.LOOKUP_PROGRAM
UNION ALL
SELECT 'LOOKUP_SPONSOR' as table_name, COUNT(*) as row_count FROM $DB_NAME.LOOKUP_SPONSOR
UNION ALL
SELECT 'LOOKUP_RESOURCE_CATEGORY' as table_name, COUNT(*) as row_count FROM $DB_NAME.LOOKUP_RESOURCE_CATEGORY
UNION ALL
SELECT 'RESOURCE_CONSTRAINED_COA_2024' as table_name, COUNT(*) as row_count FROM $DB_NAME.RESOURCE_CONSTRAINED_COA_2024
UNION ALL
SELECT 'ISS_SUMMARY_2024' as table_name, COUNT(*) as row_count FROM $DB_NAME.ISS_SUMMARY_2024
UNION ALL
SELECT 'ISS_SUMMARY_2025' as table_name, COUNT(*) as row_count FROM $DB_NAME.ISS_SUMMARY_2025;" 2>/dev/null
echo ""

echo "=== Database Variables ==="
docker exec rhombus-mysql mysql -u $DB_USER -p$DB_PASSWORD -e "SHOW VARIABLES LIKE 'sql_mode'; SHOW VARIABLES LIKE 'lower_case_table_names';" 2>/dev/null
echo ""

echo "=== Health Check Complete ==="
echo "Check the output above for any issues or inconsistencies."



