from datetime import datetime
from sqlalchemy import text
import json


def write_log(e):
    print(datetime.today().strftime('%Y%m%d') + ":\t" + str(e) + "\n\n")


class ErrorLogger:
    def __init__(self):
        self.msg = [] #list of messages/errors to upload to the db
    
    def log_scheduler_error(self, session, scheduler_id: int,schema:str):
        
        """
        Logs an error message to the USR_DT_SCHEDULER.ERRORS column for a specific scheduler ID.
        
        Parameters:
        - session: SQLAlchemy session
        - scheduler_id: ID of USR_DT_SCHEDULER row
        """
        error_msg = json.dumps(self.msg)
        update_query = f"""
        UPDATE {schema}.USR_DT_SCHEDULER
        SET ERRORS = :error_msg
        WHERE ID = :scheduler_id AND TYPE = 'PROGRAM_SCORE_UPLOAD';
        """

        session.execute(text(update_query), {
            'error_msg': error_msg,
            'scheduler_id': scheduler_id
        })
        session.commit()

    def add_error_log(self,msg:str):
        self.msg.append(msg)