#!/bin/bash
# Restore script for backup: socom_database_backup_20250901_115157.sql
echo "Restoring database from backup: socom_database_backup_20250901_115157.sql"
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password < "socom_database_backup_20250901_115157.sql"
echo "Database restore completed!"
