Michael Alaimo going forward, we need to follow this format of sql for ALTER - ADD/DELETE statement. Otherwise, multiple runs of the sql will cause issues and break the pipeline. 
 
 
 
SET @dbname = DATABASE();
SET @tablename = 'USR_LOOKUP_SAVED_COA';
SET @newcol = 'APP_VERSION';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `APP_VERSION` VARCHAR(45) NOT NULL DEFAULT '1.4.0' AFTER `IS_DELETED`,","ADD COLUMN `TYPE_OF_COA` ENUM('ISS', 'ISS_EXTRACT') NOT NULL DEFAULT 'ISS' AFTER `APP_VERSION`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ZBT_AD_SAVES';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ZBT_AD_SAVES_HISTORY';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ZBT_AO_SAVES';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ZBT_AO_SAVES_HISTORY';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ISSUE_AD_SAVES';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ISSUE_AD_SAVES_HISTORY';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ISSUE_AO_SAVES';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @dbname = DATABASE();
SET @tablename = 'USR_ISSUE_AO_SAVES_HISTORY';
SET @newcol = 'POM_ID';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `POM_ID` INT NOT NULL DEFAULT 1 AFTER `EVENT_ID`,","ADD COLUMN `IS_DELETED` TINYINT NOT NULL DEFAULT 0 AFTER `POM_ID`;"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
 

SET @dbname = DATABASE();
SET @tablename = 'USR_LOOKUP_SAVED_COA';
SET @newcol = 'STORM_FLAG';
SELECT count(*) INTO @updated FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname and TABLE_NAME=@tablename and COLUMN_NAME=@newcol;
SET @sql := IF(@updated = 1,'SELECT "Table already updated";',CONCAT('ALTER TABLE ', @dbname, '.', @tablename, ' ',"ADD COLUMN `STORM_FLAG` TINYINT NOT NULL DEFAULT 0 AFTER `TYPE_OF_COA`"));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
