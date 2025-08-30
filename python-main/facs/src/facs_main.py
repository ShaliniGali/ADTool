# main.py
from fastapi import FastAPI
import sys
from facs.src.documentation import api_description
from facs.src.facs_router import facs_router
# from api.router import optimizer

app = FastAPI(
    title="FACS - Feature Access Control Service",
    description=api_description,
    version="0.0.1",
    contact={
        "name": "Rhombus Power IT Department",
        "url": "https://rhombuspower.com/",
        "email": "it@rhombuspower.com",
    },
    license_info={
        "name": "Apache 2.0",
        "url": "https://www.apache.org/licenses/LICENSE-2.0.html",
    })

app.include_router(facs_router)
# app.include_router(optimizer.router)