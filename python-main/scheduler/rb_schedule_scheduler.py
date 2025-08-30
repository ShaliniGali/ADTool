import sys
import os
import schedule
import rb_schedule_lib as rsl  
from rb_schedule_jobs import (
    job_dt_base_process,
    job_prog_score_process,
    job_dt_out_of_pom_process,
    job_dt_extract_process,
)

config_env = rsl.get_config()

def configure_scheduler():
    if config_env['SOCOM_RUN_PROGRAM_SCORE_UPLOAD'] == 'TRUE':
        schedule.every(1).minutes.do(job_prog_score_process)
    if config_env['SOCOM_RUN_DT_UPLOAD_BASE_UPLOAD'] == 'TRUE':
        schedule.every(5).minutes.do(job_dt_base_process)
    if config_env['SOCOM_RUN_DT_UPLOAD_OUT_OF_POM'] == 'TRUE':
        schedule.every(3).minutes.do(job_dt_out_of_pom_process)
    if config_env['SOCOM_RUN_DT_UPLOAD_EXTRACT'] == 'TRUE':
        schedule.every(2).minutes.do(job_dt_extract_process)
    return schedule
