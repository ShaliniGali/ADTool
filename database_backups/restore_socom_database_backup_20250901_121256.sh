#!/bin/bash
# Restore script for backup: socom_database_backup_20250901_121256.sql
echo "Restoring database from backup: socom_database_backup_20250901_121256.sql"
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password < "socom_database_backup_20250901_121256.sql"
echo "Database restore completed!"
