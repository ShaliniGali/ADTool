#!/bin/bash

echo "=========================================="
echo "Setting up Rhombus Project in Docker"
echo "=========================================="

# Set environment variables
export MYSQL_ROOT_PASSWORD=rhombus_root_password
export MYSQL_DATABASE=rhombus_db
export MYSQL_USER=rhombus_user
export MYSQL_PASSWORD=rhombus_password
export MYSQL_HOST=mysql
export MYSQL_PORT=3306

echo "Environment variables set..."

# Create necessary directories
echo "Creating necessary directories..."
mkdir -p nginx/ssl
mkdir -p logs

# Build and start the services
echo "Building and starting Docker services..."
docker-compose up --build -d

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 30

# Wait for MinIO to be ready and set it up
echo "Setting up MinIO storage..."
sleep 20
./minio-setup.sh

# Check if all services are running
echo "Checking service status..."
docker-compose ps

echo "=========================================="
echo "Rhombus Project Setup Complete!"
echo "=========================================="
echo ""
echo "Services available at:"
echo "- Main Application: http://localhost"
echo "- Python API: http://localhost:8020"
echo "- JavaScript App: http://localhost:3000"
echo "- phpMyAdmin: http://localhost:8080"
echo "- MySQL: localhost:3306"
echo "- Redis: localhost:6379"
echo "- MinIO Console: http://localhost:9001"
echo "- MinIO API: localhost:9000"
echo ""
echo "To view logs: docker-compose logs -f"
echo "To stop services: docker-compose down"
echo "To restart: docker-compose restart"
