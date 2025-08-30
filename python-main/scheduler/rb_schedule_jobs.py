import sys
import os
from pathlib import Path
import rb_schedule_lib as rsl  

sys.path.append(str(Path(__file__).resolve().parent / 'lib'))

from db_env import get_creds

def job_prog_score_process():
    os.chdir(Path(__file__).parent.parent)  

    PATH_TO_SCRIPT = str(Path(__file__).resolve().parent.parent / 'upload_pipelines' / 'validate_program_scores.py')

    rsl.run_proc(proc=[
        "python3",
        PATH_TO_SCRIPT
    ], cli_env=os.environ, schedule_logger=rsl.get_logger())  



def job_dt_base_process():
    os.chdir(Path(__file__).parent)

    PATH_TO_SCRIPT = str(Path(__file__).resolve().parent.parent / 'upload_pipelines' / 'validate_dt_base.py')
    rsl.run_proc(proc=[
        "python3",
        PATH_TO_SCRIPT,
    ],cli_env=os.environ, schedule_logger=rsl.get_logger())


def job_dt_out_of_pom_process():
    os.chdir(Path(__file__).parent)

    PATH_TO_SCRIPT = str(Path(__file__).resolve().parent.parent / 'upload_pipelines' / 'validate_dt_out_of_pom.py')
    rsl.run_proc(proc=[
        "python3",
        PATH_TO_SCRIPT,
    ],cli_env=os.environ, schedule_logger=rsl.get_logger())


def job_dt_extract_process():
    os.chdir(Path(__file__).parent)

    PATH_TO_SCRIPT = str(Path(__file__).resolve().parent.parent / 'upload_pipelines' / 'validate_dt_extract.py')
    rsl.run_proc(proc=[
        "python3",
        PATH_TO_SCRIPT,
    ],cli_env=os.environ, schedule_logger=rsl.get_logger())