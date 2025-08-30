
import os, sys

from fastapi import FastAPI, Depends
from dotenv import load_dotenv

from contextlib import asynccontextmanager

res = load_dotenv('/.env', override=True)
if not res:
    print("not found /.env in root directory, trying to read ./.env")
    load_dotenv('./.env',  override=True)

from api.internal.resources import (
    SharedResources, 
    query_pom_position_decrement_map, 
    get_all_dt_tables,
    set_pom_position,
    ZbtSummaryTableSet,
    IssSummaryTableSet,
    ResourceConstraintCOATableSet,
)
from socom.metadata import get_dt_tables_from_pom_position


from api.router import (
    optimizer,
    socom,
    avscan,
    stream,
    auth,
)

from api.models import PomPositionInput

# sys.path.append('facs/src')
# sys.path.append('application/python/facs/src')
# from facs.src.facs_router import facs_router


@asynccontextmanager
async def lifespan(app: FastAPI):
    query_pom_position_decrement_map()
    get_all_dt_tables()
    print(SharedResources.DT_TABLE_SET)
    print(SharedResources.DT_TABLE_DECR_MAP)
    active_pom_year,active_pom_position = set_pom_position()
    _ = await get_dt_tables_from_pom_position(model=PomPositionInput(position=active_pom_position,
                                                                year=active_pom_year),
                                            set_position=True)
    print(ZbtSummaryTableSet.CURRENT)
    print(IssSummaryTableSet.CURRENT)
    print(ResourceConstraintCOATableSet.CURRENT)
    print(ZbtSummaryTableSet.HISTORICAL_POM)
    print(IssSummaryTableSet.HISTORICAL_POM)
    # breakpoint()
    # print(ZbtSummaryTableSet)
    # print(IssSummaryTableSet)
    # print(ResourceConstraintCOATableSet)

    yield


app = FastAPI(lifespan=lifespan)
# app.include_router(facs_router)
app.include_router(optimizer.router)
app.include_router(socom.router)
app.include_router(stream.router)
app.include_router(avscan.router)
app.include_router(auth.router)

if os.environ.get('USE_SCHED') == 'TRUE':
    sys.path.append('scheduler')
    from rb_schedule import run_scheduler
    stop_sched = run_scheduler()