#!/bin/bash

# SOCOM Database Setup Script
# This script runs the SQL setup to create the required database and tables

echo "Setting up SOCOM Database..."

# Check if MySQL is accessible
if ! mysql -h rhombus-mysql -u rhombus_user -prhombus_password -e "SELECT 1;" > /dev/null 2>&1; then
    echo "Error: Cannot connect to MySQL database"
    echo "Please ensure the database is running and accessible"
    exit 1
fi

echo "Connected to MySQL successfully"

# Run the SQL setup script
echo "Creating SOCOM_UI database and tables..."
mysql -h rhombus-mysql -u rhombus_user -prhombus_password < setup_socom_database.sql

if [ $? -eq 0 ]; then
    echo "SOCOM database setup completed successfully!"
    echo ""
    echo "Created tables:"
    echo "- ZBT_SUMMARY_2024 and ZBT_SUMMARY_2025"
    echo "- ISS_SUMMARY_2024 and ISS_SUMMARY_2025" 
    echo "- POM_SUMMARY_2024 and POM_SUMMARY_2025"
    echo "- RESOURCE_CONSTRAINED_COA_2024 and RESOURCE_CONSTRAINED_COA_2025"
    echo "- LOOKUP_SPONSOR, LOOKUP_ASSESSMENT_AREA, LOOKUP_RESOURCE_CATEGORY"
    echo ""
    echo "Sample data has been inserted for testing."
    echo "The 'undefined' error in your SOCOM URLs should now be resolved."
else
    echo "Error: Database setup failed"
    exit 1
fi
