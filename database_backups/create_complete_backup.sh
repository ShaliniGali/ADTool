#!/bin/bash
# Comprehensive Backup Script for SOCOM Application
# This script creates a complete backup of all changes made during development fixes

# Configuration
BACKUP_DIR="./database_backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="socom_complete_backup_${TIMESTAMP}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "Starting comprehensive backup at $(date)"
echo "Backup will be saved to: ${BACKUP_DIR}/${BACKUP_NAME}"

# Create backup directory for this backup
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}"

echo "📁 Creating backup structure..."

# 1. Database Backup
echo "🗄️  Creating database backup..."
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
    > "${BACKUP_DIR}/${BACKUP_NAME}/database_backup.sql"

if [ $? -eq 0 ]; then
    echo "✅ Database backup completed successfully!"
else
    echo "❌ Database backup failed!"
    exit 1
fi

# 2. Configuration Files Backup
echo "⚙️  Backing up configuration files..."

# Docker configuration
cp docker-compose.yml "${BACKUP_DIR}/${BACKUP_NAME}/"
cp mysql-config/my.cnf "${BACKUP_DIR}/${BACKUP_NAME}/"

# PHP configuration files
cp php-main/application/config/config.php "${BACKUP_DIR}/${BACKUP_NAME}/"
cp php-main/application/config/constants.php "${BACKUP_DIR}/${BACKUP_NAME}/"
cp php-main/application/config/routes.php "${BACKUP_DIR}/${BACKUP_NAME}/"
cp php-main/application/config/project_config/project_routes.php "${BACKUP_DIR}/${BACKUP_NAME}/"

# 3. Modified PHP Files Backup
echo "🐘 Backing up modified PHP files..."

# Controllers
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/controllers"
cp php-main/application/controllers/SOCOM/Database_Save_Program_Alignment.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"
cp php-main/application/controllers/SOCOM/Database_upload.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"
cp php-main/application/controllers/SOCOM/SOCOM_Dashboard_COA_Management.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"
cp php-main/application/controllers/SOCOM/SOCOM_Dashboard_Cycle_Management.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"
cp php-main/application/controllers/SOCOM/SOCOM_Event_Summary.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"
cp php-main/application/controllers/SOCOM/SOCOM_HOME.php "${BACKUP_DIR}/${BACKUP_NAME}/controllers/"

# Models
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/models"
cp php-main/application/models/SOCOM_COA_model.php "${BACKUP_DIR}/${BACKUP_NAME}/models/"
cp php-main/application/models/SOCOM_Database_Upload_model.php "${BACKUP_DIR}/${BACKUP_NAME}/models/"

# Helpers
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/helpers"
cp php-main/application/helpers/auth_user_role_coa_helper.php "${BACKUP_DIR}/${BACKUP_NAME}/helpers/"

# Views
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/views"
cp php-main/application/views/SOCOM/home_view.php "${BACKUP_DIR}/${BACKUP_NAME}/views/"
cp php-main/application/views/SOCOM/dashboard/home.php "${BACKUP_DIR}/${BACKUP_NAME}/views/"
cp php-main/application/views/SOCOM/overall_event_summary_view.php "${BACKUP_DIR}/${BACKUP_NAME}/views/"

# 4. JavaScript Files Backup
echo "📜 Backing up modified JavaScript files..."
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/javascript"
cp php-main/assets/js/actions/SOCOM/overall_event_summary.js "${BACKUP_DIR}/${BACKUP_NAME}/javascript/"

# 5. SQL Scripts Backup
echo "📋 Backing up SQL scripts..."
mkdir -p "${BACKUP_DIR}/${BACKUP_NAME}/sql"
cp php-main/sql/seed_data.sql "${BACKUP_DIR}/${BACKUP_NAME}/sql/"
cp php-main/sql/seed_data_corrected.sql "${BACKUP_DIR}/${BACKUP_NAME}/sql/"
cp php-main/sql/rhombus_schema.sql "${BACKUP_DIR}/${BACKUP_NAME}/sql/"
cp database_backups/complete_database_backup.sql "${BACKUP_DIR}/${BACKUP_NAME}/sql/"

# 6. Create backup manifest
echo "📝 Creating backup manifest..."
cat > "${BACKUP_DIR}/${BACKUP_NAME}/BACKUP_MANIFEST.md" << 'EOF'
# SOCOM Application Complete Backup

## Backup Information
- **Date**: $(date)
- **Backup Name**: ${BACKUP_NAME}
- **Description**: Complete backup of all development fixes and database changes

## Contents

### Database
- `database_backup.sql` - Complete MySQL database dump
- `sql/complete_database_backup.sql` - Comprehensive database schema and data script

### Configuration Files
- `docker-compose.yml` - Docker Compose configuration with environment variables
- `my.cnf` - MySQL configuration for development
- `config.php` - PHP application configuration
- `constants.php` - PHP constants configuration
- `routes.php` - Main routing configuration
- `project_routes.php` - Project-specific routes

### Modified PHP Files

#### Controllers
- `Database_Save_Program_Alignment.php` - Fixed session handling and authentication
- `Database_upload.php` - Added dev bypass and session handling
- `SOCOM_Dashboard_COA_Management.php` - Fixed missing models and session handling
- `SOCOM_Dashboard_Cycle_Management.php` - Added dev bypass
- `SOCOM_Event_Summary.php` - Added sample data for Python API bypass
- `SOCOM_HOME.php` - Fixed authentication and missing libraries

#### Models
- `SOCOM_COA_model.php` - Added session handling for dev bypass
- `SOCOM_Database_Upload_model.php` - Database upload functionality

#### Helpers
- `auth_user_role_coa_helper.php` - Added development bypass functionality

#### Views
- `home_view.php` - Updated tile visibility logic
- `home.php` - Updated dashboard tile visibility
- `overall_event_summary_view.php` - Fixed PHP syntax error

### JavaScript Files
- `overall_event_summary.js` - Fixed DataTables column definitions

### SQL Scripts
- `seed_data.sql` - Original seed data
- `seed_data_corrected.sql` - Corrected seed data
- `rhombus_schema.sql` - Database schema
- `complete_database_backup.sql` - Complete database backup script

## Key Changes Made

### 1. Development Bypass Implementation
- Added `SOCOM_DEV_BYPASS_AUTH=TRUE` environment variable
- Modified authentication helpers to bypass session requirements in development
- Updated controllers to handle missing session data gracefully

### 2. Database Schema Fixes
- Renamed `usr_dt_uploads` to `USR_DT_UPLOADS` for case sensitivity
- Created missing tables: `USR_DT_SCHEDULER_MAP`, `USR_DT_SCHEDULER`
- Added missing columns: `ID`, `UPDATE_USER_ID`, `ASSESSMENT_AREA_CODE`
- Created COA management tables: `USR_LOOKUP_SAVED_COA`, `USR_LOOKUP_USER_SAVED_COA`, `USR_LOOKUP_USER_SHARED_COA`
- Created `USR_EVENT_FUNDING_LINES` table for event summaries

### 3. Data Population
- Added comprehensive seed data for all lookup tables
- Populated missing sponsor codes, assessment areas, resource categories
- Added sample data for event summaries and funding lines

### 4. Error Fixes
- Fixed DataTables Ajax errors on import_upload page
- Fixed PHP syntax errors in view files
- Fixed missing model dependencies in controllers
- Fixed session handling issues

## Restoration Instructions

### Database Restoration
```bash
# Restore complete database
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password < database_backup.sql

# Or apply schema changes only
docker exec -i rhombus-mysql mysql -u rhombus_user -prhombus_password SOCOM_UI < sql/complete_database_backup.sql
```

### File Restoration
```bash
# Restore configuration files
cp docker-compose.yml /path/to/project/
cp my.cnf /path/to/project/mysql-config/

# Restore PHP files
cp controllers/* /path/to/project/php-main/application/controllers/SOCOM/
cp models/* /path/to/project/php-main/application/models/
cp helpers/* /path/to/project/php-main/application/helpers/
cp views/* /path/to/project/php-main/application/views/SOCOM/

# Restore JavaScript files
cp javascript/* /path/to/project/php-main/assets/js/actions/SOCOM/
```

## Environment Variables Required
```bash
SOCOM_DEV_BYPASS_AUTH=TRUE
SOCOM_DEV_MODE=TRUE
SOCOM_DISABLE_STRICT_SQL=TRUE
```

## Notes
- This backup includes all changes made during the development session
- The database backup contains the current state with all fixes applied
- All Ajax errors and DataTables issues have been resolved
- Development bypass is enabled for testing without authentication
EOF

# 7. Create restore script
echo "🔧 Creating restore script..."
cat > "${BACKUP_DIR}/${BACKUP_NAME}/restore_backup.sh" << EOF
#!/bin/bash
# Restore script for backup: ${BACKUP_NAME}

echo "Restoring SOCOM application from backup: ${BACKUP_NAME}"

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
EOF

chmod +x "${BACKUP_DIR}/${BACKUP_NAME}/restore_backup.sh"

# 8. Create archive
echo "📦 Creating backup archive..."
cd "${BACKUP_DIR}"
tar -czf "${BACKUP_NAME}.tar.gz" "${BACKUP_NAME}"

# Show backup information
echo ""
echo "✅ Comprehensive backup completed successfully!"
echo "📁 Backup location: ${BACKUP_DIR}/${BACKUP_NAME}"
echo "📦 Archive location: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
echo ""
echo "📊 Backup contents:"
echo "   - Database backup (${BACKUP_NAME}/database_backup.sql)"
echo "   - Configuration files (${BACKUP_NAME}/)"
echo "   - Modified PHP files (${BACKUP_NAME}/controllers/, models/, helpers/, views/)"
echo "   - JavaScript files (${BACKUP_NAME}/javascript/)"
echo "   - SQL scripts (${BACKUP_NAME}/sql/)"
echo "   - Backup manifest (${BACKUP_NAME}/BACKUP_MANIFEST.md)"
echo "   - Restore script (${BACKUP_NAME}/restore_backup.sh)"
echo ""
echo "🔄 To restore this backup, run:"
echo "   cd ${BACKUP_DIR}/${BACKUP_NAME}"
echo "   ./restore_backup.sh"
echo ""
echo "📋 Backup process completed at $(date)"
