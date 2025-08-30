from socom import summary as test
from api.models import IssSummaryFilterInputModel, ZbtSummaryFilterInputModel
from socom.summary import ZBTQuery, ISSQuery
import asyncio
import pytest

SCHEMA = 'SOCOM_UI'

class SQLiteZBTQuery(ZBTQuery):
  approval_query = """
      SELECT
          DISTINCT LUT.PROGRAM_GROUP, LUT.PROGRAM_NAME,
          CASE 
              WHEN SUM(CASE WHEN EXTRACT.EVENT_STATUS LIKE 'NOT DECIDED' THEN 1 ELSE 0 END) = 0 THEN 'COMPLETED'
              WHEN SUM(CASE WHEN EXTRACT.EVENT_STATUS = 'NOT DECIDED' THEN 1 ELSE 0 END) > 0 THEN 'PENDING'
          END AS APPROVAL_ACTION_STATUS
      FROM (
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              `EVENT_STATUS`
          FROM
              {schema}.DT_ZBT_EXTRACT_2027
          UNION ALL
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              'DECIDED' AS EVENT_STATUS
          FROM
              {schema}.`DT_EXT_2027`
          WHERE (
              PROGRAM_CODE NOT IN (
                  SELECT DISTINCT PROGRAM_CODE
                  FROM {schema}.DT_ZBT_EXTRACT_2027
              )
          )
          UNION ALL
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              'DECIDED' AS `EVENT_STATUS`
          FROM
              {schema}.`DT_ZBT_2026`
          WHERE (
              PROGRAM_CODE NOT IN (
                  SELECT DISTINCT PROGRAM_CODE
                  FROM {schema}.DT_ISS_EXTRACT_2026
              )
          )
      ) AS EXTRACT
      LEFT JOIN (
          SELECT
              `PROGRAM_NAME`,
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `POM_SPONSOR_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`
          FROM
              {schema}.`LOOKUP_PROGRAM`
      ) AS LUT ON EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
      AND EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
      AND EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
      AND EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
      AND EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
      WHERE LUT.PROGRAM_GROUP IS NOT NULL
      GROUP BY LUT.PROGRAM_NAME
      HAVING APPROVAL_ACTION_STATUS IN ('PENDING', 'COMPLETED')
  """.format(schema=SCHEMA)
  base_k_query = """
      SELECT
          `LUT`.`PROGRAM_NAME`,
          LUT.CAPABILITY_SPONSOR_CODE,
          LUT.ASSESSMENT_AREA_CODE,
          LUT.POM_SPONSOR_CODE,
          '26EXT' AS "26EXT",
          `EXT`.`POM_POSITION_CODE`,
          `EXT`.`FISCAL_YEAR`,
          SUM(EXT.RESOURCE_K) AS BASE_K_SUM,
          (
              SELECT
                  GROUP_CONCAT(FISCAL_YEAR)
              FROM
                  (
                  SELECT DISTINCT FISCAL_YEAR
                  FROM {schema}.`DT_EXT_2027`
                  ORDER BY FISCAL_YEAR
                  )
                   
          ) as FISCAL_YEARS
      FROM
          (
              SELECT
                  *
              FROM
                  {schema}.`DT_EXT_2027`
              UNION ALL
              SELECT
                  0 AS `ADJUSTMENT_K`,
                  `ASSESSMENT_AREA_CODE`,
                  0 AS `BASE_K`,
                  `BUDGET_ACTIVITY_CODE`,
                  `BUDGET_ACTIVITY_NAME`,
                  `BUDGET_SUB_ACTIVITY_CODE`,
                  `BUDGET_SUB_ACTIVITY_NAME`,
                  `CAPABILITY_SPONSOR_CODE`,
                  0 AS `END_STRENGTH`,
                  `EOC_CODE`,
                  `EVENT_JUSTIFICATION`,
                  `EVENT_NAME`,
                  `EXECUTION_MANAGER_CODE`,
                  `FISCAL_YEAR`,
                  `LINE_ITEM_CODE`,
                  0 AS `OCO_OTHD_ADJUSTMENT_K`,
                  0 AS `OCO_OTHD_K`,
                  0 AS `OCO_TO_BASE_K`,
                  `OSD_PROGRAM_ELEMENT_CODE`,
                  "26EXT" AS `POM_POSITION_CODE`,
                  `POM_SPONSOR_CODE`,
                  `PROGRAM_CODE`,
                  `PROGRAM_GROUP`,
                  `RDTE_PROJECT_CODE`,
                  `RESOURCE_CATEGORY_CODE`,
                  0 AS `RESOURCE_K`,
                  `SPECIAL_PROJECT_CODE`,
                  `SUB_ACTIVITY_GROUP_CODE`,
                  `SUB_ACTIVITY_GROUP_NAME`,
                  2024 AS `WORK_YEARS`
              FROM
                  {schema}.`DT_ZBT_EXTRACT_2027`
              WHERE
                  (
                      `PROGRAM_CODE` NOT IN (
                          SELECT
                              DISTINCT PROGRAM_CODE
                          FROM
                              {schema}.DT_EXT_2027
                      )
                  )
          ) AS EXT
          LEFT JOIN (
              SELECT
                  ASSESSMENT_AREA_CODE,
                  PROGRAM_NAME,
                  PROGRAM_GROUP,
                  PROGRAM_CODE,
                  CAPABILITY_SPONSOR_CODE,
                  POM_SPONSOR_CODE
              FROM
                  {schema}.LOOKUP_PROGRAM
          ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
          AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
          AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
          AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
          AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
      WHERE
          `EXT`.`FISCAL_YEAR` IN ('2027', '2028', '2029', '2030','2031')
          AND `LUT`.`PROGRAM_NAME` IS NOT NULL
      GROUP BY
          `LUT`.`PROGRAM_NAME`,
          `EXT`.`POM_POSITION_CODE`,
          `EXT`.`FISCAL_YEAR`
      ORDER BY
          `PROGRAM_NAME`,
          `FISCAL_YEAR`
  """.format(schema=SCHEMA)
  zbt_requested_delta_query = """
      SELECT 
          `LUT`.`PROGRAM_NAME`,
          LUT.CAPABILITY_SPONSOR_CODE,
          LUT.ASSESSMENT_AREA_CODE,
          LUT.POM_SPONSOR_CODE,
          '26ZBT REQUESTED DELTA' AS "26ZBT_REQUESTED_DELTA", 
          `EXT`.`POM_POSITION_CODE`, 
          `EXT`.`FISCAL_YEAR`, 
          SUM(ZBT_EXTRACT.DELTA_AMT) AS DELTA_AMT, 
          (
              SELECT
                  GROUP_CONCAT(FISCAL_YEAR)
              FROM
                  (
                  SELECT DISTINCT FISCAL_YEAR
                  FROM {schema}.`DT_EXT_2027`
                  ORDER BY FISCAL_YEAR
                  )
                   
          ) as FISCAL_YEARS
      FROM 
          ( 
              SELECT 
                  * 
              FROM 
                  {schema}.`DT_EXT_2027` 
              UNION ALL 
              SELECT 
                  0 AS `ADJUSTMENT_K`, 
                  `ASSESSMENT_AREA_CODE`, 
                  0 AS `BASE_K`, 
                  `BUDGET_ACTIVITY_CODE`, 
                  `BUDGET_ACTIVITY_NAME`, 
                  `BUDGET_SUB_ACTIVITY_CODE`, 
                  `BUDGET_SUB_ACTIVITY_NAME`, 
                  `CAPABILITY_SPONSOR_CODE`, 
                  0 AS `END_STRENGTH`, 
                  `EOC_CODE`, 
                  `EVENT_JUSTIFICATION`, 
                  `EVENT_NAME`, 
                  `EXECUTION_MANAGER_CODE`, 
                  `FISCAL_YEAR`, 
                  `LINE_ITEM_CODE`, 
                  0 AS `OCO_OTHD_ADJUSTMENT_K`, 
                  0 AS `OCO_OTHD_K`, 
                  0 AS `OCO_TO_BASE_K`, 
                  `OSD_PROGRAM_ELEMENT_CODE`, 
                  "26EXT" AS `POM_POSITION_CODE`, 
                  `POM_SPONSOR_CODE`, 
                  `PROGRAM_CODE`, 
                  `PROGRAM_GROUP`, 
                  `RDTE_PROJECT_CODE`, 
                  `RESOURCE_CATEGORY_CODE`, 
                  0 AS `RESOURCE_K`, 
                  `SPECIAL_PROJECT_CODE`, 
                  `SUB_ACTIVITY_GROUP_CODE`, 
                  `SUB_ACTIVITY_GROUP_NAME`, 
                  2024 AS `WORK_YEARS` 
              FROM 
                  {schema}.`DT_ZBT_EXTRACT_2027` 
              WHERE 
                  (
                      `PROGRAM_CODE` NOT IN ( 
                          SELECT 
                              DISTINCT PROGRAM_CODE 
                          FROM 
                              {schema}.DT_EXT_2027 
                      )
                      OR `EOC_CODE` NOT IN ( 
                          SELECT 
                              DISTINCT EOC_CODE 
                          FROM 
                              {schema}.DT_ZBT_2026 
                      ) 
                  ) 
          ) AS EXT 
          LEFT JOIN ( 
              SELECT 
                  ASSESSMENT_AREA_CODE, 
                  PROGRAM_NAME, 
                  PROGRAM_GROUP, 
                  PROGRAM_CODE, 
                  CAPABILITY_SPONSOR_CODE, 
                  POM_SPONSOR_CODE 
              FROM 
                  {schema}.LOOKUP_PROGRAM 
          ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
          AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE 
          AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE 
          AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE 
          AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE 
          LEFT JOIN ( 
              SELECT 
                  PROGRAM_CODE, 
                  EOC_CODE, 
                  CAPABILITY_SPONSOR_CODE, 
                  POM_SPONSOR_CODE, 
                  ASSESSMENT_AREA_CODE, 
                  FISCAL_YEAR, 
                  DELTA_AMT, 
                  RESOURCE_K 
              FROM 
                  {schema}.DT_ZBT_EXTRACT_2027
          ) AS ZBT_EXTRACT ON EXT.PROGRAM_CODE = ZBT_EXTRACT.PROGRAM_CODE 
          AND EXT.FISCAL_YEAR = ZBT_EXTRACT.FISCAL_YEAR 
          AND EXT.EOC_CODE = ZBT_EXTRACT.EOC_CODE 
          AND EXT.POM_SPONSOR_CODE = ZBT_EXTRACT.POM_SPONSOR_CODE 
          AND EXT.CAPABILITY_SPONSOR_CODE = ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE 
          AND EXT.ASSESSMENT_AREA_CODE = ZBT_EXTRACT.ASSESSMENT_AREA_CODE 
      WHERE 
          `EXT`.`FISCAL_YEAR` IN ('2027', '2028', '2029', '2030','2031') 
          AND `LUT`.`PROGRAM_NAME` IS NOT NULL 
          AND EXT.EXECUTION_MANAGER_CODE != '' 
      GROUP BY 
          `LUT`.`PROGRAM_NAME`, 
          `EXT`.`POM_POSITION_CODE`, 
          `EXT`.`FISCAL_YEAR` 
      ORDER BY 
          `PROGRAM_NAME`, 
          `EXT`.`FISCAL_YEAR` 
  """.format(schema=SCHEMA)
  jca_query = """
          SELECT 
              A.JCA, 
              B.DESCRIPTION AS JCA_DESCRIPTION, 
              A.PROGRAM_NAME
          FROM 
              {schema}.LOOKUP_PROGRAM_DETAIL A
          LEFT JOIN 
              {schema}.LOOKUP_JCA2 B 
          ON 
              TRIM(REPLACE(REPLACE(A.JCA, '["', ''), '"]', '')) = B.ID;

  """.format(schema=SCHEMA)


class SQLiteISSQuery(ISSQuery):
  approval_query = """
      SELECT
          DISTINCT LUT.PROGRAM_GROUP, LUT.PROGRAM_NAME,
          CASE 
              WHEN SUM(CASE WHEN EXTRACT.EVENT_STATUS LIKE 'NOT DECIDED' THEN 1 ELSE 0 END) = 0 THEN 'COMPLETED'
              WHEN SUM(CASE WHEN EXTRACT.EVENT_STATUS = 'NOT DECIDED' THEN 1 ELSE 0 END) > 0 THEN 'PENDING'
          END AS APPROVAL_ACTION_STATUS
      FROM (
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              `EVENT_STATUS`
          FROM
              {schema}.DT_ISS_EXTRACT_2026
          UNION ALL
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              'DECIDED' AS EVENT_STATUS
          FROM
              {schema}.`DT_EXT_2026`
          WHERE (
              PROGRAM_CODE NOT IN (
                  SELECT DISTINCT PROGRAM_CODE
                  FROM {schema}.DT_ZBT_EXTRACT_2026
              )
          )
          UNION ALL
          SELECT
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `POM_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`,
              'DECIDED' AS `EVENT_STATUS`
          FROM
              {schema}.`DT_ZBT_2026`
          WHERE (
              PROGRAM_CODE NOT IN (
                  SELECT DISTINCT PROGRAM_CODE
                  FROM {schema}.DT_ISS_EXTRACT_2026
              )
          )
      ) AS EXTRACT
      LEFT JOIN (
          SELECT
              `PROGRAM_NAME`,
              `PROGRAM_GROUP`,
              `PROGRAM_CODE`,
              `POM_SPONSOR_CODE`,
              `CAPABILITY_SPONSOR_CODE`,
              `ASSESSMENT_AREA_CODE`
          FROM
              {schema}.`LOOKUP_PROGRAM`
      ) AS LUT ON EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
      AND EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
      AND EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
      AND EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
      AND EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
      WHERE LUT.PROGRAM_GROUP IS NOT NULL
      GROUP BY LUT.PROGRAM_NAME
      HAVING APPROVAL_ACTION_STATUS IN ('PENDING', 'COMPLETED')
  """.format(schema=SCHEMA)
  base_k_query = """
        SELECT
            `LUT`.`PROGRAM_NAME`,
            LUT.CAPABILITY_SPONSOR_CODE,
            LUT.ASSESSMENT_AREA_CODE,
            LUT.POM_SPONSOR_CODE,
            '26EXT' AS "26EXT",
            `EXT`.`POM_POSITION_CODE`,
            `EXT`.`FISCAL_YEAR`,
            SUM(EXT.RESOURCE_K) AS BASE_K_SUM,
            (
              SELECT
                  GROUP_CONCAT(FISCAL_YEAR)
              FROM
                  (
                  SELECT DISTINCT FISCAL_YEAR
                  FROM {schema}.`DT_EXT_2026`
                  ORDER BY FISCAL_YEAR
                  )
                   
          ) as FISCAL_YEARS
        FROM
            (
                SELECT
                    *
                FROM
                    {schema}.`DT_EXT_2026`
                UNION ALL
                SELECT
                    0 AS `ADJUSTMENT_K`,
                    `ASSESSMENT_AREA_CODE`,
                    0 AS `BASE_K`,
                    `BUDGET_ACTIVITY_CODE`,
                    `BUDGET_ACTIVITY_NAME`,
                    `BUDGET_SUB_ACTIVITY_CODE`,
                    `BUDGET_SUB_ACTIVITY_NAME`,
                    `CAPABILITY_SPONSOR_CODE`,
                    0 AS `END_STRENGTH`,
                    `EOC_CODE`,
                    `EVENT_JUSTIFICATION`,
                    `EVENT_NAME`,
                    `EXECUTION_MANAGER_CODE`,
                    `FISCAL_YEAR`,
                    `LINE_ITEM_CODE`,
                    0 AS `OCO_OTHD_ADJUSTMENT_K`,
                    0 AS `OCO_OTHD_K`,
                    0 AS `OCO_TO_BASE_K`,
                    `OSD_PROGRAM_ELEMENT_CODE`,
                    "26EXT" AS `POM_POSITION_CODE`,
                    `POM_SPONSOR_CODE`,
                    `PROGRAM_CODE`,
                    `PROGRAM_GROUP`,
                    `RDTE_PROJECT_CODE`,
                    `RESOURCE_CATEGORY_CODE`,
                    0 AS `RESOURCE_K`,
                    `SPECIAL_PROJECT_CODE`,
                    `SUB_ACTIVITY_GROUP_CODE`,
                    `SUB_ACTIVITY_GROUP_NAME`,
                    2024 AS `WORK_YEARS`
                FROM
                    {schema}.`DT_ISS_EXTRACT_2026`
                WHERE
                    (
                        `PROGRAM_CODE` NOT IN (
                            SELECT
                                DISTINCT PROGRAM_CODE
                            FROM
                                {schema}.DT_EXT_2026
                        )
                    )
            ) AS EXT
            LEFT JOIN (
                SELECT
                    ASSESSMENT_AREA_CODE,
                    PROGRAM_NAME,
                    PROGRAM_GROUP,
                    PROGRAM_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    POM_SPONSOR_CODE
                FROM
                    {schema}.LOOKUP_PROGRAM
            ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
        WHERE
            `EXT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030')
            AND `LUT`.`PROGRAM_NAME` IS NOT NULL
        GROUP BY
            `LUT`.`PROGRAM_NAME`,
            `EXT`.`POM_POSITION_CODE`,
            `EXT`.`FISCAL_YEAR`
        ORDER BY
            `PROGRAM_NAME`,
            `FISCAL_YEAR`
  """.format(schema=SCHEMA)
  iss_requested_delta_query = """

                        SELECT
                
                    LUT.CAPABILITY_SPONSOR_CODE,
                    LUT.ASSESSMENT_AREA_CODE,
                    LUT.POM_SPONSOR_CODE,

                    LUT.PROGRAM_NAME, 

                    ZBT.POM_POSITION_CODE, 

                    '26ISS REQUESTED DELTA' AS "26ISS_REQUESTED_DELTA", 

                    ZBT.FISCAL_YEAR, 

                    SUM(ISS_EXTRACT.DELTA_AMT) AS DELTA_AMT, 
                
                    (
                
                        SELECT
                            GROUP_CONCAT(FISCAL_YEAR)
                        FROM
                            (
                            SELECT DISTINCT FISCAL_YEAR
                            FROM {schema}.`DT_EXT_2026`
                            ORDER BY FISCAL_YEAR
                            )
                            
                    ) as FISCAL_YEARS
                
                FROM
                
                    (
                
                        SELECT
                
                            *
                
                        FROM
                
                            {schema}.DT_ZBT_2026
                

                        UNION ALL
                
                        SELECT
                
                            0 AS ADJUSTMENT_K,
                
                            ASSESSMENT_AREA_CODE,
                
                            0 AS BASE_K,
                
                            BUDGET_ACTIVITY_CODE,
                
                            BUDGET_ACTIVITY_NAME,
                
                            BUDGET_SUB_ACTIVITY_CODE,
                
                            BUDGET_SUB_ACTIVITY_NAME,
                
                            CAPABILITY_SPONSOR_CODE,
                
                            0 AS END_STRENGTH,
                
                            EOC_CODE,
                
                            EVENT_JUSTIFICATION,
                
                            EVENT_NAME,
                
                            EXECUTION_MANAGER_CODE,
                
                            FISCAL_YEAR,
                
                            LINE_ITEM_CODE,
                
                            0 AS OCO_OTHD_ADJUSTMENT_K,
                
                            0 AS OCO_OTHD_K,
                
                            0 AS OCO_TO_BASE_K,
                
                            OSD_PROGRAM_ELEMENT_CODE,
                
                            '26ZBT' AS POM_POSITION_CODE,
                
                            POM_SPONSOR_CODE,
                
                            PROGRAM_CODE,
                
                            PROGRAM_GROUP,
                
                            RDTE_PROJECT_CODE,
                
                            RESOURCE_CATEGORY_CODE,
                
                            0 AS RESOURCE_K,
                
                            SPECIAL_PROJECT_CODE,
                
                            SUB_ACTIVITY_GROUP_CODE,
                
                            SUB_ACTIVITY_GROUP_NAME,
                
                            2024 AS WORK_YEARS
                
                        FROM
                
                            {schema}.DT_ISS_EXTRACT_2026
                
                        WHERE
                
                            (
                
                                PROGRAM_CODE NOT IN (
                
                                    SELECT
                
                                        DISTINCT PROGRAM_CODE
                
                                    FROM
                
                                        {schema}.DT_ZBT_2026
                
                                )
                
                
                                OR EOC_CODE NOT IN (
                
                                    SELECT
                
                                        DISTINCT EOC_CODE
                
                                    FROM
                
                                        {schema}.DT_ZBT_2026
                
                                )
                
                            )
                

                        ) AS ZBT
                
                    LEFT JOIN (
                
                        SELECT
                
                            POM_SPONSOR_CODE,
                
                            CAPABILITY_SPONSOR_CODE,
                
                            ASSESSMENT_AREA_CODE,
                
                            PROGRAM_NAME,
                
                            PROGRAM_GROUP,
                
                            PROGRAM_CODE
                
                        FROM
                
                            {schema}.LOOKUP_PROGRAM
                
                    ) AS LUT ON ZBT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                
                    AND ZBT.PROGRAM_CODE = LUT.PROGRAM_CODE
                
                    AND ZBT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                
                    AND ZBT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                
                    AND ZBT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
                
                    LEFT JOIN (
                
                        SELECT
                
                            PROGRAM_CODE,
                
                            EOC_CODE,
                
                            CAPABILITY_SPONSOR_CODE,
                
                            POM_SPONSOR_CODE,
                
                            ASSESSMENT_AREA_CODE,
                
                            FISCAL_YEAR,
                
                            DELTA_AMT,
                
                EXECUTION_MANAGER_CODE
                
                        FROM
                
                            {schema}.DT_ISS_EXTRACT_2026

                
                    ) as ISS_EXTRACT ON ZBT.PROGRAM_CODE = ISS_EXTRACT.PROGRAM_CODE
                
                    AND ZBT.FISCAL_YEAR = ISS_EXTRACT.FISCAL_YEAR
                
                    AND ZBT.EOC_CODE = ISS_EXTRACT.EOC_CODE
                
                    AND ZBT.POM_SPONSOR_CODE = ISS_EXTRACT.POM_SPONSOR_CODE
                
                    AND ZBT.CAPABILITY_SPONSOR_CODE = ISS_EXTRACT.CAPABILITY_SPONSOR_CODE
                
                    AND ZBT.ASSESSMENT_AREA_CODE = ISS_EXTRACT.ASSESSMENT_AREA_CODE
                
                    AND ZBT.EXECUTION_MANAGER_CODE = ISS_EXTRACT.EXECUTION_MANAGER_CODE
                
                
                WHERE
                
                    ZBT.FISCAL_YEAR IN ('2026', '2027', '2028', '2029', '2030')
                
                    AND LUT.PROGRAM_NAME IS NOT NULL
                
                    AND ZBT.EXECUTION_MANAGER_CODE != ''
                
                GROUP BY
                
                    LUT.PROGRAM_NAME,
                
                    ZBT.POM_POSITION_CODE,
                
                    ZBT.FISCAL_YEAR
                
                ORDER BY
                
                    PROGRAM_NAME,
                
                    ZBT.FISCAL_YEAR
  """.format(schema=SCHEMA)
  iss_k_query = """
      SELECT
          `LUT`.`PROGRAM_NAME`,
          LUT.CAPABILITY_SPONSOR_CODE,
          LUT.ASSESSMENT_AREA_CODE,
          LUT.POM_SPONSOR_CODE, 
          '26ZBT' AS "26ZBT_REQUESTED", 
          `EXT`.`POM_POSITION_CODE`, 
          `EXT`.`FISCAL_YEAR`, 
          SUM(EXT.RESOURCE_K) AS PROP_AMT, 
          (
              SELECT
                  GROUP_CONCAT(FISCAL_YEAR)
              FROM
                  (
                  SELECT DISTINCT FISCAL_YEAR
                  FROM {schema}.`DT_EXT_2026`
                  ORDER BY FISCAL_YEAR
                  )
                   
          ) as FISCAL_YEARS 
      FROM
          ( 
              SELECT 
                  * 
              FROM 
                  {schema}.DT_ZBT_2026 
              UNION ALL 
              SELECT 
                  0 AS `ADJUSTMENT_K`, 
                  `ASSESSMENT_AREA_CODE`, 
                  0 AS `BASE_K`, 
                  `BUDGET_ACTIVITY_CODE`, 
                  `BUDGET_ACTIVITY_NAME`, 
                  `BUDGET_SUB_ACTIVITY_CODE`, 
                  `BUDGET_SUB_ACTIVITY_NAME`, 
                  `CAPABILITY_SPONSOR_CODE`, 
                  0 AS `END_STRENGTH`, 
                  `EOC_CODE`, 
                  `EVENT_JUSTIFICATION`, 
                  `EVENT_NAME`, 
                  `EXECUTION_MANAGER_CODE`, 
                  `FISCAL_YEAR`, 
                  `LINE_ITEM_CODE`, 
                  0 AS `OCO_OTHD_ADJUSTMENT_K`, 
                  0 AS `OCO_OTHD_K`, 
                  0 AS `OCO_TO_BASE_K`, 
                  `OSD_PROGRAM_ELEMENT_CODE`, 
                  '26ZBT' AS `POM_POSITION_CODE`, 
                  `POM_SPONSOR_CODE`, 
                  `PROGRAM_CODE`, 
                  `PROGRAM_GROUP`, 
                  `RDTE_PROJECT_CODE`, 
                  `RESOURCE_CATEGORY_CODE`, 
                  0 AS `RESOURCE_K`, 
                  `SPECIAL_PROJECT_CODE`, 
                  `SUB_ACTIVITY_GROUP_CODE`, 
                  `SUB_ACTIVITY_GROUP_NAME`, 
                  2024 AS `WORK_YEARS`
              FROM 
                  {schema}.DT_ISS_EXTRACT_2026
              WHERE 
                  (
                      `PROGRAM_CODE` NOT IN ( 
                          SELECT 
                              DISTINCT PROGRAM_CODE 
                          FROM 
                              {schema}.DT_ZBT_2026 
                      )
                  )
              UNION ALL 
              SELECT 
                  0 AS `ADJUSTMENT_K`, 
                  `ASSESSMENT_AREA_CODE`, 
                  0 AS `BASE_K`, 
                  `BUDGET_ACTIVITY_CODE`, 
                  `BUDGET_ACTIVITY_NAME`, 
                  `BUDGET_SUB_ACTIVITY_CODE`, 
                  `BUDGET_SUB_ACTIVITY_NAME`, 
                  `CAPABILITY_SPONSOR_CODE`, 
                  0 AS `END_STRENGTH`, 
                  `EOC_CODE`, 
                  `EVENT_JUSTIFICATION`, 
                  `EVENT_NAME`, 
                  `EXECUTION_MANAGER_CODE`, 
                  `FISCAL_YEAR`, 
                  `LINE_ITEM_CODE`, 
                  0 AS `OCO_OTHD_ADJUSTMENT_K`, 
                  0 AS `OCO_OTHD_K`, 
                  0 AS `OCO_TO_BASE_K`, 
                  `OSD_PROGRAM_ELEMENT_CODE`, 
                  '26ZBT' AS `POM_POSITION_CODE`, 
                  `POM_SPONSOR_CODE`, 
                  `PROGRAM_CODE`, 
                  `PROGRAM_GROUP`, 
                  `RDTE_PROJECT_CODE`, 
                  `RESOURCE_CATEGORY_CODE`, 
                  0 AS `RESOURCE_K`, 
                  `SPECIAL_PROJECT_CODE`, 
                  `SUB_ACTIVITY_GROUP_CODE`, 
                  `SUB_ACTIVITY_GROUP_NAME`, 
                  2024 AS `WORK_YEARS`
              FROM 
                  {schema}.DT_EXT_2026
              WHERE 
                  (
                      `PROGRAM_CODE` NOT IN ( 
                          SELECT 
                              DISTINCT PROGRAM_CODE 
                          FROM 
                              {schema}.DT_ZBT_2026 
                      )
                  )
          ) AS EXT 
          LEFT JOIN (
              SELECT 
                  POM_SPONSOR_CODE, 
                  CAPABILITY_SPONSOR_CODE, 
                  ASSESSMENT_AREA_CODE, 
                  PROGRAM_NAME, 
                  PROGRAM_GROUP, 
                  PROGRAM_CODE 
              FROM 
                  {schema}.LOOKUP_PROGRAM 
          ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
          AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE 
          AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE 
          AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE 
          AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE 
      WHERE 
          `EXT`.`FISCAL_YEAR` IN ('2026', '2027', '2028', '2029', '2030') 
          AND `LUT`.`PROGRAM_NAME` IS NOT NULL 
      GROUP BY 
          `LUT`.`PROGRAM_NAME`, 
          `EXT`.`POM_POSITION_CODE`, 
          `EXT`.`FISCAL_YEAR` 
      ORDER BY 
          `PROGRAM_NAME`, 
          `FISCAL_YEAR` 
  """.format(schema=SCHEMA)

  jca_query = """
          SELECT 
              A.JCA, 
              B.DESCRIPTION AS JCA_DESCRIPTION, 
              A.PROGRAM_NAME
          FROM 
              {schema}.LOOKUP_PROGRAM_DETAIL A
          LEFT JOIN 
              {schema}.LOOKUP_JCA2 B 
          ON 
              TRIM(REPLACE(REPLACE(A.JCA, '["', ''), '"]', '')) = B.ID;

  """.format(schema=SCHEMA)

def transform_result_zbt(data):
    def convert_resource_k(resource_k):
        for key in resource_k:
            for year in resource_k[key]:
                resource_k[key][year] = int(resource_k[key][year])
        return resource_k

    def convert_jca_alignment(jca_alignment):
        if isinstance(jca_alignment, set):
            jca_alignment = list(jca_alignment)
        jca_alignment.sort()
        return jca_alignment

    def format_fiscal_years(fiscal_years):
        return ", ".join(fiscal_years.split(','))

    transformed_data = []
    
    for entry in data:
        transformed_entry = entry.copy()
        transformed_entry['RESOURCE_K'] = convert_resource_k(entry['RESOURCE_K'])
        transformed_entry['JCA_ALIGNMENT'] = convert_jca_alignment(entry['JCA_ALIGNMENT'])
        transformed_entry['FISCAL_YEARS'] = format_fiscal_years(entry['FISCAL_YEARS'])
        transformed_data.append(transformed_entry)
    
    return transformed_data

def transform_result_iss(data):
    def convert_jca_alignment(jca_alignment):
        jca_alignment = sorted(list(jca_alignment))
        return jca_alignment

    def convert_resource_k(resource_k):
        for key in resource_k:
            for year in resource_k[key]:
                resource_k[key][year] = int(resource_k[key][year])
        return resource_k

    def format_fiscal_years(fiscal_years):
        return ", ".join(fiscal_years.split(','))
      
    transformed_data = []

    for entry in data:
        if isinstance(entry, dict):
            transformed_entry = entry.copy()
            transformed_entry['JCA_ALIGNMENT'] = convert_jca_alignment(entry['JCA_ALIGNMENT'])
            transformed_entry['RESOURCE_K'] = convert_resource_k(entry['RESOURCE_K'])
            transformed_entry['FISCAL_YEARS'] = format_fiscal_years(entry['FISCAL_YEARS'])
            transformed_data.append(transformed_entry)
        else:
            transformed_data.append(entry)

    return transformed_data

def normalize_dict_values(d):
    
      for key, value in d.items():
          if isinstance(value, str):
              d[key] = set(value.replace(" ", "").split(',')) if ',' in value else value.strip()
          elif isinstance(value, dict):
              d[key] = normalize_dict_values(value)
          elif isinstance(value, set):
              d[key] = set(value)
      return d

def test_get_zbt_summary_fromdb(socom_session):

    zbt_model_dict = {
        "CAPABILITY_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE" : ["A", "B", "C", "D", "E"],
        "PROGRAM_GROUP" : ["STUFF"]
    }
  
    model = ZbtSummaryFilterInputModel.parse_obj(zbt_model_dict)

    zbt_query = SQLiteZBTQuery()
    result = test.get_zbt_summary_fromdb(model,zbt_query, socom_session)
    result_trans_zbt = transform_result_zbt(result)

    expected_result = [
            {
              "PROGRAM_NAME": "STUFF ARMADILLO",
              "EOC_CODES": [
                "STUFFAA.QWE",
                "STUFFAA.XXX",
                "STUFFAA.QWR"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 36162,
                  "2028": 30430,
                  "2029": 13010,
                  "2030": 13030,
                  "2031": 13050
                },
                "27ZBT_REQUESTED": {
                  "2027": 28755,
                  "2028": 26846,
                  "2029": 11709,
                  "2030": 11504,
                  "2031": 11299
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -7407,
                  "2028": -3584,
                  "2029": -1301,
                  "2030": -1526,
                  "2031": -1751
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF CAT",
              "EOC_CODES": [
                "STUFFCAT.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE APPLICATION, MANEUVER, MANEUVER TO ENGAGE (MTE)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 11591,
                  "2028": 11645,
                  "2029": 11699,
                  "2030": 11753,
                  "2031": 11807
                },
                "27ZBT_REQUESTED": {
                  "2027": 12912,
                  "2028": 13259,
                  "2029": 13607,
                  "2030": 13955,
                  "2031": 14303
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 1321,
                  "2028": 1614,
                  "2029": 1908,
                  "2030": 2202,
                  "2031": 2496
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF CRICKET",
              "EOC_CODES": [
                "STUFFCRI.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 10460,
                  "2028": 10520,
                  "2029": 10580,
                  "2030": 10640,
                  "2031": 10700
                },
                "27ZBT_REQUESTED": {
                  "2027": 9866,
                  "2028": 9590,
                  "2029": 9314,
                  "2030": 9038,
                  "2031": 8761
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -594,
                  "2028": -930,
                  "2029": -1266,
                  "2030": -1602,
                  "2031": -1939
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF GRASSHOPPER",
              "EOC_CODES": [
                "STUFFGRA.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BUILDING PARTNERSHIPS, COMMUNICATE",
                "FORCE APPLICATION",
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 18068,
                  "2028": 18214,
                  "2029": 18360,
                  "2030": 18506,
                  "2031": 18652
                },
                "27ZBT_REQUESTED": {
                  "2027": 20661,
                  "2028": 21486,
                  "2029": 22311,
                  "2030": 23137,
                  "2031": 23962
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 2593,
                  "2028": 3272,
                  "2029": 3951,
                  "2030": 4631,
                  "2031": 5310
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF HOPEFUL6",
              "EOC_CODES": [
                "STUFFHOP.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, BA DATA DISSEMINATION AND RELAY"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 17516,
                  "2028": 17608,
                  "2029": 17700,
                  "2030": 17792,
                  "2031": 17884
                },
                "27ZBT_REQUESTED": {
                  "2027": 19503,
                  "2028": 20405,
                  "2029": 21307,
                  "2030": 22209,
                  "2031": 23111
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 1987,
                  "2028": 2797,
                  "2029": 3607,
                  "2030": 4417,
                  "2031": 5227
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF JACKAL",
              "EOC_CODES": [
                "STUFFJJ.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE SUPPORT, FORCE PREPARATION, DOCTRINE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF JEALOUS9",
              "EOC_CODES": [
                "STUFFJEA.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, PRODUCT GENERATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 8828,
                  "2028": 8959,
                  "2029": 9090,
                  "2030": 9221,
                  "2031": 9352
                },
                "27ZBT_REQUESTED": {
                  "2027": 7948,
                  "2028": 7822,
                  "2029": 7695,
                  "2030": 7569,
                  "2031": 7442
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -880,
                  "2028": -1137,
                  "2029": -1395,
                  "2030": -1652,
                  "2031": -1910
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF KANGAROO",
              "EOC_CODES": [
                "STUFFKK.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, PRODUCT GENERATION (AP)"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 570,
                  "2028": 585,
                  "2029": 600,
                  "2030": 615,
                  "2031": 630
                },
                "27ZBT_REQUESTED": {
                  "2027": 684,
                  "2028": 722,
                  "2029": 761,
                  "2030": 800,
                  "2031": 839
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 114,
                  "2028": 137,
                  "2029": 161,
                  "2030": 185,
                  "2031": 209
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF LEOPARD2",
              "EOC_CODES": [
                "STUFFLEO.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE",
                "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 4485,
                  "2028": 4509,
                  "2029": 4533,
                  "2030": 4557,
                  "2031": 4581
                },
                "27ZBT_REQUESTED": {
                  "2027": 4710,
                  "2028": 4921,
                  "2029": 5133,
                  "2030": 5345,
                  "2031": 5556
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 225,
                  "2028": 412,
                  "2029": 600,
                  "2030": 788,
                  "2031": 975
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF LONELY",
              "EOC_CODES": [
                "STUFFLON.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, ACCOUNTING AND FINANCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 13049,
                  "2028": 13081,
                  "2029": 13113,
                  "2030": 13145,
                  "2031": 13177
                },
                "27ZBT_REQUESTED": {
                  "2027": 13733,
                  "2028": 14231,
                  "2029": 14730,
                  "2030": 15228,
                  "2031": 15727
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 684,
                  "2028": 1150,
                  "2029": 1617,
                  "2030": 2083,
                  "2031": 2550
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF MUG7",
              "EOC_CODES": [
                "STUFFMUG.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 1884,
                  "2028": 1916,
                  "2029": 1948,
                  "2030": 1980,
                  "2031": 2012
                },
                "27ZBT_REQUESTED": {
                  "2027": 1623,
                  "2028": 1613,
                  "2029": 1603,
                  "2030": 1592,
                  "2031": 1582
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -261,
                  "2028": -303,
                  "2029": -345,
                  "2030": -388,
                  "2031": -430
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF NEW HAMPSHIRE 1",
              "EOC_CODES": [
                "STUFFNH.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "LOGISTICS, MAINTAIN, FIELD MAINTENANCE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 805,
                  "2028": 905,
                  "2029": 1005,
                  "2030": 1015,
                  "2031": 1025
                },
                "27ZBT_REQUESTED": {
                  "2027": 725,
                  "2028": 804,
                  "2029": 883,
                  "2030": 871,
                  "2031": 860
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": -80,
                  "2028": -101,
                  "2029": -122,
                  "2030": -144,
                  "2031": -165
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF NEW HAMPSHIRE 2",
              "EOC_CODES": [
                "STUFFNZ.YYZ",
                "STUFFNZ.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "BUILDING PARTNERSHIPS, SHAPE, LEVERAGE CAPACITIES AND CAPABILITIES OF SECURITY ESTABLISHMENTS"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 508,
                  "2028": 518,
                  "2029": 528,
                  "2030": 538,
                  "2031": 548
                },
                "27ZBT_REQUESTED": {
                  "2027": 668,
                  "2028": 702,
                  "2029": 737,
                  "2030": 772,
                  "2031": 807
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 160,
                  "2028": 184,
                  "2029": 209,
                  "2030": 234,
                  "2031": 259
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF RABBIT4",
              "EOC_CODES": [
                "STUFFRAB.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "FORCE SUPPORT, FORCE PREPARATION, DOCTRINE"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 6753,
                  "2028": 6849,
                  "2029": 6945,
                  "2030": 7041,
                  "2031": 7137
                },
                "27ZBT_REQUESTED": {
                  "2027": 7675,
                  "2028": 7979,
                  "2029": 8284,
                  "2030": 8588,
                  "2031": 8893
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 922,
                  "2028": 1130,
                  "2029": 1339,
                  "2030": 1547,
                  "2031": 1756
                }
              }
            },
            {
              "PROGRAM_NAME": "STUFF URSULA",
              "EOC_CODES": [
                "STUFFUU.XXX"
              ],
              "FISCAL_YEARS": "2027, 2028, 2029, 2030, 2031",
              "PROGRAM_GROUP": "STUFF",
              "APPROVAL_ACTION_STATUS": "COMPLETED",
              "JCA_ALIGNMENT": [
                "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
              ],
              "RESOURCE_K": {
                "27EXT": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                },
                "27ZBT_REQUESTED_DELTA": {
                  "2027": 0,
                  "2028": 0,
                  "2029": 0,
                  "2030": 0,
                  "2031": 0
                }
              }
            }
          ]

    for i in range(len(expected_result)):
      expected_result[i]["EOC_CODES"] = set(expected_result[i]["EOC_CODES"])
      result_trans_zbt[i]["EOC_CODES"] = set(result_trans_zbt[i]["EOC_CODES"])

      expected_result[i]["JCA_ALIGNMENT"] = set(expected_result[i]["JCA_ALIGNMENT"])
      result_trans_zbt[i]["JCA_ALIGNMENT"] = set(result_trans_zbt[i]["JCA_ALIGNMENT"])

    # print("check_in_detail:",result_trans_zbt)
    assert len(result_trans_zbt) == len(expected_result)
    
    for result, expected in zip(result_trans_zbt, expected_result):
      assert normalize_dict_values(result) == normalize_dict_values(expected)
    # assert result_trans_zbt == expected_result

def test_get_iss_summary_fromdb(socom_session):

    model_dict_iss = {
        "CAPABILITY_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE" : ["A", "B", "C", "D", "E"],
        "PROGRAM_GROUP" : ["5G"],
        "PROGRAM_NAME" : ["5G BEETLE1"]
    }

    model = IssSummaryFilterInputModel.parse_obj(model_dict_iss)

    iss_query = SQLiteISSQuery()
    result = test.get_iss_summary_fromdb(model, iss_query, socom_session)
    result_trans_iss = transform_result_iss(result)

    expected_result_name = [
                  {
            "PROGRAM_NAME": "5G BEETLE1",
            "EOC_CODES": [
              "5GBEE.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE",
              "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 5028,
                "2027": 5058,
                "2028": 5088,
                "2029": 5118,
                "2030": 5148
              },
              "26ZBT": {
                "2026": 4916,
                "2027": 4946,
                "2028": 4976,
                "2029": 5006,
                "2030": 5036
              },
              "26ZBT_DELTA": {
                "2026": -112,
                "2027": -112,
                "2028": -112,
                "2029": -112,
                "2030": -112
              },
              "26ISS_REQUESTED": {
                "2026": 5292,
                "2027": 5493,
                "2028": 5694,
                "2029": 5895,
                "2030": 6096
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 376,
                "2027": 547,
                "2028": 718,
                "2029": 889,
                "2030": 1060
              }
            }
          }
        ]

    # breakpoint()
    for i in range(len(result_trans_iss)):
            result_trans_iss[i]["EOC_CODES"] = set(result_trans_iss[i]["EOC_CODES"])
            expected_result_name[i]["EOC_CODES"] = set(expected_result_name[i]["EOC_CODES"])

            result_trans_iss[i]["JCA_ALIGNMENT"] = set(result_trans_iss[i]["JCA_ALIGNMENT"])
            expected_result_name[i]["JCA_ALIGNMENT"] = set(expected_result_name[i]["JCA_ALIGNMENT"])

    model_dict_iss_all = {
        "CAPABILITY_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "POM_SPONSOR_CODE" : ["AT&L", "AFSOC", "NSW", "USASOC", "MARSOC"],
        "ASSESSMENT_AREA_CODE" : ["A", "B", "C", "D", "E"],
        "PROGRAM_GROUP" : ["5G"],
        "PROGRAM_NAME" : []
    }

    expected_result_all = [
                {
            "PROGRAM_NAME": "5G BEAR",
            "EOC_CODES": [
              "5GBEA.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "FORCE SUPPORT, FORCE MANAGEMENT"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 18401,
                "2027": 18531,
                "2028": 18661,
                "2029": 18791,
                "2030": 18921
              },
              "26ZBT": {
                "2026": 18325,
                "2027": 18436,
                "2028": 18547,
                "2029": 18658,
                "2030": 18769
              },
              "26ZBT_DELTA": {
                "2026": -76,
                "2027": -95,
                "2028": -114,
                "2029": -133,
                "2030": -152
              },
              "26ISS_REQUESTED": {
                "2026": 20811,
                "2027": 21718,
                "2028": 22625,
                "2029": 23532,
                "2030": 24440
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 2486,
                "2027": 3282,
                "2028": 4078,
                "2029": 4874,
                "2030": 5671
              }
            }
          },
          {
            "PROGRAM_NAME": "5G BEETLE1",
            "EOC_CODES": [
              "5GBEE.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "NET-CENTRIC, ENTERPRISE SERVICES, CORE ENTERPRISE SERVICES",
              "LOGISTICS, DEPLOYMENT AND DISTRIBUTION, SUSTAIN THE FORCE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 5028,
                "2027": 5058,
                "2028": 5088,
                "2029": 5118,
                "2030": 5148
              },
              "26ZBT": {
                "2026": 4916,
                "2027": 4946,
                "2028": 4976,
                "2029": 5006,
                "2030": 5036
              },
              "26ZBT_DELTA": {
                "2026": -112,
                "2027": -112,
                "2028": -112,
                "2029": -112,
                "2030": -112
              },
              "26ISS_REQUESTED": {
                "2026": 5292,
                "2027": 5493,
                "2028": 5694,
                "2029": 5895,
                "2030": 6096
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 376,
                "2027": 547,
                "2028": 718,
                "2029": 889,
                "2030": 1060
              }
            }
          },
          {
            "PROGRAM_NAME": "5G BORED",
            "EOC_CODES": [
              "5GBOR.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "FORCE SUPPORT, FORCE PREPARATION, DOCTRINE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 7320,
                "2027": 7446,
                "2028": 7572,
                "2029": 7698,
                "2030": 7824
              },
              "26ZBT": {
                "2026": 7438,
                "2027": 7564,
                "2028": 7690,
                "2029": 7816,
                "2030": 7942
              },
              "26ZBT_DELTA": {
                "2026": 118,
                "2027": 118,
                "2028": 118,
                "2029": 118,
                "2030": 118
              },
              "26ISS_REQUESTED": {
                "2026": 6737,
                "2027": 6644,
                "2028": 6551,
                "2029": 6458,
                "2030": 6365
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": -701,
                "2027": -920,
                "2028": -1139,
                "2029": -1358,
                "2030": -1577
              }
            }
          },
          {
            "PROGRAM_NAME": "5G FIREANT",
            "EOC_CODES": [
              "5GFIR.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "COMPLETED",
            "JCA_ALIGNMENT": [
              "BATTLESPACE AWARENESS, ANALYSIS, PREDICTION AND PRODUCTION, INTERPRETATION (AP)"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 4490,
                "2027": 4607,
                "2028": 4724,
                "2029": 4841,
                "2030": 4958
              },
              "26ZBT": {
                "2026": 4462,
                "2027": 4579,
                "2028": 4696,
                "2029": 4813,
                "2030": 4930
              },
              "26ZBT_DELTA": {
                "2026": -28,
                "2027": -28,
                "2028": -28,
                "2029": -28,
                "2030": -28
              },
              "26ISS_REQUESTED": {
                "2026": 4462,
                "2027": 4579,
                "2028": 4696,
                "2029": 4813,
                "2030": 4930
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 0,
                "2027": 0,
                "2028": 0,
                "2029": 0,
                "2030": 0
              }
            }
          },
          {
            "PROGRAM_NAME": "5G GRATEFUL",
            "EOC_CODES": [
              "5GGRA.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, ACCOUNTING AND FINANCE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 10429,
                "2027": 10502,
                "2028": 10575,
                "2029": 10648,
                "2030": 10721
              },
              "26ZBT": {
                "2026": 10316,
                "2027": 10389,
                "2028": 10462,
                "2029": 10535,
                "2030": 10608
              },
              "26ZBT_DELTA": {
                "2026": -113,
                "2027": -113,
                "2028": -113,
                "2029": -113,
                "2030": -113
              },
              "26ISS_REQUESTED": {
                "2026": 9504,
                "2027": 9351,
                "2028": 9198,
                "2029": 9044,
                "2030": 8891
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": -812,
                "2027": -1038,
                "2028": -1264,
                "2029": -1491,
                "2030": -1717
              }
            }
          },
          {
            "PROGRAM_NAME": "5G HAPPY",
            "EOC_CODES": [
              "5GHAP.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "FORCE APPLICATION, ENGAGEMENT, NON-KINETIC MEANS"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 11330,
                "2027": 11371,
                "2028": 11412,
                "2029": 11453,
                "2030": 11494
              },
              "26ZBT": {
                "2026": 11410,
                "2027": 11464,
                "2028": 11518,
                "2029": 11572,
                "2030": 11626
              },
              "26ZBT_DELTA": {
                "2026": 80,
                "2027": 93,
                "2028": 106,
                "2029": 119,
                "2030": 132
              },
              "26ISS_REQUESTED": {
                "2026": 10924,
                "2027": 10431,
                "2028": 9938,
                "2029": 9445,
                "2030": 8952
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": -486,
                "2027": -1033,
                "2028": -1580,
                "2029": -2127,
                "2030": -2674
              }
            }
          },
          {
            "PROGRAM_NAME": "5G KANGAROO",
            "EOC_CODES": [
              "5GKAN.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "FORCE SUPPORT, FORCE MANAGEMENT, FORCE CONFIGURATION"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 13664,
                "2027": 13794,
                "2028": 13924,
                "2029": 14054,
                "2030": 14184
              },
              "26ZBT": {
                "2026": 13600,
                "2027": 13730,
                "2028": 13860,
                "2029": 13990,
                "2030": 14120
              },
              "26ZBT_DELTA": {
                "2026": -64,
                "2027": -64,
                "2028": -64,
                "2029": -64,
                "2030": -64
              },
              "26ISS_REQUESTED": {
                "2026": 15444,
                "2027": 15850,
                "2028": 16257,
                "2029": 16664,
                "2030": 17070
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 1844,
                "2027": 2120,
                "2028": 2397,
                "2029": 2674,
                "2030": 2950
              }
            }
          },
          {
            "PROGRAM_NAME": "5G RABBIT5",
            "EOC_CODES": [
              "5GRAB.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, ACCOUNTING AND FINANCE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 2044,
                "2027": 2174,
                "2028": 2304,
                "2029": 2434,
                "2030": 2564
              },
              "26ZBT": {
                "2026": 2024,
                "2027": 2154,
                "2028": 2284,
                "2029": 2414,
                "2030": 2544
              },
              "26ZBT_DELTA": {
                "2026": -20,
                "2027": -20,
                "2028": -20,
                "2029": -20,
                "2030": -20
              },
              "26ISS_REQUESTED": {
                "2026": 2204,
                "2027": 2376,
                "2028": 2548,
                "2029": 2720,
                "2030": 2893
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 180,
                "2027": 222,
                "2028": 264,
                "2029": 306,
                "2030": 349
              }
            }
          },
          {
            "PROGRAM_NAME": "5G RELAXED1",
            "EOC_CODES": [
              "5GREL.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "COMPLETED",
            "JCA_ALIGNMENT": [
              "FORCE APPLICATION"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 11195,
                "2027": 11275,
                "2028": 11355,
                "2029": 11435,
                "2030": 11515
              },
              "26ZBT": {
                "2026": 11167,
                "2027": 11247,
                "2028": 11327,
                "2029": 11407,
                "2030": 11487
              },
              "26ZBT_DELTA": {
                "2026": -28,
                "2027": -28,
                "2028": -28,
                "2029": -28,
                "2030": -28
              },
              "26ISS_REQUESTED": {
                "2026": 11167,
                "2027": 11247,
                "2028": 11327,
                "2029": 11407,
                "2030": 11487
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 0,
                "2027": 0,
                "2028": 0,
                "2029": 0,
                "2030": 0
              }
            }
          },
          {
            "PROGRAM_NAME": "5G WASP",
            "EOC_CODES": [
              "5GWAS.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "COMPLETED",
            "JCA_ALIGNMENT": [
              "BUILDING PARTNERSHIPS, COMMUNICATE",
              "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, PROGRAM / BUDGET AND PERFORMANCE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 17411,
                "2027": 17472,
                "2028": 17533,
                "2029": 17594,
                "2030": 17655
              },
              "26ZBT": {
                "2026": 17504,
                "2027": 17565,
                "2028": 17626,
                "2029": 17687,
                "2030": 17748
              },
              "26ZBT_DELTA": {
                "2026": 93,
                "2027": 93,
                "2028": 93,
                "2029": 93,
                "2030": 93
              },
              "26ISS_REQUESTED": {
                "2026": 17504,
                "2027": 17565,
                "2028": 17626,
                "2029": 17687,
                "2030": 17748
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": 0,
                "2027": 0,
                "2028": 0,
                "2029": 0,
                "2030": 0
              }
            }
          },
          {
            "PROGRAM_NAME": "5G WATCH1",
            "EOC_CODES": [
              "5GWAT.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "FORCE APPLICATION"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 16571,
                "2027": 16683,
                "2028": 16795,
                "2029": 16907,
                "2030": 17019
              },
              "26ZBT": {
                "2026": 16508,
                "2027": 16620,
                "2028": 16732,
                "2029": 16844,
                "2030": 16956
              },
              "26ZBT_DELTA": {
                "2026": -63,
                "2027": -63,
                "2028": -63,
                "2029": -63,
                "2030": -63
              },
              "26ISS_REQUESTED": {
                "2026": 14858,
                "2027": 14319,
                "2028": 13780,
                "2029": 13240,
                "2030": 12701
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": -1650,
                "2027": -2301,
                "2028": -2952,
                "2029": -3604,
                "2030": -4255
              }
            }
          },
          {
            "PROGRAM_NAME": "5G WOLF",
            "EOC_CODES": [
              "5GWOL.XXX"
            ],
            "FISCAL_YEARS": "2026, 2027, 2028, 2029, 2030",
            "PROGRAM_GROUP": "5G",
            "APPROVAL_ACTION_STATUS": "PENDING",
            "JCA_ALIGNMENT": [
              "CORPORATE MANAGEMENT AND SUPPORT, PROGRAM, BUDGET AND FINANCE, ACCOUNTING AND FINANCE"
            ],
            "RESOURCE_K": {
              "26EXT": {
                "2026": 4509,
                "2027": 4546,
                "2028": 4583,
                "2029": 4620,
                "2030": 4657
              },
              "26ZBT": {
                "2026": 4601,
                "2027": 4638,
                "2028": 4675,
                "2029": 4712,
                "2030": 4749
              },
              "26ZBT_DELTA": {
                "2026": 92,
                "2027": 92,
                "2028": 92,
                "2029": 92,
                "2030": 92
              },
              "26ISS_REQUESTED": {
                "2026": 4368,
                "2027": 4248,
                "2028": 4128,
                "2029": 4007,
                "2030": 3887
              },
              "26ISS_REQUESTED_DELTA": {
                "2026": -233,
                "2027": -390,
                "2028": -547,
                "2029": -705,
                "2030": -862
              }
            }
          }        
    ]

    model = IssSummaryFilterInputModel.parse_obj(model_dict_iss_all)
    iss_query = SQLiteISSQuery()
    result = test.get_iss_summary_fromdb(model, iss_query, socom_session)
    result_trans_iss_all= transform_result_iss(result)

    for i in range(len(expected_result_all)):
        expected_result_all[i]["EOC_CODES"] = set(expected_result_all[i]["EOC_CODES"])
        result_trans_iss_all[i]["EOC_CODES"] = set(result_trans_iss_all[i]["EOC_CODES"])

        expected_result_all[i]["JCA_ALIGNMENT"] = set(expected_result_all[i]["JCA_ALIGNMENT"])
        result_trans_iss_all[i]["JCA_ALIGNMENT"] = set(result_trans_iss_all[i]["JCA_ALIGNMENT"])
    
    # breakpoint()
    
    
    assert len(result_trans_iss) == len(expected_result_name)
    assert result_trans_iss == expected_result_name
   
    assert len(result_trans_iss_all) == len(expected_result_all)
 
    for result, expected in zip(result_trans_iss_all, expected_result_all):
      assert normalize_dict_values(result) == normalize_dict_values(expected)

@pytest.mark.asyncio
async def test_get_event_summary_view(socom_session):
    event_name = "JM_EVENT_01"

    result = await test.get_event_summary_view(event_name,db_conn=socom_session)
    assert isinstance(result["events"],list) and (len(result["all_years"]) > 1) and isinstance(result["event_title"],str) \
        and isinstance(result["event_justification"],str)
    return result