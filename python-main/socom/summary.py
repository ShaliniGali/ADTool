from fastapi import HTTPException
from typing import List
import pandas as pd

import time
import os
import asyncio
import json
from sqlalchemy import text


from api.internal.redis_cache import RedisController

from rds.table_model.socom.DtZbtExtractModel import DtZBTExtractModel
from rds.table_model.socom.DtIssExtractModel import DtISSExtractModel
from rds.table_model.socom.UsrIssADSaves import UsrIssADSaves
from rds.table_model.socom.UsrIssAOSaves import UsrIssAOSaves
from rds.table_model.socom.UsrZbtADSaves import UsrZbtADSaves
from rds.table_model.socom.UsrZbtAOSaves import UsrZbtAOSaves
from rds.table_model.socom.UsrIssADFinalSaves import UsrIssADFinalSaves
from rds.table_model.socom.UsrLookupPOMPosition import UsrLookupPOMPosition
from rds.table_model.socom.UsrEveFundLines import UsrEventFundingLines
from rds.table_model.socom.UsrZbtADFinalSaves import UsrZbtADFinalSaves
from rds.table_model.socom.UsrZbtEveFundLines import UsrZbtEveFundLines


from api.internal.resources import (
    create_dynamic_table_class,
    ZbtSummaryTableSet,
    IssSummaryTableSet,
)

from collections import defaultdict

SCHEMA = os.environ.get("SOCOM_UI","SOCOM_UI")

class ZBTQuery:
    def __init__(self,zbt_extract_table,ext_table,year_range):
        self.zbt_extract = zbt_extract_table
        self.ext = ext_table
        self.year_range = year_range

    def get_base_k_query(self):
        base_k_query = f"""
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
                        GROUP_CONCAT(
                            DISTINCT FISCAL_YEAR
                            ORDER BY
                                FISCAL_YEAR SEPARATOR ', '
                        )
                    FROM
                        {SCHEMA}.`{self.ext}`
                ) as FISCAL_YEARS
            FROM
                (
                    SELECT
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        POM_POSITION_CODE,
                        FISCAL_YEAR,
                        RESOURCE_K
                    FROM
                        {SCHEMA}.`{self.ext}`
                    UNION ALL
                    SELECT
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        FISCAL_YEAR,
                        0 AS RESOURCE_K
                    FROM
                        {SCHEMA}.`{self.zbt_extract}`
                    WHERE
                        (
                            (`PROGRAM_CODE`,`FISCAL_YEAR`) NOT IN (
                                SELECT
                                    DISTINCT PROGRAM_CODE,`FISCAL_YEAR`
                                FROM
                                    {SCHEMA}.{self.ext}
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
                        {SCHEMA}.LOOKUP_PROGRAM_DETAIL
                ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
                AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE
                `EXT`.`FISCAL_YEAR` IN {self.year_range}
                AND `LUT`.`PROGRAM_NAME` IS NOT NULL
            GROUP BY
                `LUT`.`PROGRAM_NAME`,
                `EXT`.`POM_POSITION_CODE`,
                `EXT`.`FISCAL_YEAR`
            ORDER BY
                `PROGRAM_NAME`,
                `FISCAL_YEAR`
            """
        return base_k_query
    
    def get_approval_query(self):
        approval_query = f"""
            SELECT
                DISTINCT LUT.PROGRAM_GROUP, LUT.PROGRAM_NAME,
                CASE WHEN SUM( 
                    IF(EXTRACT.EVENT_STATUS LIKE 'NOT DECIDED', 1, 0) 
                    ) = 0 THEN 'COMPLETED' WHEN SUM(IF(EXTRACT.EVENT_STATUS = 'NOT DECIDED', 1, 0)) > 0 THEN 'PENDING' END AS APPROVAL_ACTION_STATUS                 
            FROM(
                    SELECT
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `POM_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`,
                        `EVENT_STATUS`
                    FROM
                        {SCHEMA}.{self.zbt_extract}
                    UNION ALL
                    SELECT
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `POM_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`,
                        'DECIDED' AS EVENT_STATUS
                    FROM
                        {SCHEMA}.`{self.ext}`
                    WHERE(
                            PROGRAM_CODE NOT IN(
                                SELECT
                                    DISTINCT PROGRAM_CODE
                                FROM
                                    {SCHEMA}.{self.zbt_extract}
                            )
                        )
                ) AS EXTRACT
                LEFT JOIN(
                    SELECT
                        `PROGRAM_NAME`,
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `POM_SPONSOR_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`
                    FROM
                        {SCHEMA}.`LOOKUP_PROGRAM_DETAIL`
                ) AS LUT ON EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
                AND EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                AND EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                AND EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE
                LUT.PROGRAM_GROUP IS NOT NULL
            GROUP BY
                LUT.PROGRAM_NAME
            HAVING
                APPROVAL_ACTION_STATUS IN('PENDING', 'COMPLETED')
            """
        return approval_query
    
    def get_jca_query(self):
        jca_query = f"""
            SELECT B.DESCRIPTION AS JCA_DESCRIPTION, A.PROGRAM_NAME
            FROM {SCHEMA}.LOOKUP_PROGRAM_DETAIL A
            JOIN {SCHEMA}.LOOKUP_JCA2 B ON
            JSON_UNQUOTE(JSON_EXTRACT(A.JCA, '$[0]')) = B.ID
            """
        return jca_query
    
    def get_eoc_query(self):
        eoc_query = f"""
                SELECT 
                DISTINCT B.EOC_CODE, 
                A.PROGRAM_NAME 
            FROM 
                {SCHEMA}.LOOKUP_PROGRAM_DETAIL AS A 
                LEFT JOIN ( 
                    SELECT 
                        * 
                    FROM 
                        {SCHEMA}.{self.ext} 
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
                        "26EXT" AS POM_POSITION_CODE, 
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
                        {SCHEMA}.{self.zbt_extract} 
                    WHERE 

                        ( 

                            PROGRAM_CODE NOT IN ( 
                                SELECT 

                                    DISTINCT PROGRAM_CODE 
                                FROM 

                                    {SCHEMA}.{self.ext} 
                            ) 

                            OR EOC_CODE NOT IN ( 
                                SELECT 
                                    DISTINCT EOC_CODE 

                                FROM 
                                    {SCHEMA}.{self.ext}
                            ) 

                        ) 
                ) AS B ON A.PROGRAM_CODE = B.PROGRAM_CODE 
                
            """
        return eoc_query
    
    def get_zbt_requested_delta_query(self):
        zbt_requested_delta_query = f"""
                SELECT 
                    `LUT`.`PROGRAM_NAME`,
                    LUT.CAPABILITY_SPONSOR_CODE,
                    LUT.ASSESSMENT_AREA_CODE,
                    LUT.POM_SPONSOR_CODE,
                    '26ZBT REQUESTED DELTA' AS "26ZBT_REQUESTED_DELTA", 
                    '26EXT' AS POM_POSITION_CODE, 
                    `ZBT_EXTRACT`.`FISCAL_YEAR`, 
                    SUM(ZBT_EXTRACT.DELTA_AMT) AS DELTA_AMT, 
                    ( 

                        SELECT 
                            GROUP_CONCAT( 
                                DISTINCT FISCAL_YEAR 
                                ORDER BY 
                                    FISCAL_YEAR SEPARATOR ', '
                            ) 
                        FROM 
                            {SCHEMA}.`{self.ext}` 
                    ) as FISCAL_YEARS 
                FROM 
                    ( 
                        SELECT 
                            PROGRAM_CODE,
                            PROGRAM_GROUP,
                            ASSESSMENT_AREA_CODE,
                            POM_SPONSOR_CODE,
                            CAPABILITY_SPONSOR_CODE,
                            FISCAL_YEAR,
                            DELTA_AMT 
                        FROM 
                            {SCHEMA}.`{self.zbt_extract}` 
                        UNION ALL 
                        SELECT 
                            PROGRAM_CODE,
                            PROGRAM_GROUP,
                            ASSESSMENT_AREA_CODE,
                            POM_SPONSOR_CODE,
                            CAPABILITY_SPONSOR_CODE,
                            FISCAL_YEAR,
                            0 AS DELTA_AMT
                        FROM 
                            {SCHEMA}.`{self.ext}` 
                        WHERE 
                            

                                (`PROGRAM_CODE`,`FISCAL_YEAR`) NOT IN ( 
                                    SELECT 
                                        DISTINCT PROGRAM_CODE, FISCAL_YEAR
                                    FROM 
                                        {SCHEMA}.{self.zbt_extract} 
                                    WHERE `PROGRAM_CODE` IS NOT NULL
                                ) 
                                
                    ) AS ZBT_EXTRACT 
                    LEFT JOIN ( 
                        SELECT 
                            ASSESSMENT_AREA_CODE, 
                            PROGRAM_NAME, 
                            PROGRAM_GROUP, 
                            PROGRAM_CODE, 
                            CAPABILITY_SPONSOR_CODE, 
                            POM_SPONSOR_CODE 
                        FROM 
                            {SCHEMA}.LOOKUP_PROGRAM_DETAIL 

                    ) AS LUT ON ZBT_EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
                    AND ZBT_EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE 
                    AND ZBT_EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE 
                    AND ZBT_EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE 
                    AND ZBT_EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE 

                    
                WHERE 
                    `ZBT_EXTRACT`.`FISCAL_YEAR` IN {self.year_range}
                    AND `LUT`.`PROGRAM_NAME` IS NOT NULL
                GROUP BY 
                    `LUT`.`PROGRAM_NAME`, 
                    `POM_POSITION_CODE`, 
                    `ZBT_EXTRACT`.`FISCAL_YEAR` 
                ORDER BY 
                    `PROGRAM_NAME`, 
                    `ZBT_EXTRACT`.`FISCAL_YEAR` 
                """
        return zbt_requested_delta_query
    
    def get_zbt_all_query(self):
        zbt_all_query = f"""
            SELECT 
                D.PROGRAM_CODE, 
                LUT.PROGRAM_NAME,
                D.CAPABILITY_SPONSOR_CODE,
                D.ASSESSMENT_AREA_CODE,
                D.PROGRAM_GROUP,
                D.FISCAL_YEAR,
                D.DELTA_AMT
            FROM {SCHEMA}.{self.zbt_extract} D
            LEFT JOIN {SCHEMA}.LOOKUP_PROGRAM_DETAIL LUT
              ON D.PROGRAM_GROUP = LUT.PROGRAM_GROUP
             AND D.PROGRAM_CODE = LUT.PROGRAM_CODE
             AND D.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
             AND D.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
             AND D.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE LUT.PROGRAM_NAME IS NOT NULL
        """
        return zbt_all_query


class ISSQuery:
    def __init__(self,ext,zbt,iss_extract,zbt_extract,year_range):
        self.ext = ext
        self.zbt = zbt
        self.iss_extract = iss_extract
        self.zbt_extract = zbt_extract
        self.year_range = year_range
        print(self.year_range)

    def get_base_k_query(self):
        base_k_query = f"""
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
                        GROUP_CONCAT(
                            DISTINCT FISCAL_YEAR
                            ORDER BY
                                FISCAL_YEAR SEPARATOR ', '
                        )
                    FROM
                        {SCHEMA}.`{self.ext}`
                ) as FISCAL_YEARS
            FROM
                (
                    SELECT
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        POM_POSITION_CODE,
                        FISCAL_YEAR,
                        RESOURCE_K
                    FROM
                        {SCHEMA}.`{self.ext}`
                    UNION ALL
                    SELECT
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        '26EXT' AS POM_POSITION_CODE,
                        FISCAL_YEAR,
                        0 AS RESOURCE_K
                    FROM
                        {SCHEMA}.`{self.iss_extract}`
                    WHERE
                        (
                            (`PROGRAM_CODE`,FISCAL_YEAR) NOT IN (
                                SELECT
                                    DISTINCT PROGRAM_CODE,FISCAL_YEAR
                                FROM
                                    {SCHEMA}.{self.ext}
                            )
                        )
                UNION ALL
                
                SELECT
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        '27EXT' AS POM_POSITION_CODE,
                        FISCAL_YEAR,
                        0 AS RESOURCE_K
                    FROM
                {SCHEMA}.{self.zbt}
                    WHERE (PROGRAM_CODE, FISCAL_YEAR) NOT IN (SELECT DISTINCT PROGRAM_CODE, FISCAL_YEAR FROM {SCHEMA}.{self.ext})
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
                        {SCHEMA}.LOOKUP_PROGRAM_DETAIL
                ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE
                AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE
                `EXT`.`FISCAL_YEAR` IN {self.year_range}
                AND `LUT`.`PROGRAM_NAME` IS NOT NULL
            GROUP BY
                `LUT`.`PROGRAM_NAME`,
                `EXT`.`POM_POSITION_CODE`,
                `EXT`.`FISCAL_YEAR`
            ORDER BY
                `PROGRAM_NAME`,
                `FISCAL_YEAR`
            """
        return base_k_query
    
    def get_iss_k_query(self):
        iss_k_query = f"""
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
                        GROUP_CONCAT( 
                            DISTINCT FISCAL_YEAR 
                            ORDER BY 
                                FISCAL_YEAR SEPARATOR ', '
                        ) 
                    FROM 
                        {SCHEMA}.{self.zbt}
                ) as FISCAL_YEARS 
            FROM 
                ( 
                    SELECT  
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        POM_POSITION_CODE,
                        FISCAL_YEAR,
                        RESOURCE_K 
                    FROM 
                        {SCHEMA}.{self.zbt} 
                    UNION ALL 
                    SELECT 
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        '27ZBT' AS POM_POSITION_CODE,
                        FISCAL_YEAR,
                        0 AS RESOURCE_K
                    FROM 
                        {SCHEMA}.{self.iss_extract}
                    WHERE 
                        ( 
                            (`PROGRAM_CODE`,FISCAL_YEAR) NOT IN ( 
                                SELECT 
                                    DISTINCT PROGRAM_CODE, FISCAL_YEAR 
                                FROM 
                                    {SCHEMA}.{self.zbt} 
                            ) 
                        ) 
                    UNION ALL 
                    SELECT 
                        PROGRAM_CODE,
                        PROGRAM_GROUP,
                        ASSESSMENT_AREA_CODE,
                        POM_SPONSOR_CODE,
                        CAPABILITY_SPONSOR_CODE,
                        '27ZBT' AS POM_POSITION_CODE,
                        FISCAL_YEAR,
                        0 AS RESOURCE_K
                    FROM 
                        {SCHEMA}.{self.ext}
                    WHERE 
                        ( 
                            (`PROGRAM_CODE`,FISCAL_YEAR) NOT IN ( 
                                SELECT 
                                    DISTINCT PROGRAM_CODE, FISCAL_YEAR 
                                FROM 
                                    {SCHEMA}.{self.zbt} 
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
                        {SCHEMA}.LOOKUP_PROGRAM_DETAIL 
                ) AS LUT ON EXT.PROGRAM_GROUP = LUT.PROGRAM_GROUP 
                AND EXT.PROGRAM_CODE = LUT.PROGRAM_CODE 
                AND EXT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE 
                AND EXT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE 
                AND EXT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE 
            WHERE 
                `EXT`.`FISCAL_YEAR` IN {self.year_range}
                AND `LUT`.`PROGRAM_NAME` IS NOT NULL 
            GROUP BY 
                `LUT`.`PROGRAM_NAME`, 
                `EXT`.`POM_POSITION_CODE`, 
                `EXT`.`FISCAL_YEAR` 

            ORDER BY 
                `PROGRAM_NAME`,        
                `FISCAL_YEAR` 
            """
        return iss_k_query
    
    def get_approval_query(self):
        approval_query = f"""
            SELECT
                DISTINCT LUT.PROGRAM_GROUP, LUT.PROGRAM_NAME,
                CASE WHEN SUM( 
                    IF(EXTRACT.EVENT_STATUS LIKE 'NOT DECIDED', 1, 0) 
                    ) = 0 THEN 'COMPLETED' WHEN SUM(IF(EXTRACT.EVENT_STATUS = 'NOT DECIDED', 1, 0)) > 0 THEN 'PENDING' END AS APPROVAL_ACTION_STATUS                 
            FROM(
                    SELECT
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `POM_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`,
                        `EVENT_STATUS`
                    FROM
                        {SCHEMA}.{self.iss_extract}
                    UNION ALL
                    SELECT
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `POM_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`,
                        'DECIDED' AS EVENT_STATUS
                    FROM
                        {SCHEMA}.`{self.ext}`
                    WHERE(
                            PROGRAM_CODE NOT IN(
                                SELECT
                                    DISTINCT PROGRAM_CODE
                                FROM
                                    {SCHEMA}.{self.iss_extract}
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
                        {SCHEMA}.`{self.zbt}`
                    WHERE(
                            PROGRAM_CODE NOT IN(
                                SELECT
                                    DISTINCT PROGRAM_CODE
                                FROM
                                    {SCHEMA}.{self.iss_extract}
                            )
                        )
                ) AS EXTRACT
                LEFT JOIN(
                    SELECT
                        `PROGRAM_NAME`,
                        `PROGRAM_GROUP`,
                        `PROGRAM_CODE`,
                        `POM_SPONSOR_CODE`,
                        `CAPABILITY_SPONSOR_CODE`,
                        `ASSESSMENT_AREA_CODE`
                    FROM
                        {SCHEMA}.`LOOKUP_PROGRAM_DETAIL`
                ) AS LUT ON EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP
                AND EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE
                AND EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
                AND EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
                AND EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE
                LUT.PROGRAM_GROUP IS NOT NULL
            GROUP BY
                LUT.PROGRAM_NAME
            HAVING
                APPROVAL_ACTION_STATUS IN('PENDING', 'COMPLETED')
            """
        return approval_query
    def get_jca_query(self):
        jca_query = f"""
                SELECT B.DESCRIPTION AS JCA_DESCRIPTION, A.PROGRAM_NAME
                FROM {SCHEMA}.LOOKUP_PROGRAM_DETAIL A
                JOIN {SCHEMA}.LOOKUP_JCA2 B ON
                JSON_UNQUOTE(JSON_EXTRACT(A.JCA, '$[0]')) = B.ID
                """
        return jca_query
    
    def get_eoc_query(self):
        eoc_query = f"""
                    SELECT 
                    DISTINCT B.EOC_CODE, 
                    A.PROGRAM_NAME 
                FROM 
                    {SCHEMA}.LOOKUP_PROGRAM_DETAIL AS A 
                    LEFT JOIN ( 
                        SELECT 
                            * 
                        FROM 
                            {SCHEMA}.{self.ext}
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
                            {SCHEMA}.{self.iss_extract}
                        WHERE 
                            ( 
                                PROGRAM_CODE NOT IN ( 
                                    SELECT 
                                        DISTINCT PROGRAM_CODE 
                                    FROM 
                                        {SCHEMA}.{self.ext}
                                ) 
                                OR EOC_CODE NOT IN ( 
                                    SELECT 
                                        DISTINCT EOC_CODE 
                                    FROM 
                                        {SCHEMA}.{self.ext}
                                ) 
                            ) 
                    ) AS B ON A.PROGRAM_CODE = B.PROGRAM_CODE 
                    
                """
        return eoc_query
    
    def get_iss_requested_delta_query(self):
        iss_requested_delta_query = f"""
            SELECT
                LUT.CAPABILITY_SPONSOR_CODE,
                LUT.ASSESSMENT_AREA_CODE,
                LUT.POM_SPONSOR_CODE,
                LUT.PROGRAM_NAME, 
                '26ZBT' AS POM_POSITION_CODE,
                '26ISS REQUESTED DELTA' AS "26ISS_REQUESTED_DELTA", 
                ISS_EXTRACT.FISCAL_YEAR, 
                SUM(ISS_EXTRACT.DELTA_AMT) AS DELTA_AMT, 
        
            (               
                SELECT
                    GROUP_CONCAT(               
                        DISTINCT FISCAL_YEAR               
                        ORDER BY               
                            FISCAL_YEAR SEPARATOR ', '                
                    )
        
                FROM       
                    {SCHEMA}.{self.ext}          
            ) as FISCAL_YEARS             
        FROM              
            (
                SELECT
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    ASSESSMENT_AREA_CODE,
                    POM_SPONSOR_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    FISCAL_YEAR,
                    DELTA_AMT
                FROM
                    {SCHEMA}.{self.iss_extract}
                    
                UNION ALL
            
                SELECT
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    ASSESSMENT_AREA_CODE,
                    POM_SPONSOR_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    FISCAL_YEAR,
                    0 AS DELTA_AMT
                FROM
                    {SCHEMA}.{self.ext}
                WHERE
                    (PROGRAM_CODE, FISCAL_YEAR) NOT IN 
                        (SELECT 
                            DISTINCT PROGRAM_CODE, FISCAL_YEAR 
                        FROM {SCHEMA}.{self.iss_extract}
                        WHERE PROGRAM_CODE IS NOT NULL)

                UNION ALL
            
                SELECT
                    PROGRAM_CODE,
                    PROGRAM_GROUP,
                    ASSESSMENT_AREA_CODE,
                    POM_SPONSOR_CODE,
                    CAPABILITY_SPONSOR_CODE,
                    FISCAL_YEAR,
                    0 AS DELTA_AMT
                FROM
                    {SCHEMA}.{self.zbt}
                WHERE
                (PROGRAM_CODE, FISCAL_YEAR) NOT IN (
                    SELECT 
                        DISTINCT PROGRAM_CODE, FISCAL_YEAR 
                    FROM {SCHEMA}.{self.iss_extract}
                    WHERE PROGRAM_CODE IS NOT NULL)
            ) AS ISS_EXTRACT

            LEFT JOIN (               
                SELECT              
                    POM_SPONSOR_CODE,              
                    CAPABILITY_SPONSOR_CODE,              
                    ASSESSMENT_AREA_CODE,              
                    PROGRAM_NAME,               
                    PROGRAM_GROUP,               
                    PROGRAM_CODE               
                FROM
        
                    {SCHEMA}.LOOKUP_PROGRAM_DETAIL             
            ) AS LUT ON ISS_EXTRACT.PROGRAM_GROUP = LUT.PROGRAM_GROUP             
            AND ISS_EXTRACT.PROGRAM_CODE = LUT.PROGRAM_CODE               
            AND ISS_EXTRACT.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE              
            AND ISS_EXTRACT.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE              
            AND ISS_EXTRACT.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE              
        
        WHERE               
            ISS_EXTRACT.FISCAL_YEAR IN {self.year_range}         
            AND LUT.PROGRAM_NAME IS NOT NULL               
        
        GROUP BY               
            LUT.PROGRAM_NAME,              
            POM_POSITION_CODE,               
            ISS_EXTRACT.FISCAL_YEAR
        
        ORDER BY
            PROGRAM_NAME,               
            ISS_EXTRACT.FISCAL_YEAR
        """
        return iss_requested_delta_query

    def get_iss_all_query(self):
        iss_all_query = f"""
            SELECT 
                D.PROGRAM_CODE, 
                LUT.PROGRAM_NAME,
                D.CAPABILITY_SPONSOR_CODE,
                D.ASSESSMENT_AREA_CODE,
                D.PROGRAM_GROUP,
                D.FISCAL_YEAR,
                D.DELTA_AMT
            FROM {SCHEMA}.{self.iss_extract} D
            LEFT JOIN {SCHEMA}.LOOKUP_PROGRAM_DETAIL LUT
            ON D.PROGRAM_GROUP = LUT.PROGRAM_GROUP
            AND D.PROGRAM_CODE = LUT.PROGRAM_CODE
            AND D.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
            AND D.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
            AND D.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE LUT.PROGRAM_NAME IS NOT NULL
        """
        return iss_all_query
    
    def get_zbt_all_query(self):
        zbt_all_query = f"""
            SELECT 
                D.PROGRAM_CODE, 
                LUT.PROGRAM_NAME,
                D.CAPABILITY_SPONSOR_CODE,
                D.ASSESSMENT_AREA_CODE,
                D.PROGRAM_GROUP,
                D.FISCAL_YEAR,
                D.DELTA_AMT
            FROM {SCHEMA}.{self.zbt_extract} D
            LEFT JOIN {SCHEMA}.LOOKUP_PROGRAM_DETAIL LUT
              ON D.PROGRAM_GROUP = LUT.PROGRAM_GROUP
             AND D.PROGRAM_CODE = LUT.PROGRAM_CODE
             AND D.POM_SPONSOR_CODE = LUT.POM_SPONSOR_CODE
             AND D.CAPABILITY_SPONSOR_CODE = LUT.CAPABILITY_SPONSOR_CODE
             AND D.ASSESSMENT_AREA_CODE = LUT.ASSESSMENT_AREA_CODE
            WHERE LUT.PROGRAM_NAME IS NOT NULL
        """
        return zbt_all_query

def sorted_filter(value):
    if value is None:
        return []
    return sorted(value)


def get_zbt_iss_summary_from_cache(model, key, redis):
    cached = RedisController.get_json_from_redis(key, redis)

    if not cached:
        return []

    if isinstance(cached, list):
        if len(cached) == 1 and isinstance(cached[0], dict) and "MESSAGE" in cached[0]:
            return cached
        return cached

    if not isinstance(cached, dict):
        return []

    def sorted_filter(value):
        return sorted(value) if isinstance(value, list) else []

    current_filter = {
        "CAPABILITY_SPONSOR_CODE": sorted_filter(model.CAPABILITY_SPONSOR_CODE),
        "ASSESSMENT_AREA_CODE": sorted_filter(model.ASSESSMENT_AREA_CODE),
        "PROGRAM_GROUP": sorted_filter(model.PROGRAM_GROUP),
    }

    cached_filter_raw = cached.get("filter", {})
    cached_filter = {
        "CAPABILITY_SPONSOR_CODE": sorted_filter(cached_filter_raw.get("CAPABILITY_SPONSOR_CODE")),
        "ASSESSMENT_AREA_CODE": sorted_filter(cached_filter_raw.get("ASSESSMENT_AREA_CODE")),
        "PROGRAM_GROUP": sorted_filter(cached_filter_raw.get("PROGRAM_GROUP")),
    }


    if current_filter != cached_filter:
        return None

    data = cached.get("data")
    if not isinstance(data, list):
        return []

    return data


def build_summary_row(program_code, group, fiscal_map_iss=None, fiscal_map_zbt=None, iss_key=None, zbt_key=None):
    fiscal_years = sorted(group["FISCAL_YEAR"].astype(str).unique())
    pos_sum = group[group["DELTA_AMT"] > 0]["DELTA_AMT"].sum()
    neg_sum = group[group["DELTA_AMT"] < 0]["DELTA_AMT"].sum()
    all_sum = group["DELTA_AMT"].sum()

    resource_k = {}
    if fiscal_map_iss and iss_key is not None:
        resource_k[f"{iss_key}ISS_REQUESTED_DELTA"] = {str(k): float(v) for k, v in fiscal_map_iss.items()}
    if fiscal_map_zbt and zbt_key is not None:
        resource_k[f"{zbt_key}ZBT_REQUESTED_DELTA"] = {str(k): float(v) for k, v in fiscal_map_zbt.items()}

    return {
        "PROGRAM_CODE": program_code,
        "PROGRAM_NAME": group["PROGRAM_NAME"].iloc[0],
        "CAPABILITY_SPONSOR_CODE": group["CAPABILITY_SPONSOR_CODE"].iloc[0],
        "ASSESSMENT_AREA_CODE": group["ASSESSMENT_AREA_CODE"].iloc[0],
        "PROGRAM_GROUP": group["PROGRAM_GROUP"].iloc[0],
        "FISCAL_YEARS": ", ".join(fiscal_years),
        "APPROVAL_ACTION_STATUS": "PENDING",
        "JCA_ALIGNMENT": [],
        "EOC_CODES": list(group["EOC_CODE"].unique()) if "EOC_CODE" in group.columns else [],
        "RESOURCE_K": resource_k,
        "POSITIVE_SUM": float(pos_sum),
        "NEGATIVE_SUM": float(neg_sum),
        "OVERALL_SUM": float(all_sum)
    } 

def needs_refresh_based_on_cache(model, key, redis):
    cached = RedisController.get_json_from_redis(key, redis)
    if not cached:
        return True

    if isinstance(cached, list):
        if len(cached) == 1 and isinstance(cached[0], dict) and "MESSAGE" in cached[0]:
            return True
        return False

    if not isinstance(cached, dict):
        return True

    def sorted_filter(value):
        return sorted(value) if isinstance(value, list) else []

    current_filter = {
        "CAPABILITY_SPONSOR_CODE": sorted_filter(model.CAPABILITY_SPONSOR_CODE),
        "ASSESSMENT_AREA_CODE": sorted_filter(model.ASSESSMENT_AREA_CODE),
        "PROGRAM_GROUP": sorted_filter(model.PROGRAM_GROUP),
    }

    cached_filter_raw = cached.get("filter", {})
    cached_filter = {
        "CAPABILITY_SPONSOR_CODE": sorted_filter(cached_filter_raw.get("CAPABILITY_SPONSOR_CODE")),
        "ASSESSMENT_AREA_CODE": sorted_filter(cached_filter_raw.get("ASSESSMENT_AREA_CODE")),
        "PROGRAM_GROUP": sorted_filter(cached_filter_raw.get("PROGRAM_GROUP")),
    }

    return current_filter != cached_filter

def get_zbt_summary_fromdb(model, query:ZBTQuery, db_conn,redis):
    start_time = time.time()
    approval_filter = model.APPROVAL_FILTER[0] if isinstance(model.APPROVAL_FILTER, list) else model.APPROVAL_FILTER

    key = f"api::/socom/zbt/program_summary::{approval_filter}"
    lock_key = f"{key}::status"

   
    if not model.REFRESH:
        if key in redis.keys():
            if needs_refresh_based_on_cache(model, key, redis):
                print("Input is different from cached filter or cache contains only MESSAGE. Forcing refresh.")
                model.REFRESH = True

    if not model.REFRESH and key in redis.keys():
        cached_result = get_zbt_iss_summary_from_cache(model, key, redis)
        if cached_result:
            return cached_result
        else:
            print("Cache exists but filter mismatch or empty. Proceeding to compute fresh data.")
       
       
    if model.APPROVAL_FILTER:
        pom_year = query.zbt_extract.split("_")[-1]
        table_name = f"{SCHEMA}.DT_ZBT_EXTRACT_{pom_year}"

        if approval_filter == "zbt_all":
            zbt_all_sql = query.get_zbt_all_query()
            df = pd.read_sql(zbt_all_sql, con=db_conn.bind)

            if model.CAPABILITY_SPONSOR_CODE:
                df = df[df["CAPABILITY_SPONSOR_CODE"].isin(model.CAPABILITY_SPONSOR_CODE)]

            if model.ASSESSMENT_AREA_CODE:
                df = df[df["ASSESSMENT_AREA_CODE"].isin(model.ASSESSMENT_AREA_CODE)]

            if model.PROGRAM_GROUP:
                df = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]

            total_all_sum = df["DELTA_AMT"].sum()
            df["FYDP"] = df["DELTA_AMT"]
            print(f"Total DELTA_AMT for zbt_all: {total_all_sum:.2f}")

            if df.empty:
                message = [ {
                    "MESSAGE": f"No ZBT data found matching PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"
                }]
                
                redis.set(key, json.dumps(message))
                redis.delete(lock_key)
                return message

        elif approval_filter == "zbt_approved":
            all_rows = []
            sponsor_list = model.CAPABILITY_SPONSOR_CODE or ["ALL"]
            area_list = model.ASSESSMENT_AREA_CODE or ["ALL"]
            group_list = model.PROGRAM_GROUP or ["ALL"]

            for sponsor in sponsor_list:
                for area in area_list:
                    for group in group_list:

                        rows = UsrZbtADFinalSaves.get_approved_zbt_events(
                            dt_zbt_extract_table=table_name,
                            db_conn=db_conn,
                            capability_sponsor_code=sponsor,
                            assessment_area_code=area,
                            program_group=group
                        )

                        all_rows.extend(rows)

            if not all_rows:
                message = [{"MESSAGE": f"No approved ZBT found for PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"}]
                redis.set(key, json.dumps(message))
                redis.delete(lock_key)
                return message

            df = pd.DataFrame(all_rows, columns=[
                "PROGRAM_CODE", "PROGRAM_NAME", "CAPABILITY_SPONSOR_CODE",
                "ASSESSMENT_AREA_CODE", "PROGRAM_GROUP", "FISCAL_YEAR", "DELTA_AMT", "EVENT_NAME", "AD_RECOMENDATION"
            ])

           
            scale_events = df[df["AD_RECOMENDATION"] == "Approve at Scale"]["EVENT_NAME"].unique().tolist()

            if scale_events:
                pom_id = UsrLookupPOMPosition.get_active_pom_id(db_conn)
                scale_fydp_rows = UsrZbtEveFundLines.get_fydp_sum_for_approve_at_scale(pom_id, scale_events, db_conn)

                scale_fydp_map = {}
                for row in scale_fydp_rows:
                    event_name = row[0]
                    yearly_values = row[1:]  # FY_1, FY_2, ...
                    fiscal_years = [str(2027 + i) for i in range(len(yearly_values))]

                    scale_fydp_map[event_name] = {
                        year: value for year, value in zip(fiscal_years, yearly_values)
                    }
            else:
                scale_fydp_map = {}

            df["FYDP"] = df.apply(
                lambda row: sum(scale_fydp_map[row["EVENT_NAME"]].values())
                if row["AD_RECOMENDATION"] == "Approve at Scale" and row["EVENT_NAME"] in scale_fydp_map
                else row["DELTA_AMT"],
                axis=1
            )

            # df["FYDP"] = df.apply(
            #     lambda row: scale_fydp_map[row["EVENT_NAME"]] if row["AD_RECOMENDATION"] == "Approve at Scale" and row["EVENT_NAME"] in scale_fydp_map
            #     else row["DELTA_AMT"],
            #     axis=1
            # )
        
        
        else:
            raise HTTPException(400, f"Unsupported APPROVAL_FILTER: {approval_filter}")

        summary = []
        grouped = df.groupby("PROGRAM_CODE")
        zbt_key = int(pom_year) % 2000
        zbt_requested_delta_key = f"{zbt_key}ZBT_REQUESTED_DELTA"

        for program_code, group in grouped:
            program_name = group["PROGRAM_NAME"].iloc[0]
            sponsor_code = group["CAPABILITY_SPONSOR_CODE"].iloc[0]
            area_code = group["ASSESSMENT_AREA_CODE"].iloc[0]
            program_group = group["PROGRAM_GROUP"].iloc[0]

            fiscal_years = sorted(group["FISCAL_YEAR"].astype(str).unique())

            fiscal_map = {}

            for _, row in group.iterrows():
                if approval_filter == "zbt_approved":
                    event = row["EVENT_NAME"]
                    if row["AD_RECOMENDATION"] == "Approve at Scale" and event in scale_fydp_map:
                        for year, amt in scale_fydp_map[event].items():
                            fiscal_map[year] = fiscal_map.get(year, 0) + amt
                    else:
                        year = str(row["FISCAL_YEAR"])
                        fiscal_map[year] = fiscal_map.get(year, 0) + row["DELTA_AMT"]
                else: 
                    year = str(row["FISCAL_YEAR"])
                    fiscal_map[year] = fiscal_map.get(year, 0) + row["DELTA_AMT"]

            pos_sum = sum(v for v in fiscal_map.values() if v > 0)
            neg_sum = sum(v for v in fiscal_map.values() if v < 0)
            all_sum = sum(fiscal_map.values())

            summary.append({
                "PROGRAM_CODE": program_code,
                "PROGRAM_NAME": program_name,
                "CAPABILITY_SPONSOR_CODE": sponsor_code,
                "ASSESSMENT_AREA_CODE": area_code,
                "PROGRAM_GROUP": program_group,
                "FISCAL_YEARS": ", ".join(fiscal_years),
                "RESOURCE_K": {
                    zbt_requested_delta_key: {k: float(v) for k, v in fiscal_map.items()}
                },
                "POSITIVE_SUM": float(pos_sum),
                "NEGATIVE_SUM": float(neg_sum),
                "OVERALL_SUM": float(all_sum)
            })

        RedisController.write_json_to_redis(key, json.dumps({
            "filter": {
                "CAPABILITY_SPONSOR_CODE": model.CAPABILITY_SPONSOR_CODE,
                "ASSESSMENT_AREA_CODE": model.ASSESSMENT_AREA_CODE,
                "PROGRAM_GROUP": model.PROGRAM_GROUP
            },
            "data": summary
        }), redis)

        redis.delete(lock_key)
        return summary

    
    base_k = pd.read_sql(query.get_base_k_query(), con=db_conn.bind)
    approval = pd.read_sql(query.get_approval_query(),con=db_conn.bind)
    jca = pd.read_sql(query.get_jca_query(),con=db_conn.bind)
    eoc = pd.read_sql(query.get_eoc_query(),con=db_conn.bind)
    zbt_delta = pd.read_sql(query.get_zbt_requested_delta_query(),con=db_conn.bind)

    def merge_df(base_dataframe, eoc, jca, approval):
        pro_df = base_dataframe.merge(eoc, on="PROGRAM_NAME", how="left")
        pro_df = pro_df.merge(jca, on="PROGRAM_NAME", how="left")
        pro_df = pro_df.merge(approval, on="PROGRAM_NAME", how="left")

        pro_df["JCA_DESCRIPTION"] = pro_df["JCA_DESCRIPTION"].apply(
            lambda x: "No JCA Alignment" if pd.isna(x) or x == "" else x
        )

        return pro_df

    base_df = merge_df(base_k, eoc, jca, approval)
    delta_df = merge_df(zbt_delta, eoc, jca, approval)

    filter_base_df = base_df[base_df["EOC_CODE"].notnull()]
    filter_delta_df = delta_df[delta_df["EOC_CODE"].notnull()]

    filter_base_df = base_df[
        base_df['EOC_CODE'].notnull() & base_df['JCA_DESCRIPTION'].notnull()
    ]
    filter_delta_df = delta_df[
        delta_df['EOC_CODE'].notnull() & delta_df['JCA_DESCRIPTION'].notnull()
    ]
    # if model.CAPABILITY_SPONSOR_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['CAPABILITY_SPONSOR_CODE'].isin(model.CAPABILITY_SPONSOR_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['CAPABILITY_SPONSOR_CODE'].isin(model.CAPABILITY_SPONSOR_CODE)]
    # if model.POM_SPONSOR_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['POM_SPONSOR_CODE'].isin(model.POM_SPONSOR_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['POM_SPONSOR_CODE'].isin(model.POM_SPONSOR_CODE)]
    # if model.ASSESSMENT_AREA_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['ASSESSMENT_AREA_CODE'].isin(model.ASSESSMENT_AREA_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['ASSESSMENT_AREA_CODE'].isin(model.ASSESSMENT_AREA_CODE)]
    # if model.PROGRAM_GROUP:
    #     filter_base_df = filter_base_df[filter_base_df['PROGRAM_GROUP'].isin(model.PROGRAM_GROUP)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['PROGRAM_GROUP'].isin(model.PROGRAM_GROUP)]

    if filter_base_df.empty and filter_delta_df.empty:
        return ['No Matching Data']

    filter_base_df = filter_base_df.groupby(['PROGRAM_NAME', 'CAPABILITY_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE',
       'POM_SPONSOR_CODE', '26EXT', 'POM_POSITION_CODE', 'FISCAL_YEAR',
       'BASE_K_SUM', 'FISCAL_YEARS', 'PROGRAM_GROUP', 'APPROVAL_ACTION_STATUS']).agg({
            'EOC_CODE': list, 'JCA_DESCRIPTION':list}).reset_index()
    filter_delta_df = filter_delta_df.groupby(['PROGRAM_NAME', 'CAPABILITY_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE',
       'POM_SPONSOR_CODE', '26ZBT_REQUESTED_DELTA', 'POM_POSITION_CODE', 'FISCAL_YEAR',
       'DELTA_AMT', 'FISCAL_YEARS', 'PROGRAM_GROUP', 'APPROVAL_ACTION_STATUS']).agg({
            'EOC_CODE': list, 'JCA_DESCRIPTION':list}).reset_index()

    resource_k_26zbt_requested_delta = {} 
    for _, delta_row in filter_delta_df.iterrows():
        program_name = delta_row['PROGRAM_NAME']
        fiscal_year = str(delta_row['FISCAL_YEAR'])
        delta_amt = float(delta_row['DELTA_AMT'])

        if program_name not in resource_k_26zbt_requested_delta:
            resource_k_26zbt_requested_delta[program_name] = {}
        resource_k_26zbt_requested_delta[program_name][fiscal_year] = delta_amt

    program_name_set = dict()

    ###note, to avoid errors, only process table key name at the end, not during query execution
    ext_key = query.ext.split("_")[-1]
    ext_key = int(ext_key)%2000
    ext_key = f"{ext_key}EXT"

    zbt_key = query.zbt_extract.split("_")[-1]
    zbt_key = int(zbt_key)%2000
    zbt_requested_key = f"{zbt_key}ZBT_REQUESTED"

    zbt_requested_delta_key = f"{zbt_key}ZBT_REQUESTED_DELTA"

    for _, program_row in filter_base_df.iterrows():
        program_name = program_row['PROGRAM_NAME']
        program_group = program_row['PROGRAM_GROUP']
        capability_sponsor_code = program_row["CAPABILITY_SPONSOR_CODE"]
        pom_sponsor_code = program_row["POM_SPONSOR_CODE"]
        assessment_area_code = program_row["ASSESSMENT_AREA_CODE"]
        fiscal_years = str(program_row["FISCAL_YEARS"])
        approval = program_row["APPROVAL_ACTION_STATUS"]
        base_k_sum = program_row["BASE_K_SUM"]
        fiscal_year = str(program_row["FISCAL_YEAR"])
        jca = program_row["JCA_DESCRIPTION"]

        if fiscal_years is None:
            fiscal_years = ""
        fiscal_years = [str(fy).strip() for fy in fiscal_years.split(", ")]

        eoc_code = list(set(program_row["EOC_CODE"]))

        if program_name not in program_name_set:
            d_ = {
                "PROGRAM_NAME": program_name,
                "CAPABILITY_SPONSOR_CODE":capability_sponsor_code,
                "POM_SPONSOR_CODE":pom_sponsor_code,
                "ASSESSMENT_AREA_CODE":assessment_area_code,
                "EOC_CODES": eoc_code,
                "FISCAL_YEARS": ", ".join(fiscal_years),
                "PROGRAM_GROUP": program_group,
                "APPROVAL_ACTION_STATUS": approval,
                "JCA_ALIGNMENT": list(set(jca)),
                "RESOURCE_K": {
                    ext_key: {},
                    zbt_requested_key: {},
                    zbt_requested_delta_key: {}
                }
            }
        
        else:
            d_ = program_name_set[program_name]
            
        d_["RESOURCE_K"][ext_key][fiscal_year] = base_k_sum
        d_["RESOURCE_K"][zbt_requested_delta_key][fiscal_year] = resource_k_26zbt_requested_delta.get(program_name,{}).get(fiscal_year,0)
        d_["RESOURCE_K"][zbt_requested_key][fiscal_year] = base_k_sum + resource_k_26zbt_requested_delta.get(program_name,{}).get(fiscal_year,0)

        program_name_set[program_name] = d_
    
    result = [v for k,v in program_name_set.items()]
    # RedisController.write_json_to_redis(key,json.dumps(result),redis)
    RedisController.write_json_to_redis(key, json.dumps({
            "filter": {
                "CAPABILITY_SPONSOR_CODE": model.CAPABILITY_SPONSOR_CODE,
                "ASSESSMENT_AREA_CODE": model.ASSESSMENT_AREA_CODE,
                "PROGRAM_GROUP": model.PROGRAM_GROUP
            },
            "data": result
        }), redis)
   
    result = get_zbt_iss_summary_from_cache(model, key, redis)

    end_time = time.time()  
    execution_time = end_time - start_time  
    print(f"Execution time: {execution_time:.2f} seconds")  
    
    redis.delete(lock_key) #release key after caching
    return result



def run_async_function_sync(coro):
    try:
        return asyncio.run(coro)  
    except RuntimeError:  
        loop = asyncio.new_event_loop()
        asyncio.set_event_loop(loop)
        return loop.run_until_complete(coro)

def get_iss_summary_fromdb(model,query: ISSQuery,db_conn,redis):
    start_time = time.time()
    # breakpoint()
    approval_filter = model.APPROVAL_FILTER[0] if isinstance(model.APPROVAL_FILTER, list) else model.APPROVAL_FILTER
    

    key = f"api::/socom/iss/program_summary::{approval_filter}"
    lock_key = f"{key}::status"

    if not model.REFRESH:
        if key in redis.keys():
            if needs_refresh_based_on_cache(model, key, redis):
                model.REFRESH = True

    if not model.REFRESH and key in redis.keys():
        cached_result = get_zbt_iss_summary_from_cache(model, key, redis)
        if cached_result:
            return cached_result
        else:
            print("Cache exists but filter mismatch or empty. Proceeding to compute fresh data.")

    # breakpoint()
    approval_filter = model.APPROVAL_FILTER[0] if isinstance(model.APPROVAL_FILTER, list) else model.APPROVAL_FILTER
    print("Debugging, approval_filter type:",approval_filter)

    if model.APPROVAL_FILTER:
        pom_year_iss = query.iss_extract.split("_")[-1]
        pom_year_zbt = query.zbt_extract.split("_")[-1]

        if approval_filter == "issue_all":
            iss_all_sql = query.get_iss_all_query()
            df = pd.read_sql(iss_all_sql, con=db_conn.bind)

            if model.CAPABILITY_SPONSOR_CODE:
                df = df[df["CAPABILITY_SPONSOR_CODE"].isin(model.CAPABILITY_SPONSOR_CODE)]

            if model.ASSESSMENT_AREA_CODE:
                df = df[df["ASSESSMENT_AREA_CODE"].isin(model.ASSESSMENT_AREA_CODE)]

            if model.PROGRAM_GROUP:
                df = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]



            grouped = df.groupby("PROGRAM_CODE")
            iss_key = int(pom_year_iss) % 2000

            summary = []
            for program_code, group in grouped:
                fiscal_map_iss = group.groupby("FISCAL_YEAR")["DELTA_AMT"].sum().to_dict()
                summary.append(
                    build_summary_row(
                        program_code=program_code,
                        group=group,
                        fiscal_map_iss=fiscal_map_iss,
                        iss_key=iss_key
                    )
        )

            if not summary:
                message = [{
                    "MESSAGE": f"No approved ISS found for PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"
                }]
                RedisController.write_json_to_redis(key, json.dumps(message), redis)
                redis.delete(lock_key)
                return message
            
        elif approval_filter == "issue_all_zbt_all":
            df_iss = pd.read_sql(query.get_iss_all_query(), con=db_conn.bind)
            df_iss["SOURCE"] = "ISS"
            df_zbt = pd.read_sql(query.get_zbt_all_query(), con=db_conn.bind)
            df_zbt["SOURCE"] = "ZBT"

            df = pd.concat([df_iss, df_zbt], ignore_index=True)
            
            if model.PROGRAM_GROUP:
                matching_programs = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]
                if matching_programs.empty:
                    message = [{
                        "MESSAGE": f"No value found for PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"
                    }]
                    RedisController.write_json_to_redis(key, json.dumps(message), redis)
                    redis.delete(lock_key)
                    return message
            
            if model.CAPABILITY_SPONSOR_CODE:
                df = df[df["CAPABILITY_SPONSOR_CODE"].isin(model.CAPABILITY_SPONSOR_CODE)]
            if model.ASSESSMENT_AREA_CODE:
                df = df[df["ASSESSMENT_AREA_CODE"].isin(model.ASSESSMENT_AREA_CODE)]
            if model.PROGRAM_GROUP:
                df = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]

            grouped = df.groupby("PROGRAM_CODE")

            iss_key = int(pom_year_iss) % 2000
            zbt_key = int(pom_year_zbt) % 2000

            summary = []
            for program_code, group in grouped:
                group_iss = group[group["SOURCE"] == "ISS"]
                group_zbt = group[group["SOURCE"] == "ZBT"]

                fiscal_map_iss = group_iss.groupby("FISCAL_YEAR")["DELTA_AMT"].sum().to_dict()
                fiscal_map_zbt = group_zbt.groupby("FISCAL_YEAR")["DELTA_AMT"].sum().to_dict()

                summary.append(
                    build_summary_row(
                        program_code=program_code,
                        group=group,
                        fiscal_map_iss=fiscal_map_iss,
                        fiscal_map_zbt=fiscal_map_zbt,
                        iss_key=iss_key,
                        zbt_key=zbt_key
                    )
                )
        
        elif approval_filter == "issue_all_zbt_approved":

            iss_table = f"{SCHEMA}.{query.iss_extract}"
            zbt_table = f"{SCHEMA}.{query.zbt_extract}"
            pom_id = int(query.zbt_extract.split("_")[-1])

            all_zbt_rows = []
            sponsor_list = model.CAPABILITY_SPONSOR_CODE or ["ALL"]
            area_list = model.ASSESSMENT_AREA_CODE or ["ALL"]
            group_list = model.PROGRAM_GROUP or ["ALL"]

            for sponsor in sponsor_list:
                for area in area_list:
                    for group in group_list:
                        zbt_rows = UsrZbtADFinalSaves.get_approved_zbt_events(
                            dt_zbt_extract_table=zbt_table,
                            db_conn=db_conn,
                            capability_sponsor_code=sponsor,
                            assessment_area_code=area,
                            program_group=group
                        )
                        all_zbt_rows.extend(zbt_rows)

            df_zbt = pd.DataFrame(all_zbt_rows, columns=[
                "PROGRAM_CODE", "PROGRAM_NAME", "CAPABILITY_SPONSOR_CODE",
                "ASSESSMENT_AREA_CODE", "PROGRAM_GROUP", "FISCAL_YEAR",
                "DELTA_AMT", "EVENT_NAME", "AD_RECOMENDATION"
            ])
            df_zbt["SOURCE"] = "ZBT"

            iss_all_sql = query.get_iss_all_query()
            df_iss = pd.read_sql(iss_all_sql, con=db_conn.bind)
            df_iss["SOURCE"] = "ISS"

            if model.CAPABILITY_SPONSOR_CODE:
                df_iss = df_iss[df_iss["CAPABILITY_SPONSOR_CODE"].isin(model.CAPABILITY_SPONSOR_CODE)]
            if model.ASSESSMENT_AREA_CODE:
                df_iss = df_iss[df_iss["ASSESSMENT_AREA_CODE"].isin(model.ASSESSMENT_AREA_CODE)]
            if model.PROGRAM_GROUP:
                df_iss = df_iss[df_iss["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]

            scale_zbt_events = df_zbt[df_zbt["AD_RECOMENDATION"] == "Approve at Scale"]["EVENT_NAME"].unique().tolist()
            if scale_zbt_events:
                scale_rows = UsrZbtEveFundLines.get_fydp_sum_for_approve_at_scale(pom_id, scale_zbt_events, db_conn)
                scale_fydp_map = {}
                for row in scale_rows:
                    event_name = row[0]
                    fiscal_years = [str(pom_id + i) for i in range(5)]
                    scale_fydp_map[event_name] = {
                        year: value for year, value in zip(fiscal_years, row[1:])
                    }
            else:
                scale_fydp_map = {}

            df = pd.concat([df_iss, df_zbt], ignore_index=True)

            if model.PROGRAM_GROUP:
                df = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]
                if df.empty:
                    message = [{
                        "MESSAGE": f"No value found for PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"
                    }]
                    RedisController.write_json_to_redis(key, json.dumps(message), redis)
                    redis.delete(lock_key)
                    return message

            grouped = df.groupby("PROGRAM_CODE")
            iss_key = int(pom_id) % 2000
            zbt_key = int(pom_id) % 2000
            iss_requested_delta_key = f"{iss_key}ISS_REQUESTED_DELTA"
            zbt_delta_key = f"{zbt_key}ZBT_REQUESTED_DELTA"

            summary = []
            for program_code, group in grouped:
                group_iss = group[group["SOURCE"] == "ISS"]
                group_zbt = group[group["SOURCE"] == "ZBT"]

                fiscal_map_iss = group_iss.groupby("FISCAL_YEAR")["DELTA_AMT"].sum().to_dict()

                fiscal_map_zbt = {}
                for _, row in group_zbt.iterrows():
                    if row["AD_RECOMENDATION"] == "Approve at Scale" and row["EVENT_NAME"] in scale_fydp_map:
                        for year, amt in scale_fydp_map[row["EVENT_NAME"]].items():
                            fiscal_map_zbt[year] = fiscal_map_zbt.get(year, 0) + amt
                    else:
                        year = str(row["FISCAL_YEAR"])
                        fiscal_map_zbt[year] = fiscal_map_zbt.get(year, 0) + row["DELTA_AMT"]

                pos_sum = group[group["DELTA_AMT"] > 0]["DELTA_AMT"].sum()
                neg_sum = group[group["DELTA_AMT"] < 0]["DELTA_AMT"].sum()
                all_sum = group["DELTA_AMT"].sum()
                fiscal_years = sorted(group["FISCAL_YEAR"].astype(str).unique())

                resource_k = {}
                if fiscal_map_iss:
                    resource_k[iss_requested_delta_key] = {str(k): float(v) for k, v in fiscal_map_iss.items()}
                if fiscal_map_zbt:
                    resource_k[zbt_delta_key] = {str(k): float(v) for k, v in fiscal_map_zbt.items()}

                summary.append({
                    "PROGRAM_CODE": program_code,
                    "PROGRAM_NAME": group["PROGRAM_NAME"].iloc[0],
                    "CAPABILITY_SPONSOR_CODE": group["CAPABILITY_SPONSOR_CODE"].iloc[0],
                    "ASSESSMENT_AREA_CODE": group["ASSESSMENT_AREA_CODE"].iloc[0],
                    "PROGRAM_GROUP": group["PROGRAM_GROUP"].iloc[0],
                    "FISCAL_YEARS": ", ".join(fiscal_years),
                    "APPROVAL_ACTION_STATUS": "PENDING",
                    "JCA_ALIGNMENT": [],
                    "EOC_CODES": list(group["EOC_CODE"].unique()) if "EOC_CODE" in group.columns else [],
                    "RESOURCE_K": resource_k,
                    "POSITIVE_SUM": float(pos_sum),
                    "NEGATIVE_SUM": float(neg_sum),
                    "OVERALL_SUM": float(all_sum)
                })

        elif approval_filter == "issue_approved_zbt_approved":

            iss_table = f"{SCHEMA}.{query.iss_extract}"
            zbt_table = f"{SCHEMA}.{query.zbt_extract}"
            pom_year_iss = int(query.iss_extract.split("_")[-1])
            pom_year_zbt = int(query.zbt_extract.split("_")[-1])
            # breakpoint()
            # ISS Approved
            iss_rows = UsrIssADFinalSaves.get_approved_iss_events(
                dt_iss_extract_table=iss_table,
                db_conn=db_conn,
                capability_sponsor_code=model.CAPABILITY_SPONSOR_CODE[0] if model.CAPABILITY_SPONSOR_CODE else "ALL",
                assessment_area_code=model.ASSESSMENT_AREA_CODE[0] if model.ASSESSMENT_AREA_CODE else "ALL",
                program_group=model.PROGRAM_GROUP[0] if model.PROGRAM_GROUP else "ALL"
            )

            all_zbt_rows = []
            sponsor_list = model.CAPABILITY_SPONSOR_CODE or ["ALL"]
            area_list = model.ASSESSMENT_AREA_CODE or ["ALL"]
            group_list = model.PROGRAM_GROUP or ["ALL"]

            for sponsor in sponsor_list:
                for area in area_list:
                    for group in group_list:
                        rows = UsrZbtADFinalSaves.get_approved_zbt_events(
                            dt_zbt_extract_table=zbt_table,
                            db_conn=db_conn,
                            capability_sponsor_code=sponsor,
                            assessment_area_code=area,
                            program_group=group
                        )
                        all_zbt_rows.extend(rows)

            df_iss = pd.DataFrame(iss_rows, columns=[
                "PROGRAM_CODE", "PROGRAM_NAME", "CAPABILITY_SPONSOR_CODE",
                "ASSESSMENT_AREA_CODE", "PROGRAM_GROUP", "FISCAL_YEAR",
                "DELTA_AMT", "EVENT_NAME", "AD_RECOMENDATION"
            ])
            df_iss["SOURCE"] = "ISS"

            df_zbt = pd.DataFrame(all_zbt_rows, columns=[
                "PROGRAM_CODE", "PROGRAM_NAME", "CAPABILITY_SPONSOR_CODE",
                "ASSESSMENT_AREA_CODE", "PROGRAM_GROUP", "FISCAL_YEAR",
                "DELTA_AMT", "EVENT_NAME", "AD_RECOMENDATION"
            ])
            df_zbt["SOURCE"] = "ZBT"
            # breakpoint()

            # FYDP for Approve at Scale from ZBT
            scale_zbt_events = df_zbt[df_zbt["AD_RECOMENDATION"] == "Approve at Scale"]["EVENT_NAME"].unique().tolist()
            if scale_zbt_events:
                scale_rows = UsrZbtEveFundLines.get_fydp_sum_for_approve_at_scale(pom_year_zbt, scale_zbt_events, db_conn)
                scale_fydp_map = {
                    row[0]: {str(pom_year_zbt + i): row[1:][i] for i in range(5)}
                    for row in scale_rows
                }
            else:
                scale_fydp_map = {}

            # FYDP for Approve at Scale from ISS
            approve_at_scale_iss = df_iss[df_iss["AD_RECOMENDATION"] == "Approve at Scale"]
            iss_event_names = approve_at_scale_iss["EVENT_NAME"].unique().tolist()

            if iss_event_names:
                # funding_lines = UsrEventFundingLines.get_event_funding_lines(pom_id=pom_year_iss, event_names=iss_event_names,db_conn=db_conn)
                funding_lines = run_async_function_sync(
                    UsrEventFundingLines.get_event_funding_lines(
                        pom_id=pom_year_iss,
                        event_names=iss_event_names,
                        db_conn=db_conn
                    )
                )
                
                # print(type(funding_lines))
                # breakpoint()
                funding_map = {
                    line["EVENT_NAME"]: {
                        str(pom_year_iss + i): line[f"FY_{i+1}"] for i in range(5)
                    } for line in funding_lines
                }
            else:
                funding_map = {}

            df = pd.concat([df_iss, df_zbt], ignore_index=True)
            # breakpoint()
            if model.PROGRAM_GROUP:
                df = df[df["PROGRAM_GROUP"].isin(model.PROGRAM_GROUP)]
                if df.empty:
                    message = [{
                        "MESSAGE": f"No value found for PROGRAM_GROUP(s): {model.PROGRAM_GROUP}"
                    }]
                    RedisController.write_json_to_redis(key, json.dumps(message), redis)
                    redis.delete(lock_key)
                    return message

            grouped = df.groupby("PROGRAM_CODE")
            iss_key = int(pom_year_iss) % 2000
            zbt_key = int(pom_year_zbt) % 2000
            iss_requested_delta_key = f"{iss_key}ISS_REQUESTED_DELTA"
            zbt_requested_delta_key = f"{zbt_key}ZBT_REQUESTED_DELTA"

            summary = []
            for program_code, group in grouped:
                group_iss = group[group["SOURCE"] == "ISS"]
                group_zbt = group[group["SOURCE"] == "ZBT"]

                # Filter out missing FYDP
                group_iss = group_iss[~(
                    (group_iss["AD_RECOMENDATION"] == "Approve at Scale") &
                    (~group_iss["EVENT_NAME"].isin(funding_map))
                )]

                fiscal_map_iss = group_iss[group_iss["AD_RECOMENDATION"] == "Approve"].groupby("FISCAL_YEAR")["DELTA_AMT"].sum().to_dict()
                for _, row in group_iss.iterrows():
                    if row["AD_RECOMENDATION"] == "Approve at Scale" and row["EVENT_NAME"] in funding_map:
                        for fy, val in funding_map[row["EVENT_NAME"]].items():
                            if val is not None:
                                fiscal_map_iss[fy] = fiscal_map_iss.get(fy, 0) + val

                fiscal_map_zbt = {}
                for _, row in group_zbt.iterrows():
                    if row["AD_RECOMENDATION"] == "Approve at Scale" and row["EVENT_NAME"] in scale_fydp_map:
                        for fy, val in scale_fydp_map[row["EVENT_NAME"]].items():
                            if val is not None:
                                fiscal_map_zbt[fy] = fiscal_map_zbt.get(fy, 0) + val
                    else:
                        year = str(row["FISCAL_YEAR"])
                        fiscal_map_zbt[year] = fiscal_map_zbt.get(year, 0) + row["DELTA_AMT"]

                pos_sum = group[group["DELTA_AMT"] > 0]["DELTA_AMT"].sum()
                neg_sum = group[group["DELTA_AMT"] < 0]["DELTA_AMT"].sum()
                all_sum = group["DELTA_AMT"].sum()
                fiscal_years = sorted(group["FISCAL_YEAR"].astype(str).unique())

                resource_k = {}
                if fiscal_map_iss:
                    resource_k[iss_requested_delta_key] = {str(k): float(v) for k, v in fiscal_map_iss.items()}
                if fiscal_map_zbt:
                    resource_k[zbt_requested_delta_key] = {str(k): float(v) for k, v in fiscal_map_zbt.items()}
                if not resource_k:
                    continue
                
                summary.append({
                    "PROGRAM_CODE": program_code,
                    "PROGRAM_NAME": group["PROGRAM_NAME"].iloc[0],
                    "CAPABILITY_SPONSOR_CODE": group["CAPABILITY_SPONSOR_CODE"].iloc[0],
                    "ASSESSMENT_AREA_CODE": group["ASSESSMENT_AREA_CODE"].iloc[0],
                    "PROGRAM_GROUP": group["PROGRAM_GROUP"].iloc[0],
                    "FISCAL_YEARS": ", ".join(fiscal_years),
                    "APPROVAL_ACTION_STATUS": "PENDING",
                    "JCA_ALIGNMENT": [],
                    "EOC_CODES": list(group["EOC_CODE"].unique()) if "EOC_CODE" in group.columns else [],
                    "RESOURCE_K": resource_k,
                    "POSITIVE_SUM": float(pos_sum),
                    "NEGATIVE_SUM": float(neg_sum),
                    "OVERALL_SUM": float(all_sum)
                })

        else:
            raise HTTPException(400, f"Unsupported APPROVAL_FILTER: {approval_filter}")

        RedisController.write_json_to_redis(key, json.dumps({
            "filter": {
                "CAPABILITY_SPONSOR_CODE": model.CAPABILITY_SPONSOR_CODE,
                "ASSESSMENT_AREA_CODE": model.ASSESSMENT_AREA_CODE,
                "PROGRAM_GROUP": model.PROGRAM_GROUP
            },
            "data": summary
        }), redis)

        redis.delete(lock_key)
        print("Execution time: {:.2f} seconds".format(time.time() - start_time))
        return summary

    # breakpoint()
    
    base_k = pd.read_sql(query.get_base_k_query(), con=db_conn.bind)
    iss_k = pd.read_sql(query.get_iss_k_query(), con=db_conn.bind)
    approval = pd.read_sql(query.get_approval_query(),con=db_conn.bind)
    jca = pd.read_sql(query.get_jca_query(),con=db_conn.bind)
    eoc = pd.read_sql(query.get_eoc_query(),con=db_conn.bind)
    iss_delta = pd.read_sql(query.get_iss_requested_delta_query(),con=db_conn.bind)
    
    def merge_df(base_dataframe, eoc, jca, approval):
        pro_df = base_dataframe.merge(eoc, on='PROGRAM_NAME', how='left')
        pro_df = pro_df.merge(jca, on='PROGRAM_NAME', how='left')
        pro_df = pro_df.merge(approval, on='PROGRAM_NAME', how='left')
        
        pro_df["JCA_DESCRIPTION"] = pro_df["JCA_DESCRIPTION"].apply(lambda x: "No JCA Alignment" if pd.isna(x) or x == "" else x)
        
        return pro_df
    
    base_df = merge_df(base_k, eoc, jca, approval)
    delta_df = merge_df(iss_delta, eoc, jca, approval)
    delta_iss_k_df = merge_df(iss_k, eoc, jca, approval)
    
    filter_base_df = base_df[
        base_df['EOC_CODE'].notnull() & base_df['JCA_DESCRIPTION'].notnull()
    ]
    filter_delta_df = delta_df[
        delta_df['EOC_CODE'].notnull() & delta_df['JCA_DESCRIPTION'].notnull()
    ]
    filter_delta_k_df = delta_iss_k_df[
        delta_iss_k_df['EOC_CODE'].notnull() & delta_iss_k_df['JCA_DESCRIPTION'].notnull()
    ]

    # if model.CAPABILITY_SPONSOR_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['CAPABILITY_SPONSOR_CODE'].isin(model.CAPABILITY_SPONSOR_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['CAPABILITY_SPONSOR_CODE'].isin(model.CAPABILITY_SPONSOR_CODE)]
    #     filter_delta_k_df = filter_delta_k_df[filter_delta_k_df['CAPABILITY_SPONSOR_CODE'].isin(model.CAPABILITY_SPONSOR_CODE)]
    # if model.POM_SPONSOR_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['POM_SPONSOR_CODE'].isin(model.POM_SPONSOR_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['POM_SPONSOR_CODE'].isin(model.POM_SPONSOR_CODE)]
    #     filter_delta_k_df = filter_delta_k_df[filter_delta_k_df['POM_SPONSOR_CODE'].isin(model.POM_SPONSOR_CODE)]
    # if model.ASSESSMENT_AREA_CODE:
    #     filter_base_df = filter_base_df[filter_base_df['ASSESSMENT_AREA_CODE'].isin(model.ASSESSMENT_AREA_CODE)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['ASSESSMENT_AREA_CODE'].isin(model.ASSESSMENT_AREA_CODE)]
    #     filter_delta_k_df = filter_delta_k_df[filter_delta_k_df['ASSESSMENT_AREA_CODE'].isin(model.ASSESSMENT_AREA_CODE)]
    # if model.PROGRAM_GROUP:
    #     filter_base_df = filter_base_df[filter_base_df['PROGRAM_GROUP'].isin(model.PROGRAM_GROUP)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['PROGRAM_GROUP'].isin(model.PROGRAM_GROUP)]
    #     filter_delta_k_df = filter_delta_k_df[filter_delta_k_df['PROGRAM_GROUP'].isin(model.PROGRAM_GROUP)]
    # if model.PROGRAM_NAME:
    #     filter_base_df = filter_base_df[filter_base_df['PROGRAM_NAME'].isin(model.PROGRAM_NAME)]
    #     filter_delta_df = filter_delta_df[filter_delta_df['PROGRAM_NAME'].isin(model.PROGRAM_NAME)]
    #     filter_delta_k_df = filter_delta_k_df[filter_delta_k_df['PROGRAM_NAME'].isin(model.PROGRAM_NAME)]

    # filter_base_df.replace([np.inf, -np.inf, np.nan], 0, inplace=True)
    # filter_delta_df.replace([np.inf, -np.inf, np.nan], 0, inplace=True)
    # filter_delta_k_df.replace([np.inf, -np.inf, np.nan], 0, inplace=True)
   
    
    if len(filter_base_df) == 0 or  len(filter_delta_df) == 0:
        return [] 

    filter_base_df = filter_base_df.groupby(['PROGRAM_NAME', 'CAPABILITY_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE',
       'POM_SPONSOR_CODE', '26EXT', 'POM_POSITION_CODE', 'FISCAL_YEAR',
       'BASE_K_SUM', 'FISCAL_YEARS', 'PROGRAM_GROUP', 'APPROVAL_ACTION_STATUS']).agg({
            'EOC_CODE': list, 'JCA_DESCRIPTION':list}).reset_index()
    
    filter_delta_df = filter_delta_df.groupby(['PROGRAM_NAME', 'CAPABILITY_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE',
       'POM_SPONSOR_CODE', '26ISS_REQUESTED_DELTA', 'POM_POSITION_CODE', 'FISCAL_YEAR',
       'DELTA_AMT', 'FISCAL_YEARS', 'PROGRAM_GROUP', 'APPROVAL_ACTION_STATUS']).agg({
            'EOC_CODE': list, 'JCA_DESCRIPTION':list}).reset_index()

    filter_delta_k_df = filter_delta_k_df.groupby(['PROGRAM_NAME', 'CAPABILITY_SPONSOR_CODE', 'ASSESSMENT_AREA_CODE',
       'POM_SPONSOR_CODE', 'POM_POSITION_CODE', 'FISCAL_YEAR', 'FISCAL_YEARS', 'PROGRAM_GROUP', 'APPROVAL_ACTION_STATUS','PROP_AMT']).agg({
            'EOC_CODE': list, 'JCA_DESCRIPTION':list}).reset_index()
    
    resource_k_26iss_requested_delta = {} 
    for _, delta_row in filter_delta_df.iterrows():
        program_name = delta_row['PROGRAM_NAME']
        fiscal_year = str(delta_row['FISCAL_YEAR'])
        delta_amt = float(delta_row['DELTA_AMT'])

        if program_name not in resource_k_26iss_requested_delta:
            resource_k_26iss_requested_delta[program_name] = {}
        resource_k_26iss_requested_delta[program_name][fiscal_year] = delta_amt
    
    resource_k_26iss = {} 
    for _, delta_row in filter_delta_k_df.iterrows():
        program_name = delta_row['PROGRAM_NAME']
        fiscal_year = str(delta_row['FISCAL_YEAR'])
        prop_amt = float(delta_row['PROP_AMT'])
        if program_name not in resource_k_26iss:
            resource_k_26iss[program_name] = {}
        resource_k_26iss[program_name][fiscal_year] = prop_amt
    
    program_name_set = dict() 

    ###defining the dynamic keys to display
    ext_key = query.ext.split("_")[-1]
    ext_key_year = int(ext_key)%2000

    zbt_key = query.zbt.split("_")[-1]
    zbt_key_year = int(zbt_key)%2000
    
    iss_extract_key_year = query.iss_extract.split("_")[-1]
    iss_extract_key_year = int(iss_extract_key_year)%2000

    for _, program_row in filter_base_df.iterrows():
        program_name = program_row['PROGRAM_NAME']
        program_group = program_row['PROGRAM_GROUP']
        capability_sponsor_code = program_row["CAPABILITY_SPONSOR_CODE"]
        pom_sponsor_code = program_row["POM_SPONSOR_CODE"]
        assessment_area_code = program_row["ASSESSMENT_AREA_CODE"]
        approval = program_row["APPROVAL_ACTION_STATUS"]
        base_k_sum = program_row["BASE_K_SUM"]
        fiscal_year = str(program_row["FISCAL_YEAR"])
        jca = program_row["JCA_DESCRIPTION"]
        fiscal_years = str(program_row["FISCAL_YEARS"])
        fiscal_years_list = [str(fy).strip() for fy in fiscal_years.split(", ")]
        eoc_code = list(set(program_row["EOC_CODE"]))

        if program_name not in program_name_set:
            d_ = {
                "PROGRAM_NAME": program_name,
                "CAPABILITY_SPONSOR_CODE":capability_sponsor_code,
                "POM_SPONSOR_CODE":pom_sponsor_code,
                "ASSESSMENT_AREA_CODE":assessment_area_code,
                "EOC_CODES": eoc_code,
                "FISCAL_YEARS": ", ".join(fiscal_years_list),
                "PROGRAM_GROUP": program_group,
                "APPROVAL_ACTION_STATUS": approval,
                "JCA_ALIGNMENT": list(set(jca)),
                "RESOURCE_K": {
                    f"{ext_key_year}EXT": {},
                    f"{zbt_key_year}ZBT": {},
                    f"{zbt_key_year}ZBT_DELTA":{},
                    f"{iss_extract_key_year}ISS_REQUESTED": {},
                    f"{iss_extract_key_year}ISS_REQUESTED_DELTA": {}
                }
            }
        
        else:
            d_ = program_name_set[program_name]

        d_["RESOURCE_K"][f"{ext_key_year}EXT"][fiscal_year] = base_k_sum
        d_["RESOURCE_K"][f"{zbt_key_year}ZBT"][fiscal_year] = resource_k_26iss[program_name][fiscal_year] 
        d_["RESOURCE_K"][f"{zbt_key_year}ZBT_DELTA"][fiscal_year] = d_["RESOURCE_K"][f"{zbt_key_year}ZBT"].get(fiscal_year,0) - d_["RESOURCE_K"][f"{ext_key_year}EXT"].get(fiscal_year,0)
        d_["RESOURCE_K"][f"{iss_extract_key_year}ISS_REQUESTED_DELTA"][fiscal_year] = resource_k_26iss_requested_delta.get(program_name,{}).get(fiscal_year,0)
        d_["RESOURCE_K"][f"{iss_extract_key_year}ISS_REQUESTED"][fiscal_year] = resource_k_26iss.get(program_name,{}).get(fiscal_year,0)+ resource_k_26iss_requested_delta.get(program_name,{}).get(fiscal_year,0)

        program_name_set[program_name] = d_

    result = [v for k,v in program_name_set.items()]
    RedisController.write_json_to_redis(key,json.dumps(result),redis)
    #auto filter with the model
    result = get_zbt_iss_summary_from_cache(model,key,redis)
    end_time = time.time()  
    execution_time = end_time - start_time  
    print(f"Execution time: {execution_time:.2f} seconds") 
    redis.delete(lock_key) 
    return result

async def get_zbt_event_summary_view(event_names: List[str], db_conn):
    table_name = ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractModel,table_name=table_name)
    print(orm_model.__table_args__)

    # Use asyncio.gather to run both async classmethods concurrently
    tasks = [
        orm_model.get_event_summary_list(event_names, db_conn),
        orm_model.get_distinct_fiscal_years(db_conn),
        orm_model.get_event_title_from_name(event_names, db_conn),
        orm_model.get_event_justification_from_name(event_names, db_conn)
    ]
    
    # Run both tasks concurrently and wait for both to complete
    events,all_years,event_titles,event_just = await asyncio.gather(*tasks)

    if not events:
        raise HTTPException(404,f"No funding ZBT found for this event: {', '.join(event_names)}")
    
    agg_data = defaultdict(dict)
    # print(events)

    
    for event in events:
        event_name = event["EVENT_NAME"]
        key = (
            event["EVENT_NAME"],
            event['PROGRAM_GROUP'],
            event['PROGRAM_CODE'],
            event['EOC_CODE'],
            event['CAPABILITY_SPONSOR_CODE'],
            event['ASSESSMENT_AREA_CODE'],
            event['RESOURCE_CATEGORY_CODE'],
            event['SPECIAL_PROJECT_CODE'],
            event['OSD_PROGRAM_ELEMENT_CODE']
        )
        # if key in agg_data:
        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']
        
        if 'FISCAL_YEAR' not in agg_data[key]:
            agg_data[key]['FISCAL_YEAR'] = {year:0 for year in all_years} #default dict is not recursive

        agg_data[key]['FISCAL_YEAR'][year] = agg_data[key]['FISCAL_YEAR'].get(year,0) + delta_amt
        
        agg_data[key]["EVENT_NAME"] = event_name
        agg_data[key]['PROGRAM_GROUP'] = event['PROGRAM_GROUP']
        agg_data[key]['PROGRAM_CODE'] = event['PROGRAM_CODE']
        agg_data[key]['EOC_CODE'] = event['EOC_CODE']
        agg_data[key]['CAPABILITY_SPONSOR_CODE'] = event['CAPABILITY_SPONSOR_CODE']
        agg_data[key]['ASSESSMENT_AREA_CODE'] = event['ASSESSMENT_AREA_CODE']
        agg_data[key]['RESOURCE_CATEGORY_CODE'] = event['RESOURCE_CATEGORY_CODE']
        agg_data[key]['SPECIAL_PROJECT_CODE'] = event['SPECIAL_PROJECT_CODE']
        agg_data[key]['OSD_PROGRAM_ELEMENT_CODE'] = event['OSD_PROGRAM_ELEMENT_CODE']
        agg_data[key]['ROW_ID'] = event["EVENT_NAME"]+"_"+event['PROGRAM_CODE'] +  "_" + event["CAPABILITY_SPONSOR_CODE"] + "_" +\
            event["ASSESSMENT_AREA_CODE"]+"_"+event['RESOURCE_CATEGORY_CODE']+"_"+event["EOC_CODE"]+"_"+event['OSD_PROGRAM_ELEMENT_CODE']

        
        
    result = defaultdict(list)
    for key in agg_data:
        result[agg_data[key]["EVENT_NAME"]].append(agg_data[key])

    return {**result,
            #"events":list(agg_data.values()),
            "all_years":all_years,
            "event_titles": {name: title for name, title in event_titles},
            "event_justifications": {name: just for name, just in event_just}
    }

# async def get_latest_ad_recommendations(event_names:List[str],db_conn):
#     data = UsrIssADSaves.get_latest_ad_recommendations(event_names,db_conn)
#     return data

async def get_iss_event_summary_view(event_names: List[str], db_conn):
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractModel, table_name=table_name)

    tasks = [
        orm_model.get_event_summary_list(event_names, db_conn),
        orm_model.get_distinct_fiscal_years(db_conn),
        orm_model.get_event_title_from_name(event_names, db_conn),
        orm_model.get_event_justification_from_name(event_names, db_conn),
    ]
    
    events, all_years, event_titles, event_just = await asyncio.gather(*tasks)

    if not events:
        raise HTTPException(404, f"No funding ISS found for events: {', '.join(event_names)}")

    agg_data = defaultdict(dict)

    for event in events:
        event_name = event["EVENT_NAME"]
        key = (
            event["EVENT_NAME"],
            event["PROGRAM_GROUP"],
            event["PROGRAM_CODE"],
            event["EOC_CODE"],
            event["CAPABILITY_SPONSOR_CODE"],
            event["ASSESSMENT_AREA_CODE"],
            event["RESOURCE_CATEGORY_CODE"],
            event["SPECIAL_PROJECT_CODE"],
            event["OSD_PROGRAM_ELEMENT_CODE"]
        )

        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']

        if 'FISCAL_YEAR' not in agg_data[key]:
            agg_data[key]['FISCAL_YEAR'] = {year: 0 for year in all_years}

        agg_data[key]['FISCAL_YEAR'][year] = agg_data[key]['FISCAL_YEAR'].get(year, 0) + delta_amt

        agg_data[key]["CAPABILITY_SPONSOR_CODE"] = event["CAPABILITY_SPONSOR_CODE"]
        agg_data[key]["EVENT_NAME"] = event_name
        agg_data[key]['PROGRAM_GROUP'] = event['PROGRAM_GROUP']
        agg_data[key]['PROGRAM_CODE'] = event['PROGRAM_CODE']
        agg_data[key]['EOC_CODE'] = event['EOC_CODE']
        agg_data[key]['ASSESSMENT_AREA_CODE'] = event['ASSESSMENT_AREA_CODE']
        agg_data[key]['RESOURCE_CATEGORY_CODE'] = event['RESOURCE_CATEGORY_CODE']
        agg_data[key]['SPECIAL_PROJECT_CODE'] = event['SPECIAL_PROJECT_CODE']
        agg_data[key]['OSD_PROGRAM_ELEMENT_CODE'] = event['OSD_PROGRAM_ELEMENT_CODE']
        agg_data[key]['ROW_ID'] = event["EVENT_NAME"]+"_"+event['PROGRAM_CODE'] +  "_" + event["CAPABILITY_SPONSOR_CODE"] + "_" +\
            event["ASSESSMENT_AREA_CODE"]+"_"+event['RESOURCE_CATEGORY_CODE']+"_"+event["EOC_CODE"]+"_"+event['OSD_PROGRAM_ELEMENT_CODE']

    # for key in agg_data:
    #     agg_data[key]["CAPABILITY_SPONSOR_CODE"] = list(agg_data[key]["CAPABILITY_SPONSOR_CODE"])

    result = defaultdict(list)
    for key in agg_data:
        result[agg_data[key]["EVENT_NAME"]].append(agg_data[key])

    return {**result,
        #"events": list(agg_data.values()),
        "all_years": all_years,
        "event_titles": {name: title for name, title in event_titles},
        "event_justifications": {name: just for name, just in event_just}
    }


async def get_iss_event_summary_list_view(event_names:List[str],db_conn):
    
    """
    This function is to get the event summary given a list of event_names
    """
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractModel,table_name=table_name)
    active_pom_id = UsrLookupPOMPosition.get_active_pom_id(db_conn)

    # Use asyncio.gather to run both async classmethods concurrently
    events = orm_model.get_event_summary_list(event_names,db_conn)
    all_years = orm_model.get_distinct_fiscal_years(db_conn)
    event_titles = orm_model.get_event_title_from_name(event_names,db_conn)
    event_just = orm_model.get_event_justification_from_name(event_names,db_conn)
    ad_consensus = UsrIssADFinalSaves.get_ad_recommendations(event_names, active_pom_id, db_conn)  
    funding_lines = UsrEventFundingLines.get_event_funding_lines(active_pom_id, event_names, db_conn)  

    # Run both tasks concurrently and wait for both to complete
    events,all_years,event_titles,event_just,ad_consensus,funding_lines = await asyncio.gather(
        events, 
        all_years,
        event_titles,
        event_just,
        ad_consensus,
        funding_lines
        )

    # breakpoint()
    if not events:
        raise HTTPException(404,f"No funding ISS found for events: {event_names}")
    
    funding_lines_map = {
        funding["EVENT_NAME"]: {
        year: funding[f"FY_{idx + 1}"] for idx, year in enumerate(all_years)
        }
        for funding in funding_lines
    }

    
    # breakpoint()
    distinct_years = await orm_model.get_distinct_fiscal_years(db_conn)
    # print(distinct_years)
    #reprocess to combine all years into a dictionary for each event
    agg_data = defaultdict(dict)
    # print(events)

    ad_consensus_dict = {item["EVENT_NAME"]: item["AD_RECOMENDATION"] for item in ad_consensus}

    for event in events:

        key = (
            event["EVENT_NAME"]
            # event['PROGRAM_GROUP'],
            # event['PROGRAM_CODE'],
            # event['EOC_CODE'],
            # event['CAPABILITY_SPONSOR_CODE'],
            # event['ASSESSMENT_AREA_CODE'],
            # event['RESOURCE_CATEGORY_CODE'],
            # event['SPECIAL_PROJECT_CODE'],
            # event['OSD_PROGRAM_ELEMENT_CODE']
        )
        # if key in agg_data:
        event_name = event["EVENT_NAME"]
        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']

        if 'FISCAL_YEAR' not in agg_data[event_name]:
            agg_data[event_name]['FISCAL_YEAR'] = {year:0 for year in distinct_years} #default dict is not recursive
        
        if 'CAPABILITY_SPONSOR_CODE' not in agg_data[event_name]:
            agg_data[event_name]['CAPABILITY_SPONSOR_CODE'] = set()
        
        agg_data[event_name]['FISCAL_YEAR'][year] = agg_data[event_name]['FISCAL_YEAR'].get(year,0) + delta_amt

        agg_data[event_name]['EVENT_NAME'] = event['EVENT_NAME'] 
        agg_data[event_name]['PROGRAM_GROUP'] = event['PROGRAM_GROUP']
        agg_data[event_name]['PROGRAM_CODE'] = event['PROGRAM_CODE']
        agg_data[event_name]['EOC_CODE'] = event['EOC_CODE']
        agg_data[event_name]['CAPABILITY_SPONSOR_CODE'].add(event["CAPABILITY_SPONSOR_CODE"])
        agg_data[event_name]['ASSESSMENT_AREA_CODE'] = event['ASSESSMENT_AREA_CODE']
        agg_data[event_name]['RESOURCE_CATEGORY_CODE'] = event['RESOURCE_CATEGORY_CODE']
        agg_data[event_name]['SPECIAL_PROJECT_CODE'] = event['SPECIAL_PROJECT_CODE']
        agg_data[event_name]['OSD_PROGRAM_ELEMENT_CODE'] = event['OSD_PROGRAM_ELEMENT_CODE']
        agg_data[event_name]['EVENT_NAME'] =event['EVENT_NAME']
        agg_data[event_name]['AD_CONSENSUS'] = ad_consensus_dict.get(event_name, "Not Decided")

        # agg_data[event_name]['AD_CONSENSUS'] =event['AD_CONSENSUS']

    for event_name in agg_data:
        if agg_data[event_name].get('AD_CONSENSUS') == "Approve at scale":
            agg_data[event_name]['AD_CONSENSUS'] = "Approve at Scale"

    for event_name, recommendation in ad_consensus_dict.items():
        if event_name in agg_data:
            fiscal_years = agg_data[event_name]["FISCAL_YEAR"]

            if recommendation == "Disapprove":
                for year in fiscal_years:
                    fiscal_years[year] = 0

            elif recommendation == "Approve at scale":
                if event_name in funding_lines_map:
                    fiscal_years.update(funding_lines_map[event_name])

            else:
                continue
    
    ###Now parse the Review statuses
    from sqlalchemy import select, union_all, func, distinct, literal_column
    
    cte = (
        union_all(
            select(UsrIssAOSaves.EVENT_ID, UsrIssAOSaves.AO_RECOMENDATION.label("RECS")).where(
                UsrIssAOSaves.IS_DELETED == False),
            select(UsrIssADSaves.EVENT_ID, UsrIssADSaves.AD_RECOMENDATION.label("RECS")).where(
                UsrIssADSaves.IS_DELETED == False)
        ).cte("CTE")
    )

    query = (
        select(
            cte.c.EVENT_ID,
            func.group_concat(
                distinct(func.concat(cte.c.RECS, literal_column("' Flag'"))),
                separator=", "
            ).label("RECS")
        )
        .where(
            cte.c.RECS != '',
            cte.c.RECS.isnot(None),
            cte.c.EVENT_ID.in_(event_names)
        ).group_by(cte.c.EVENT_ID)
    )

    event_rows = db_conn.execute(query)
    
    #FIND distinct events on DT_ISS_EXTRACT table
    query = select(distinct(orm_model.EVENT_NAME))
    distinct_events = db_conn.execute(query)
    distinct_events = [row[0] for row in distinct_events]

    event_review_dict = {} #default
    event_review_dict = {
        row._mapping["EVENT_ID"]: row._mapping["RECS"]
        for row in event_rows
    }

    # Add any missing event IDs with 'Unreviewed'
    event_review_dict.update({
        event_id: "Unreviewed"
        for event_id in event_names
            if event_id not in event_review_dict
    })
 
    return {"events":agg_data,
            "all_years":all_years,
            "event_title":{event_name:event_title for event_name,event_title in event_titles},
            "event_justification":{event_name:event_just for event_name,event_just in event_just},
            "ad_consensus": {
                key: value.replace("Approve at scale", "Approve at Scale") for key, value in ad_consensus_dict.items()
            },
            "event_review_status":event_review_dict
    }



async def get_iss_event_summary_list_export(event_names:List[str],db_conn):
    """
    This function is to get the event summary given a list of event_names, for exporting purposes
    """
    table_name = IssSummaryTableSet.CURRENT["ISS_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtISSExtractModel,table_name=table_name)
    active_pom_id = UsrLookupPOMPosition.get_active_pom_id(db_conn)

    # Use asyncio.gather to run both async classmethods concurrently
    events = orm_model.get_event_summary_list(event_names,db_conn)
    all_years = orm_model.get_distinct_fiscal_years(db_conn)
    event_titles = orm_model.get_event_title_from_name(event_names,db_conn)
    event_just = orm_model.get_event_justification_from_name(event_names,db_conn)
    ad_consensus = UsrIssADFinalSaves.get_ad_recommendations(event_names, active_pom_id, db_conn)  
    funding_lines = UsrEventFundingLines.get_event_funding_lines(active_pom_id, event_names, db_conn)  

    # Run both tasks concurrently and wait for both to complete
    events,all_years,event_titles,event_just,ad_consensus,funding_lines = await asyncio.gather(
        events, 
        all_years,
        event_titles,
        event_just,
        ad_consensus,
        funding_lines
        )

    
    if not events:
        raise HTTPException(404,f"No funding ISS found for events: {event_names}")
    
    manual_funding_key_map = {} #to hold and make the funding lines for each block
    # ("EOC_CODE","EVENT_NAME","PROGRAM_GROUP","ASSESSMENT_AREA_CODE","SPECIAL_PROJECT_CODE",
        # "RESOURCE_CATEGORY_CODE","CAPABILITY_SPONSOR_CODE","OSD_PROGRAM_ELEMENT_CODE")
    
    for row in funding_lines:
        approval_table = row["APPROVAL_TABLE"]
        for block in approval_table:
            key = (
                block["PROGRAM_GROUP"],
                block["EOC_CODE"],
                block["CAPABILITY_SPONSOR_CODE"],
                block["ASSESSMENT_AREA_CODE"],
                block["RESOURCE_CATEGORY_CODE"],
                block["SPECIAL_PROJECT_CODE"],
                block["OSD_PROGRAM_ELEMENT_CODE"],
                block["EVENT_NAME"])
            # breakpoint()
            manual_funding_key_map[key] = block

    # print(distinct_years)
    #reprocess to combine all years into a dictionary for each event
    agg_data = defaultdict(dict)
    # print(events)

    ad_consensus_dict = {item["EVENT_NAME"]: item["AD_RECOMENDATION"] for item in ad_consensus}
    
    for event in events:

        key = (
            event['PROGRAM_GROUP'],
            event['EOC_CODE'],
            event['CAPABILITY_SPONSOR_CODE'],
            event['ASSESSMENT_AREA_CODE'],
            event['RESOURCE_CATEGORY_CODE'],
            event['SPECIAL_PROJECT_CODE'],
            event['OSD_PROGRAM_ELEMENT_CODE'],
            event['EVENT_NAME']
        )
        # if key in agg_data:
        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']
        
        if 'FISCAL_YEAR' not in agg_data[key]:
            agg_data[key]['FISCAL_YEAR'] = {year:0 for year in all_years} #default dict is not recursive

        agg_data[key]['FISCAL_YEAR'][year] = agg_data[key]['FISCAL_YEAR'].get(year,0) + delta_amt
        
        agg_data[key]['PROGRAM_GROUP'] = event['PROGRAM_GROUP']
        agg_data[key]['EOC_CODE'] = event['EOC_CODE']
        agg_data[key]['CAPABILITY_SPONSOR_CODE'] = event['CAPABILITY_SPONSOR_CODE']
        agg_data[key]['ASSESSMENT_AREA_CODE'] = event['ASSESSMENT_AREA_CODE']
        agg_data[key]['RESOURCE_CATEGORY_CODE'] = event['RESOURCE_CATEGORY_CODE']
        agg_data[key]['SPECIAL_PROJECT_CODE'] = event['SPECIAL_PROJECT_CODE']
        agg_data[key]['OSD_PROGRAM_ELEMENT_CODE'] = event['OSD_PROGRAM_ELEMENT_CODE']
        agg_data[key]['EVENT_NAME'] = event['EVENT_NAME']
        # agg_data[key]["AD_CONSENSUS"] = ad_consensus_dict[event['EVENT_NAME']]
    
    
    for key in agg_data:
        event_name = agg_data[key]['EVENT_NAME']
        # breakpoint()
        if (event_name in ad_consensus_dict) and (ad_consensus_dict[event_name] == 'Disapprove'):
            agg_data[key]["FYDP"] = 0
            for year in all_years:
                agg_data[key][f"FY{year}"] = 0

            del agg_data[key]["FISCAL_YEAR"]
        elif key in manual_funding_key_map: #approve at scale
            agg_data[key] = manual_funding_key_map[key]
            for year in all_years:
                agg_data[key][f"FY{year}"] = agg_data[key].pop(year)
        else:
            agg_data[key]["FYDP"] = 0
            for year in all_years:
                agg_data[key][f"FY{year}"] = agg_data[key]["FISCAL_YEAR"].pop(year)
                agg_data[key]["FYDP"] += agg_data[key][f"FY{year}"]
            del agg_data[key]["FISCAL_YEAR"]

    return {"events":list(agg_data.values()),
            "all_years":all_years}
    
async def get_zbt_event_summary_list_view(event_names: List[str], db_conn):
    table_name = ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractModel, table_name=table_name)
    
    # breakpoint()
    active_pom_id = UsrLookupPOMPosition.get_active_pom_id(db_conn)
 
     # Use asyncio.gather to run both async classmethods concurrently
    events = await orm_model.get_event_summary_list(event_names,db_conn)
    all_years = await orm_model.get_distinct_fiscal_years(db_conn)
    event_titles = await orm_model.get_event_title_from_name(event_names,db_conn)
    event_just = await orm_model.get_event_justification_from_name(event_names,db_conn)
    ad_consensus = await UsrZbtADFinalSaves.get_ad_recommendations(event_names, active_pom_id, db_conn)  
    funding_lines = await UsrEventFundingLines.get_event_funding_lines(active_pom_id, event_names, db_conn)  
    
    if not events:
        raise HTTPException(404, f"No funding ZBT found for events: {', '.join(event_names)}")

    
    funding_lines_map = {
        funding["EVENT_NAME"]: {
            year: funding[f"FY_{idx + 1}"] for idx, year in enumerate(all_years)
        }
        for funding in funding_lines
    }
    
    distinct_years = await orm_model.get_distinct_fiscal_years(db_conn)
    agg_data = defaultdict(dict)
    ad_consensus_dict = {item["EVENT_NAME"]: item["AD_RECOMENDATION"] for item in ad_consensus}
    
    for event in events:
        
        key = (
            event["EVENT_NAME"]
            # event['PROGRAM_GROUP'],
            # event['PROGRAM_CODE'],
            # event['EOC_CODE'],
            # event['CAPABILITY_SPONSOR_CODE'],
            # event['ASSESSMENT_AREA_CODE'],
            # event['RESOURCE_CATEGORY_CODE'],
            # event['SPECIAL_PROJECT_CODE'],
            # event['OSD_PROGRAM_ELEMENT_CODE']
        )
        event_name = event["EVENT_NAME"]
        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']

        if 'FISCAL_YEAR' not in agg_data[event_name]:
            agg_data[event_name]['FISCAL_YEAR'] = {year: 0 for year in all_years}
        
        if 'CAPABILITY_SPONSOR_CODE' not in agg_data[event_name]:
            agg_data[event_name]['CAPABILITY_SPONSOR_CODE'] = set()
        
        agg_data[event_name]['FISCAL_YEAR'][year] += delta_amt

        agg_data[event_name].update({
            "EVENT_NAME": event['EVENT_NAME'],
            "PROGRAM_GROUP": event['PROGRAM_GROUP'],
            "PROGRAM_CODE": event['PROGRAM_CODE'],
            "EOC_CODE": event['EOC_CODE'],
            "CAPABILITY_SPONSOR_CODE": {event['CAPABILITY_SPONSOR_CODE']},
            "ASSESSMENT_AREA_CODE": event['ASSESSMENT_AREA_CODE'],
            "RESOURCE_CATEGORY_CODE": event['RESOURCE_CATEGORY_CODE'],
            "SPECIAL_PROJECT_CODE": event['SPECIAL_PROJECT_CODE'],
            "OSD_PROGRAM_ELEMENT_CODE": event['OSD_PROGRAM_ELEMENT_CODE'],
            "AD_CONSENSUS": ad_consensus_dict.get(event_name, "Not Decided")
        })
    
    for event_name in agg_data:
        if agg_data[event_name].get('AD_CONSENSUS') == "Approve at scale":
            agg_data[event_name]['AD_CONSENSUS'] = "Approve at Scale"

    
    for event_name, recommendation in ad_consensus_dict.items():
        if event_name in agg_data:
            fiscal_years = agg_data[event_name]["FISCAL_YEAR"]

            if recommendation == "Disapprove":
                for year in fiscal_years:
                    fiscal_years[year] = 0

            elif recommendation == "Approve at Scale":
                if event_name in funding_lines_map:
                    fiscal_years.update(funding_lines_map[event_name])

    from sqlalchemy import select, union_all, func, distinct, literal_column
    cte = (
        union_all(
            select(UsrZbtAOSaves.EVENT_ID, UsrZbtAOSaves.AO_RECOMENDATION.label("RECS")).where(
                UsrZbtAOSaves.IS_DELETED == False),
            select(UsrZbtADSaves.EVENT_ID, UsrZbtADSaves.AD_RECOMENDATION.label("RECS")).where(
                UsrZbtADSaves.IS_DELETED == False)
        ).cte("CTE")
    )

    query = (
        select(
            cte.c.EVENT_ID,
            func.group_concat(
                distinct(func.concat(cte.c.RECS))
            ).label("RECS")
        )
        .where(
            cte.c.RECS != '',
            cte.c.RECS.isnot(None),
            cte.c.EVENT_ID.in_(event_names)
        ).group_by(cte.c.EVENT_ID)
    )
    event_rows = db_conn.execute(query)
    #FIND distinct events on DT_ISS_EXTRACT table
    query = select(distinct(orm_model.EVENT_NAME))
    distinct_events = db_conn.execute(query)
    distinct_events = [row[0] for row in distinct_events]

    event_review_dict = {} #default
    event_review_dict = {
        row._mapping["EVENT_ID"]: row._mapping["RECS"]
        for row in event_rows
    }
    for event,review_status in event_review_dict.items():
        if "disapprove" in review_status.lower():
            event_review_dict[event] = 'Disapproval Flag'
        else:
            event_review_dict[event] = "'No Disapproval Flag"

    # Add any missing event IDs with 'Unreviewed'
    event_review_dict.update({
        event_id: "Unreviewed"
        for event_id in event_names
            if event_id not in event_review_dict
    })    
    
    return {
        "events": agg_data,
        "all_years": all_years,
        "event_title": {event_name: event_title for event_name, event_title in event_titles},
        "event_justification": {event_name: event_just for event_name, event_just in event_just},
        "ad_consensus": {
            key: value.replace("Approve at scale", "Approve at Scale") for key, value in ad_consensus_dict.items()
        },
        "event_review_status":event_review_dict
    }
    
async def get_zbt_event_summary_list_export(event_names: List[str], db_conn):
    table_name = ZbtSummaryTableSet.CURRENT["ZBT_EXTRACT"][0]
    orm_model = create_dynamic_table_class(AbstractORMClass=DtZBTExtractModel, table_name=table_name)
    active_pom_id = UsrLookupPOMPosition.get_active_pom_id(db_conn)

    events = orm_model.get_event_summary_list(event_names,db_conn)
    all_years = orm_model.get_distinct_fiscal_years(db_conn)
    event_titles = orm_model.get_event_title_from_name(event_names,db_conn)
    event_just = orm_model.get_event_justification_from_name(event_names,db_conn)
    ad_consensus = UsrZbtADFinalSaves.get_ad_recommendations(event_names, active_pom_id, db_conn)  
    funding_lines = UsrEventFundingLines.get_event_funding_lines(active_pom_id, event_names, db_conn)  

    # Run both tasks concurrently and wait for both to complete
    events,all_years,event_titles,event_just,ad_consensus,funding_lines = await asyncio.gather(
        events, 
        all_years,
        event_titles,
        event_just,
        ad_consensus,
        funding_lines
        )

    if not events:
        raise HTTPException(404, f"No funding ZBT found for events: {', '.join(event_names)}")
    
    manual_funding_key_map = {} #to hold and make the funding lines for each block
    # ("EOC_CODE","EVENT_NAME","PROGRAM_GROUP","ASSESSMENT_AREA_CODE","SPECIAL_PROJECT_CODE",
        # "RESOURCE_CATEGORY_CODE","CAPABILITY_SPONSOR_CODE","OSD_PROGRAM_ELEMENT_CODE")
    
    for row in funding_lines:
        approval_table = row["APPROVAL_TABLE"]
        for block in approval_table:
            key = (
                block["PROGRAM_GROUP"],
                block["EOC_CODE"],
                block["CAPABILITY_SPONSOR_CODE"],
                block["ASSESSMENT_AREA_CODE"],
                block["RESOURCE_CATEGORY_CODE"],
                block["SPECIAL_PROJECT_CODE"],
                block["OSD_PROGRAM_ELEMENT_CODE"],
                block["EVENT_NAME"])
            # breakpoint()
            manual_funding_key_map[key] = block

    
    agg_data = defaultdict(dict)
    ad_consensus_dict = {item["EVENT_NAME"]: item["AD_RECOMENDATION"] for item in ad_consensus}

    for event in events:
        key = (
            event['PROGRAM_GROUP'],
            event['EOC_CODE'],
            event['CAPABILITY_SPONSOR_CODE'],
            event['ASSESSMENT_AREA_CODE'],
            event['RESOURCE_CATEGORY_CODE'],
            event['SPECIAL_PROJECT_CODE'],
            event['OSD_PROGRAM_ELEMENT_CODE'],
            event['EVENT_NAME']
        )
        year = str(event['FISCAL_YEAR'])
        delta_amt = event['DELTA_AMT']
        
        if delta_amt < 0:
            continue  
        
        if 'FISCAL_YEAR' not in agg_data[key]:
            agg_data[key]['FISCAL_YEAR'] = {year: 0 for year in all_years}
        agg_data[key]['FISCAL_YEAR'][year] += delta_amt
        agg_data[key]['PROGRAM_GROUP'] = event['PROGRAM_GROUP']
        agg_data[key]['EOC_CODE'] = event['EOC_CODE']
        agg_data[key]['CAPABILITY_SPONSOR_CODE'] = event['CAPABILITY_SPONSOR_CODE']
        agg_data[key]['ASSESSMENT_AREA_CODE'] = event['ASSESSMENT_AREA_CODE']
        agg_data[key]['RESOURCE_CATEGORY_CODE'] = event['RESOURCE_CATEGORY_CODE']
        agg_data[key]['SPECIAL_PROJECT_CODE'] = event['SPECIAL_PROJECT_CODE']
        agg_data[key]['OSD_PROGRAM_ELEMENT_CODE'] = event['OSD_PROGRAM_ELEMENT_CODE']
        agg_data[key]['EVENT_NAME'] = event['EVENT_NAME']
        # agg_data[key]["AD_CONSENSUS"] = ad_consensus_dict[event['EVENT_NAME']]
    
    
    for key in agg_data:
            event_name = agg_data[key]['EVENT_NAME']
            # breakpoint()
            if (event_name in ad_consensus_dict) and (ad_consensus_dict[event_name] == 'Disapprove'):
                agg_data[key]["FYDP"] = 0
                for year in all_years:
                    agg_data[key][f"FY{year}"] = 0

                del agg_data[key]["FISCAL_YEAR"]
            elif key in manual_funding_key_map: #approve at scale
                agg_data[key] = manual_funding_key_map[key]
                for year in all_years:
                    agg_data[key][f"FY{year}"] = agg_data[key].pop(year)
            else:
                agg_data[key]["FYDP"] = 0
                for year in all_years:
                    agg_data[key][f"FY{year}"] = agg_data[key]["FISCAL_YEAR"].pop(year)
                    agg_data[key]["FYDP"] += agg_data[key][f"FY{year}"]
                del agg_data[key]["FISCAL_YEAR"]

    return {"events":list(agg_data.values()),
            "all_years":all_years}