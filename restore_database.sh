#!/bin/bash

# SOCOM Database Restoration Script
# This script automates the database restoration process

echo "=========================================="
echo "SOCOM DATABASE RESTORATION SCRIPT"
echo "=========================================="

# Check if Docker containers are running
echo "Step 1: Checking Docker containers..."
if ! docker ps | grep -q "rhombus-mysql"; then
    echo "ERROR: rhombus-mysql container is not running!"
    echo "Please start Docker containers first: docker compose up -d"
    exit 1
fi

if ! docker ps | grep -q "rhombus-php"; then
    echo "ERROR: rhombus-php container is not running!"
    echo "Please start Docker containers first: docker compose up -d"
    exit 1
fi

echo "✓ Docker containers are running"

# Create databases
echo "Step 2: Creating databases..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password -e "
CREATE DATABASE IF NOT EXISTS SOCOM_UI;
CREATE DATABASE IF NOT EXISTS rhombus_db;
SHOW DATABASES;
"
echo "✓ Databases created"

# Run restoration scripts in order
echo "Step 3: Running database restoration scripts..."

echo "  - Creating missing tables and columns..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password < add_missing_dt_tables.sql

echo "  - Adding lookup tables..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password < add_missing_lookup_pom_position_decrement.sql
docker exec rhombus-mysql mysql -u root -prhombus_root_password < add_missing_pom_position_table.sql

echo "  - Restoring complete database backup..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password < complete_database_backup_corrected.sql

echo "  - Adding seed data..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password < final_seed_data_from_sql_folder.sql
docker exec rhombus-mysql mysql -u root -prhombus_root_password < seed_data_insert_ignore.sql

echo "  - Updating column names..."
docker exec rhombus-mysql mysql -u root -prhombus_root_password < rename_all_id_columns.sql

echo "✓ Database restoration scripts completed"

# Verify restoration
echo "Step 4: Verifying database restoration..."
USER_COUNT=$(docker exec rhombus-mysql mysql -u root -prhombus_root_password -e "USE SOCOM_UI; SELECT COUNT(*) FROM users;" -s -N)
echo "✓ Found $USER_COUNT users in SOCOM_UI database"

# Test application access
echo "Step 5: Testing application access..."
ZBT_TITLE=$(curl -s "http://localhost/socom/zbt_summary/" | grep -o '<title>[^<]*</title>' | head -1)
if [[ $ZBT_TITLE == *"ZBT Summary"* ]]; then
    echo "✓ ZBT Summary page accessible"
else
    echo "⚠ ZBT Summary page may not be accessible"
fi

COA_TITLE=$(curl -s "http://localhost/dashboard/coa_management" | grep -o '<title>[^<]*</title>' | head -1)
if [[ $COA_TITLE == *"COA Management"* ]]; then
    echo "✓ COA Management page accessible"
else
    echo "⚠ COA Management page may not be accessible"
fi

# Test login
echo "Step 6: Testing user login..."
LOGIN_RESULT=$(curl -s -X POST "http://localhost/login/user_check" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin@rhombus.local&password=password&tos_agreement_check=true" | jq -r '.result // "failed"')

if [[ $LOGIN_RESULT == "success" ]]; then
    echo "✓ Admin login successful"
else
    echo "⚠ Admin login failed (result: $LOGIN_RESULT)"
fi

echo "=========================================="
echo "DATABASE RESTORATION COMPLETE!"
echo "=========================================="
echo "Summary:"
echo "- Users in database: $USER_COUNT"
echo "- ZBT Summary: $ZBT_TITLE"
echo "- COA Management: $COA_TITLE"
echo "- Admin login: $LOGIN_RESULT"
echo ""
echo "If any tests failed, check the troubleshooting section"
echo "in DATABASE_RESTORATION_GUIDE.txt"
echo "=========================================="
