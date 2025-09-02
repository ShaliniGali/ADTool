-- Add missing LOOKUP_POM_POSITION_DECREMENT table and seed data
-- This table is required by the SOCOM application for ZBT and issue tiles
-- Using exact structure and data from the working backup file

USE SOCOM_UI;

-- Create the missing LOOKUP_POM_POSITION_DECREMENT table
CREATE TABLE IF NOT EXISTS LOOKUP_POM_POSITION_DECREMENT (
  `POSITION` varchar(30) DEFAULT NULL,
  `SUBAPP` varchar(30) DEFAULT NULL,
  `EXT_DECR` tinyint DEFAULT NULL,
  `ZBT_DECR` tinyint DEFAULT NULL,
  `ISS_DECR` tinyint DEFAULT NULL,
  `POM_DECR` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate LOOKUP_POM_POSITION_DECREMENT with seed data from backup
INSERT IGNORE INTO LOOKUP_POM_POSITION_DECREMENT VALUES 
('EXT','ZBT_SUMMARY',0,1,1,1),
('ZBT','ISS_SUMMARY',0,1,1,1),
('ISS','RESOURCE_CONSTRAINED_COA',0,0,0,1),
('POM',NULL,0,0,0,0),
('POSITION_1','POM_POSITION_1',0,1,1,0);

-- Verify the table was created and populated
SELECT 'LOOKUP_POM_POSITION_DECREMENT' as table_name, COUNT(*) as record_count FROM LOOKUP_POM_POSITION_DECREMENT;
