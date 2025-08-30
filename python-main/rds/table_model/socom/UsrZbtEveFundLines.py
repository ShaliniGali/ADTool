from rds.table_model.base_model import (
    SOCOMBase,
    SCHEMA,
)
from typing import List
from sqlalchemy import (
    Column,
    Integer,
    String,
    JSON,
    DateTime,
    text
)
from sqlalchemy.orm import Mapped

class UsrZbtEveFundLines(SOCOMBase):
    __tablename__ = 'USR_ZBT_EVENT_FUNDING_LINES'

    __table_args__ = {
        'schema': SCHEMA
    }

    ID: Mapped[int] = Column('ID', Integer, primary_key=True, autoincrement=True)
    EVENT_NAME: Mapped[str] = Column('EVENT_NAME', String(100))
    CYCLE_ID: Mapped[int] = Column('CYCLE_ID', Integer)
    CRITERIA_NAME_ID: Mapped[int] = Column('CRITERIA_NAME_ID', Integer)
    POM_ID: Mapped[int] = Column('POM_ID', Integer)
    POM_POSITION: Mapped[str] = Column('POM_POSITION', String(30))
    FY_1: Mapped[int] = Column('FY_1', Integer)
    FY_2: Mapped[int] = Column('FY_2', Integer)
    FY_3: Mapped[int] = Column('FY_3', Integer)
    FY_4: Mapped[int] = Column('FY_4', Integer)
    FY_5: Mapped[int] = Column('FY_5', Integer)
    APPROVE_TABLE: Mapped[dict] = Column('APPROVE_TABLE', JSON)
    YEAR_LIST: Mapped[list] = Column('YEAR_LIST', JSON)
    USER_ID: Mapped[int] = Column('USER_ID', Integer)
    UPDATE_USER_ID: Mapped[int] = Column('UPDATE_USER_ID', Integer)
    CREATED_DATETIME: Mapped[DateTime] = Column('CREATED_DATETIME', DateTime)
    UPDATED_DATETIME: Mapped[DateTime] = Column('UPDATED_DATETIME', DateTime)
    APP_VERSION: Mapped[str] = Column('APP_VERSION', String(45))

    @classmethod
    def get_fydp_sum_for_approve_at_scale(cls, pom_id: int, event_names: List[str], db_conn):
        if not event_names:
            return []

        placeholders = ", ".join([f":event_name_{i}" for i in range(len(event_names))])
        sql = text(f"""
            SELECT EVENT_NAME, FY_1, FY_2, FY_3, FY_4, FY_5
            FROM {SCHEMA}.USR_ZBT_EVENT_FUNDING_LINES
            WHERE POM_ID = :pom_id AND EVENT_NAME IN ({placeholders})
        """)

        params = {f"event_name_{i}": name for i, name in enumerate(event_names)}
        params["pom_id"] = pom_id

        return db_conn.execute(sql, params).fetchall()