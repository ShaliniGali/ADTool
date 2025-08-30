
import sys
import os
from pathlib import Path
import subprocess
import logging
from dotenv import load_dotenv
import time                                                                                                                                                                                                                                                                                                
import traceback

pipelines_path = Path(os.path.abspath(__file__)).parent
python_dir_path = pipelines_path.parent
sys.path.append(f'{python_dir_path.resolve()}/')
sys.path.append(f'{pipelines_path.resolve()}/')

datetime_format = '%Y-%m-%d %H:%M:%S'

def get_config(env_path='/.env'):
    load_dotenv(env_path, override=True)
    
    config_env = {}

    for k, v in os.environ.items():
        environ_val = v
        if environ_val is not None and len(environ_val):
            config_env[k] = environ_val
    
    return config_env

def get_logger():
    logging.basicConfig(stream=sys.stdout, format='[%(asctime)s] %(levelname)s - %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
    logger = logging.getLogger('schedule_logger')
    logger.setLevel(logging.DEBUG)
    return logger                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  

def run_proc(proc, cli_env, schedule_logger):
    try:
        start_timestamp = time.time()
        schedule_logger.info(f'Running job "{proc[1]}" at {time.strftime("%Y-%m-%d %H:%M:%S")}')
        logging.getLogger().handlers[0].flush()
        complete = subprocess.run(
            proc, 
            check=True, 
            env=cli_env, 
            stdout=subprocess.PIPE, 
            stderr=subprocess.PIPE
        )
        
        duration = time.time() - start_timestamp
        schedule_logger.info(f'Job "{proc[1]}" completed in {duration:.2f} seconds')

        complete.check_returncode()
        returncode = complete.returncode
        
        schedule_logger.info(f'SUCCESS PROCESS: {proc[1]}')
        schedule_logger.info(f'SUCCESS RETURN: {returncode}')
        schedule_logger.info(f'SUCCESS STDOUT OUTPUT: {complete.stdout.decode("utf-8")}')
        schedule_logger.info(f'SUCCESS STDERR OUTPUT: {complete.stderr.decode("utf-8")}')
        
    
    except subprocess.CalledProcessError as e:
        returncode = e.returncode
        schedule_logger.error(f'ERROR PROCESS: {proc[1]}')
        schedule_logger.error(f'ERROR RETURN: {e.returncode}')
        schedule_logger.error(f'ERROR STDOUT OUTPUT: {e.stdout.decode("utf-8")}')
        schedule_logger.error(f'ERROR STDERR OUTPUT: {e.stderr.decode("utf-8")}')
        schedule_logger.error(f'ERROR TRACEBACK: {traceback.format_exc()}')  
    
    except FileNotFoundError as e:
        returncode = -1
        schedule_logger.error(f'ERROR PROCESS: {proc[1]}')
        schedule_logger.error(f'ERROR OUTPUT: {e.strerror}')
        schedule_logger.error(f'ERROR TRACEBACK: {traceback.format_exc()}')  
    
    except Exception as e:
        returncode = -1
        schedule_logger.error(f'UNEXPECTED ERROR IN PROCESS: {proc[1]}')
        schedule_logger.error(f'ERROR DETAILS: {str(e)}')
        schedule_logger.error(f'ERROR TRACEBACK: {traceback.format_exc()}')  

    logging.getLogger().handlers[0].flush()
    
    return returncode
