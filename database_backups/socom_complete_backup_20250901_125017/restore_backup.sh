#!/bin/bash
# Restore script for backup: socom_complete_backup_20250901_125017

echo "Restoring SOCOM application from backup: socom_complete_backup_20250901_125017"

# Check if we're in the right directory
if [ ! -f "docker-compose.yml" ]; then
    echo "Error: Please run this script from the project root directory"
    exit 1
fi

# Stop containers
echo "Stopping containers..."
docker compose down

# Restore database
echo "Restoring database..."
docker compose up -d mysql
sleep 10
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password < database_backup.sql

# Restore configuration files
echo "Restoring configuration files..."
cp docker-compose.yml ../
cp my.cnf ../mysql-config/

# Restore PHP files
echo "Restoring PHP files..."
cp controllers/* ../../php-main/application/controllers/SOCOM/
cp models/* ../../php-main/application/models/
cp helpers/* ../../php-main/application/helpers/
cp views/* ../../php-main/application/views/SOCOM/

# Restore JavaScript files
echo "Restoring JavaScript files..."
cp javascript/* ../../php-main/assets/js/actions/SOCOM/

# Restart containers
echo "Restarting containers..."
cd ..
docker compose up -d

echo "Backup restoration completed!"
echo "The application should now be running with all fixes applied."
