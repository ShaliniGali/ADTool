#!/bin/bash

# Database Backup Script for SOCOM Application
# This script creates a complete backup of the MySQL databases

# Configuration
BACKUP_DIR="./database_backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="socom_database_backup_${TIMESTAMP}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "Starting database backup at $(date)"
echo "Backup will be saved to: ${BACKUP_DIR}/${BACKUP_NAME}.sql"

# Create backup of both databases
docker exec rhombus-mysql mysqldump \
    -u rhombus_user \
    -prhombus_password \
    --no-tablespaces \
    --routines \
    --triggers \
    --events \
    --single-transaction \
    --add-drop-database \
    --databases SOCOM_UI rhombus_db \
    > "${BACKUP_DIR}/${BACKUP_NAME}.sql"

if [ $? -eq 0 ]; then
    echo "✅ Database backup completed successfully!"
    echo "📁 Backup file: ${BACKUP_DIR}/${BACKUP_NAME}.sql"
    
    # Show file size
    FILE_SIZE=$(du -h "${BACKUP_DIR}/${BACKUP_NAME}.sql" | cut -f1)
    echo "📊 Backup size: ${FILE_SIZE}"
    
    # Create a restore script
    cat > "${BACKUP_DIR}/restore_${BACKUP_NAME}.sh" << EOF
#!/bin/bash
# Restore script for backup: ${BACKUP_NAME}.sql
echo "Restoring database from backup: ${BACKUP_NAME}.sql"
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password < "${BACKUP_NAME}.sql"
echo "Database restore completed!"
EOF
    
    chmod +x "${BACKUP_DIR}/restore_${BACKUP_NAME}.sh"
    echo "🔄 Restore script created: ${BACKUP_DIR}/restore_${BACKUP_NAME}.sh"
    
else
    echo "❌ Database backup failed!"
    exit 1
fi

echo "Backup process completed at $(date)"
