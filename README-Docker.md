# Rhombus Project - Docker Setup

This document describes how to set up and run the Rhombus project using Docker containers.

## Overview

The Rhombus project consists of multiple components:
- **PHP Application** (CodeIgniter framework)
- **Python API** (FastAPI)
- **JavaScript/Node.js Application**
- **MySQL Database**
- **Redis Cache**
- **Nginx Reverse Proxy**
- **phpMyAdmin** (Database management)

## Prerequisites

- Docker Desktop installed and running
- Docker Compose installed
- At least 4GB of available RAM
- Ports 80, 3000, 3306, 6379, 8020, 8080 available

## Quick Start

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd Python-PHP\ 2
   ```

2. **Run the setup script**:
   ```bash
   ./setup-rhombus.sh
   ```

   Or manually start the services:
   ```bash
   docker-compose up --build -d
   ```

3. **Wait for services to start** (about 1-2 minutes):
   ```bash
   docker-compose ps
   ```

## Service Details

### Ports and URLs

| Service | Port | URL | Description |
|---------|------|-----|-------------|
| Main Application | 80 | http://localhost | PHP application through Nginx |
| JavaScript App | 3000 | http://localhost:3000 | Direct access to Node.js app |
| Python API | 8020 | http://localhost:8020 | Direct access to FastAPI |
| MySQL Database | 3306 | localhost:3306 | Database connection |
| Redis Cache | 6379 | localhost:6379 | Cache service |
| phpMyAdmin | 8080 | http://localhost:8080 | Database management UI |

### Container Names

- `rhombus-mysql` - MySQL database
- `rhombus-php` - PHP application
- `rhombus-python` - Python API
- `rhombus-javascript` - JavaScript/Node.js application
- `rhombus-nginx` - Nginx reverse proxy
- `rhombus-redis` - Redis cache
- `rhombus-phpmyadmin` - phpMyAdmin interface

## Database Configuration

### Default Credentials

- **Database Host**: `mysql` (internal) or `localhost` (external)
- **Database Port**: `3306`
- **Database Name**: `rhombus_db`
- **Username**: `rhombus_user`
- **Password**: `rhombus_password`
- **Root Password**: `rhombus_root_password`

### Connection Strings

- **PHP**: `mysql://rhombus_user:rhombus_password@mysql:3306/rhombus_db`
- **Python**: `mysql://rhombus_user:rhombus_password@mysql:3306/rhombus_db`
- **External**: `mysql://rhombus_user:rhombus_password@localhost:3306/rhombus_db`

## Management Commands

### View Service Status
```bash
docker-compose ps
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f python-api
docker-compose logs -f php-app
docker-compose logs -f javascript-app
```

### Stop Services
```bash
docker-compose down
```

### Restart Services
```bash
docker-compose restart
```

### Rebuild and Restart
```bash
docker-compose up --build -d
```

### Access Container Shell
```bash
# PHP container
docker exec -it rhombus-php bash

# Python container
docker exec -it rhombus-python bash

# JavaScript container
docker exec -it rhombus-javascript sh
```

## Troubleshooting

### Common Issues

1. **Port Already in Use**
   ```bash
   # Check what's using the port
   lsof -i :80
   lsof -i :3000
   
   # Stop conflicting services
   sudo lsof -ti:80 | xargs kill -9
   ```

2. **Container Won't Start**
   ```bash
   # Check logs
   docker-compose logs <service-name>
   
   # Check container status
   docker ps -a
   ```

3. **Database Connection Issues**
   ```bash
   # Check if MySQL is running
   docker exec rhombus-mysql mysqladmin ping -h localhost -u root -p
   
   # Check MySQL logs
   docker-compose logs mysql
   ```

4. **Permission Issues**
   ```bash
   # Fix file permissions
   sudo chown -R $USER:$USER .
   chmod +x setup-rhombus.sh
   ```

### Reset Everything

If you need to start completely fresh:

```bash
# Stop and remove all containers
docker-compose down -v

# Remove all images
docker system prune -af

# Remove all volumes
docker volume prune -f

# Start fresh
docker-compose up --build -d
```

## Development

### Making Changes

1. **PHP Application**: Changes in `php-main/` are reflected immediately
2. **Python API**: Changes require rebuilding the container
3. **JavaScript App**: Changes require rebuilding the container
4. **Database**: Changes persist in the `mysql_data` volume

### Rebuilding After Changes

```bash
# Rebuild specific service
docker-compose up --build <service-name> -d

# Rebuild all services
docker-compose up --build -d
```

## Security Notes

- Default passwords are used for development only
- Change passwords in production environments
- Consider using Docker secrets for sensitive data
- External access to MySQL is enabled for development

## Performance

- Services are configured with reasonable defaults
- Redis provides caching capabilities
- Nginx handles static file serving and load balancing
- Consider adjusting resource limits for production use

## Support

For issues related to:
- **Docker setup**: Check this README and Docker logs
- **Application code**: Refer to individual component documentation
- **Database**: Check MySQL logs and phpMyAdmin interface

## Next Steps

1. Access the main application at http://localhost
2. Use phpMyAdmin at http://localhost:8080 to manage the database
3. Test the Python API at http://localhost:8020
4. Check the JavaScript app at http://localhost:3000
5. Review logs if any issues arise
