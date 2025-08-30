from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

app = FastAPI(
    title="Rhombus Python API", 
    version="1.0.0",
    description="Rhombus Multi-Service Application Python API with SOCOM endpoints",
    docs_url="/docs",
    redoc_url="/redoc"
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
async def root():
    return {"message": "Rhombus Python API is running!"}

@app.get("/health")
async def health_check():
    return {"status": "healthy", "service": "python-api"}

@app.get("/api/test")
async def test_endpoint():
    return {"message": "This is a test endpoint", "data": {"timestamp": "2024-01-01"}}

# SOCOM API Endpoints
@app.get("/socom/health")
async def socom_health():
    return {"status": "healthy", "service": "socom-api"}

@app.post("/socom/prog_eoc_funding")
async def prog_eoc_funding():
    return {"message": "Program EOC Funding endpoint", "status": "available"}

@app.post("/socom/prog_event_funding")
async def prog_event_funding():
    return {"message": "Program Event Funding endpoint", "status": "available"}

@app.get("/socom/zbt/event_summary/")
async def zbt_event_summary():
    return {"message": "ZBT Event Summary endpoint", "status": "available"}

@app.get("/socom/iss/event_summary/")
async def iss_event_summary():
    return {"message": "ISS Event Summary endpoint", "status": "available"}

@app.post("/socom/metadata/{coa_type}")
async def get_metadata(coa_type: str):
    return {"message": f"Metadata endpoint for {coa_type}", "status": "available"}

# Optimizer API Endpoints
@app.get("/optimizer/health")
async def optimizer_health():
    return {"status": "healthy", "service": "optimizer-api"}

@app.post("/optimizer/calculate_budget")
async def calculate_budget():
    return {"message": "Budget calculation endpoint", "status": "available"}

@app.get("/optimizer/jca_alignment/opt-run")
async def jca_alignment():
    return {"message": "JCA Alignment endpoint", "status": "available"}

@app.get("/optimizer/cga_alignment/opt-run")
async def cga_alignment():
    return {"message": "CGA Alignment endpoint", "status": "available"}

# Auth API Endpoints
@app.get("/auth/health")
async def auth_health():
    return {"status": "healthy", "service": "auth-api"}

@app.post("/auth/jwt")
async def create_jwt():
    return {"message": "JWT creation endpoint", "status": "available"}

@app.post("/auth/jwt/refresh")
async def refresh_jwt():
    return {"message": "JWT refresh endpoint", "status": "available"}

# Stream API Endpoints
@app.get("/stream/health")
async def stream_health():
    return {"status": "healthy", "service": "stream-api"}

@app.get("/stream/dt/notif/messages/{limit}")
async def get_notifications(limit: int):
    return {"message": f"Get notifications endpoint (limit: {limit})", "status": "available"}

@app.post("/stream/dt/notif")
async def create_notification():
    return {"message": "Create notification endpoint", "status": "available"}

# File Management Endpoints
@app.post("/socom/download/scores/excel")
async def download_scores_excel():
    return {"message": "Download scores Excel endpoint", "status": "available"}

@app.post("/socom/dt_table/upload")
async def upload_dt_table():
    return {"message": "Upload DT table endpoint", "status": "available"}

@app.post("/socom/dt_table/upsert")
async def upsert_dt_table():
    return {"message": "Upsert DT table endpoint", "status": "available"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8020)
