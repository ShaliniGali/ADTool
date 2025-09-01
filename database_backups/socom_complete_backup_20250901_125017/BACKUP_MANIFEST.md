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
