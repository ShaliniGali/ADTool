SET @dbname = DATABASE();
SET @tablename = 'USR_DT_LOOKUP_METADATA';
SET @newcol = 'DIRTY_TABLE_NAME';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " ADD COLUMN `DIRTY_TABLE_NAME` VARCHAR(50) NOT NULL AFTER `TABLE_NAME`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_DT_LOOKUP_METADATA';
SET @newcol = 'IS_ACTIVE';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " ADD COLUMN `IS_ACTIVE` TINYINT NOT NULL DEFAULT 0 AFTER `UPDATED_DATETIME`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_LOOKUP_SAVED_COA';
SET @newcol = 'TYPE_OF_COA';
SET @newtype = "ENUM('ISS','ISS_EXTRACT','RC_T')";
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol  AND COLUMN_TYPE=@newtype;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " CHANGE COLUMN `TYPE_OF_COA` `TYPE_OF_COA` ENUM('ISS','ISS_EXTRACT','RC_T') NOT NULL DEFAULT 'RC_T';"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_OPTION_SCORES';
SET @newcol = 'TYPE_OF_COA';
SET @newtype = "ENUM('ISS_EXTRACT','RC_T')";
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " ADD COLUMN `TYPE_OF_COA` ENUM('ISS_EXTRACT', 'RC_T') NOT NULL DEFAULT 'ISS_EXTRACT' AFTER `PROGRAM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_OPTION_SCORES_HISTORY';
SET @newcol = 'TYPE_OF_COA';
SET @newtype = "ENUM('ISS_EXTRACT','RC_T')";
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " ADD COLUMN `TYPE_OF_COA` ENUM('ISS_EXTRACT', 'RC_T') NOT NULL DEFAULT 'ISS_EXTRACT' AFTER `PROGRAM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


SET @dbname = DATABASE();
SET @tablename = 'USR_OPTION_SCORES';
SET @newcol = 'PROGRAM_ID';
SET @newtype = "VARCHAR(128)";
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol  AND COLUMN_TYPE=@newtype;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " CHANGE COLUMN `PROGRAM_ID` `PROGRAM_ID` VARCHAR(128) NOT NULL;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


SET @dbname = DATABASE();
SET @tablename = 'USR_OPTION_SCORES_HISTORY';
SET @newcol = 'PROGRAM_ID';
SET @newtype = "VARCHAR(128)";
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol  AND COLUMN_TYPE=@newtype;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ', " CHANGE COLUMN `PROGRAM_ID` `PROGRAM_ID` VARCHAR(128) NOT NULL;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


DELETE FROM USR_LOOKUP_SAVED_COA WHERE TYPE_OF_COA = "ISS";

-- Set database and table name
SET @dbname = DATABASE();
SET @tablename = 'LOOKUP_PROGRAM';

-- Check if the table exists

SELECT COUNT(*) INTO @table_exists
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename;

-- Drop the table if it exists
SET @drop_sql := IF(
@table_exists = 1,
    CONCAT('DROP TABLE ', @dbname, '.', @tablename, ';'),
    'SELECT "Table does not exist, skipping DROP";'
);

PREPARE stmt FROM @drop_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
-- Re-create the table
SET @sql := CONCAT(
  'CREATE TABLE ', @dbname, '.', @tablename, ' (
    `ID` varchar(128) NOT NULL,
    `CAPABILITY_SPONSOR_CODE` varchar(13) DEFAULT NULL,
    `POM_SPONSOR_CODE` varchar(13) DEFAULT NULL,
    `ASSESSMENT_AREA_CODE` varchar(1) DEFAULT NULL,
    `EXECUTION_MANAGER_CODE` varchar(13) DEFAULT NULL,
    `PROGRAM_GROUP` varchar(13) DEFAULT NULL,
    `PROGRAM_CODE` varchar(11) DEFAULT NULL,
    `EOC_CODE` varchar(15) DEFAULT NULL,
    `RESOURCE_CATEGORY_CODE` varchar(8) DEFAULT NULL,
    `OSD_PROGRAM_ELEMENT_CODE` varchar(10) DEFAULT NULL,
    `EVENT_NAME` varchar(60) DEFAULT NULL,
    `PROGRAM_NAME` varchar(60) DEFAULT NULL,
    `PROGRAM_TYPE_CODE` varchar(1) DEFAULT NULL,
    `PROGRAM_SUB_TYPE_CODE` varchar(5) DEFAULT NULL,
    `PROGRAM_DESCRIPTION` varchar(10000) DEFAULT NULL,
    `STORM_ID` varchar(25) DEFAULT NULL,
    `JCA` json DEFAULT NULL,
    `KOP_KSP` json DEFAULT NULL,
    `CGA` json DEFAULT NULL,
    PRIMARY KEY (`ID`),
    KEY `idx_capability_sponsor_code` (`CAPABILITY_SPONSOR_CODE`),
    KEY `idx_pom_sponsor_code` (`POM_SPONSOR_CODE`),
    KEY `idx_assessment_area_code` (`ASSESSMENT_AREA_CODE`),
    KEY `idx_execution_manager_code` (`EXECUTION_MANAGER_CODE`),
    KEY `idx_program_group` (`PROGRAM_GROUP`),
    KEY `idx_program_code` (`PROGRAM_CODE`),
    KEY `idx_eoc_code` (`EOC_CODE`),
    KEY `idx_resource_category_code` (`RESOURCE_CATEGORY_CODE`),
    KEY `idx_osd_program_element_code` (`OSD_PROGRAM_ELEMENT_CODE`),
    KEY `idx_event_name` (`EVENT_NAME`),
    KEY `idx_program_name` (`PROGRAM_NAME`),
    KEY `idx_program_type_code` (`PROGRAM_TYPE_CODE`),
    KEY `idx_program_sub_type_code` (`PROGRAM_SUB_TYPE_CODE`),
    KEY `idx_storm_id` (`STORM_ID`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;