# 🚀 Rhombus Multi-Service SOCOM Application

A comprehensive multi-service application for SOCOM (Special Operations Command) operations, featuring PHP (CodeIgniter), Python (FastAPI), JavaScript (React/Node.js), MySQL, MinIO, and Redis.

## 🏗️ Architecture Overview

This application consists of multiple microservices working together:

- **PHP Application**: CodeIgniter-based SOCOM dashboard and document management
- **Python API**: FastAPI service with SOCOM-specific endpoints and JWT/SSO
- **JavaScript S3 Service**: Node.js service for S3 file operations and React UI
- **MinIO**: S3-compatible object storage for documents
- **MySQL**: Primary database for SOCOM application data
- **Redis**: In-memory caching and session management
- **Nginx**: Reverse proxy and load balancer

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd Python-PHP-2
```

### 2. Start All Services
```bash
docker-compose up -d
```

### 3. Access the Application
- **Main Dashboard**: http://localhost/working-services.html
- **Python API**: http://localhost:8020/docs
- **JavaScript S3 Service**: http://localhost:3000
- **MinIO Console**: http://localhost:9001
- **Database**: http://localhost:8080 (phpMyAdmin)

## 📊 Services Status

### ✅ Working Services
- **Python API**: FastAPI with Swagger UI
- **JavaScript S3 Service**: File operations and React UI
- **MinIO Storage**: S3-compatible storage
- **MySQL Database**: SOCOM data with comprehensive tables

### ⚠️ Services Needing Attention
- **PHP Application**: CodeIgniter setup in progress
- **React UI**: SOCOM dashboard development

## 🗄️ Database Schema

### Core SOCOM Tables
- **ZBT_SUMMARY_2024**: Zero-Based Thinking data
- **ISS_SUMMARY_2024**: Issue Summary Sheet data
- **RESOURCE_CONSTRAINED_COA_2024**: Course of Action data
- **POM_SUMMARY_2024**: Program Objective Memorandum data
- **LOOKUP_PROGRAM**: Program lookup tables
- **USR_LOOKUP_POM_POSITION**: Position management

### Supporting Tables
- **document_metadata**: Document tracking and metadata
- **processing_pipeline**: Data processing workflows
- **git_tracking**: Version control tracking
- **pipeline_mapping**: Pipeline configurations
- **user_roles**: Comprehensive role management

## 🔐 Authentication & Roles

### User Roles
- **SOCOM_ADMIN**: Full administrative access
- **SOCOM_ANALYST**: Data analysis and export
- **SOCOM_MANAGER**: Project and team management
- **ZBT_MANAGER**: Zero-Based Thinking management
- **ISS_MANAGER**: Issue Summary management
- **COA_MANAGER**: Course of Action management
- **POM_MANAGER**: Program Objective management

## 📁 File Structure

```
├── docker-compose.yml          # Service orchestration
├── nginx/                      # Nginx configuration
├── php-main/                   # PHP CodeIgniter application
│   ├── application/           # CodeIgniter app files
│   ├── working-services.html  # Main dashboard
│   └── api-documentation.html # API documentation
├── python-main/                # Python FastAPI service
├── javascript-main/            # Node.js S3 service
├── setup-rhombus.sh           # Setup script
└── README.md                  # This file
```

## 🛠️ Development

### Adding New Services
1. Create service directory
2. Add Dockerfile
3. Update docker-compose.yml
4. Update nginx configuration
5. Test and document

### Database Changes
1. Create SQL migration files
2. Update schema documentation
3. Test with sample data
4. Update dashboard tables

## 🔧 Troubleshooting

### Common Issues
- **Port conflicts**: Check if ports 80, 3000, 8020, 9000, 9001 are available
- **Database connection**: Verify MySQL container is running
- **File permissions**: Ensure Docker volumes have correct permissions

### Logs
```bash
# View all service logs
docker-compose logs

# View specific service logs
docker-compose logs php-app
docker-compose logs python-app
docker-compose logs javascript-app
```

## 📝 API Documentation

### S3 Service Endpoints
- `GET /health` - Service health check
- `GET /files` - List files in bucket
- `POST /upload` - Upload file
- `GET /download/{filename}` - Download file
- `DELETE /delete/{filename}` - Delete file

### Python API Endpoints
- `GET /docs` - Swagger UI documentation
- `GET /health` - Health check
- `POST /auth/login` - JWT authentication
- `GET /socom/cycles` - SOCOM cycles data

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary and confidential. All rights reserved.

## 🆘 Support

For technical support or questions about the SOCOM application, contact the development team.

---

**Last Updated**: August 30, 2025  
**Version**: 1.0.0  
**Status**: Development/Testing
