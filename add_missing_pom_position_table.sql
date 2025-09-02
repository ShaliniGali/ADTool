-- Add missing USR_LOOKUP_POM_POSITION table and seed data
-- This table is required by the SOCOM application

USE SOCOM_UI;

-- Create the missing USR_LOOKUP_POM_POSITION table
CREATE TABLE IF NOT EXISTS USR_LOOKUP_POM_POSITION (
  `ID` int NOT NULL AUTO_INCREMENT,
  `POM_YEAR` int DEFAULT '2024',
  `LATEST_POSITION` varchar(50) DEFAULT 'POSITION_1',
  `POSITION_TITLE` varchar(255) NOT NULL,
  `POSITION_DESCRIPTION` text,
  `POSITION_GRADE` varchar(10) DEFAULT NULL,
  `POSITION_CATEGORY` varchar(100) DEFAULT NULL,
  `IS_ACTIVE` tinyint(1) DEFAULT '1',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create the missing USR_LOOKUP_POM_POSITION_CYCLE table
CREATE TABLE IF NOT EXISTS USR_LOOKUP_POM_POSITION_CYCLE (
  `CYCLE_MAPPING_ID` int NOT NULL AUTO_INCREMENT,
  `LOOKUP_ID` int DEFAULT NULL,
  `CYCLE_ID` int DEFAULT NULL,
  `POSITION_COUNT` int DEFAULT '1',
  `POSITION_STATUS` enum('ACTIVE','INACTIVE','FILLED','VACANT') DEFAULT 'VACANT',
  `CREATED_AT` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `POM_ID` int DEFAULT NULL,
  PRIMARY KEY (`CYCLE_MAPPING_ID`),
  KEY `LOOKUP_ID` (`LOOKUP_ID`),
  KEY `CYCLE_ID` (`CYCLE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate USR_LOOKUP_POM_POSITION with seed data from backup
INSERT IGNORE INTO USR_LOOKUP_POM_POSITION VALUES 
(1,2024,'POSITION_1','Special Forces Team Leader','Leads special operations team in field operations','E-7','COMBAT',1,'2025-08-30 23:05:42'),
(2,2024,'POSITION_1','Intelligence Analyst','Analyzes intelligence data for operations planning','E-6','INTELLIGENCE',1,'2025-08-30 23:05:42'),
(3,2024,'POSITION_1','Communications Specialist','Manages communications equipment and systems','E-5','COMMUNICATIONS',1,'2025-08-30 23:05:42'),
(4,2024,'POSITION_1','Medical Sergeant','Provides medical support in field operations','E-6','MEDICAL',1,'2025-08-30 23:05:42'),
(5,2024,'POSITION_1','Logistics Coordinator','Coordinates logistics and supply operations','E-7','LOGISTICS',1,'2025-08-30 23:05:42'),
(6,2024,'POSITION_1','Sample Position 1','Test position for development','GS-13','MANAGEMENT',1,'2025-08-31 00:14:26');

-- Populate USR_LOOKUP_POM_POSITION_CYCLE with seed data from backup
INSERT IGNORE INTO USR_LOOKUP_POM_POSITION_CYCLE VALUES 
(1,1,1,1,'ACTIVE','2025-08-31 14:14:32',1);

-- Verify the tables were created and populated
SELECT 'USR_LOOKUP_POM_POSITION' as table_name, COUNT(*) as record_count FROM USR_LOOKUP_POM_POSITION
UNION ALL
SELECT 'USR_LOOKUP_POM_POSITION_CYCLE' as table_name, COUNT(*) as record_count FROM USR_LOOKUP_POM_POSITION_CYCLE;


