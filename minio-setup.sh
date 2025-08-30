#!/bin/bash

echo "=========================================="
echo "Setting up MinIO for Rhombus Project"
echo "=========================================="

# Wait for MinIO to be ready
echo "Waiting for MinIO to be ready..."
sleep 10

# Install MinIO client if not already installed
if ! command -v mc &> /dev/null; then
    echo "Installing MinIO client..."
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        brew install minio/stable/mc
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # Linux
        wget https://dl.min.io/client/mc/release/linux-amd64/mc
        chmod +x mc
        sudo mv mc /usr/local/bin/
    else
        echo "Please install MinIO client manually for your OS"
        exit 1
    fi
fi

# Configure MinIO client
echo "Configuring MinIO client..."
mc alias set local http://localhost:9000 minioadmin minioadmin123

# Create buckets
echo "Creating storage buckets..."
mc mb local/rhombus-documents --ignore-existing
mc mb local/rhombus-uploads --ignore-existing
mc mb local/rhombus-backups --ignore-existing

# Set bucket policies
echo "Setting bucket policies..."
mc policy set public local/rhombus-documents
mc policy set public local/rhombus-uploads
mc policy set public local/rhombus-backups

# Create initial folder structure
echo "Creating folder structure..."
mc cp --recursive --ignore-existing ./php-main/secure_uploads/ local/rhombus-documents/

echo "=========================================="
echo "MinIO Setup Complete!"
echo "=========================================="
echo ""
echo "MinIO Console: http://localhost:9001"
echo "Username: minioadmin"
echo "Password: minioadmin123"
echo ""
echo "Buckets created:"
echo "- rhombus-documents (for document storage)"
echo "- rhombus-uploads (for file uploads)"
echo "- rhombus-backups (for backups)"
echo ""
echo "Access via S3-compatible clients:"
echo "- Endpoint: http://localhost:9000"
echo "- Access Key: minioadmin"
echo "- Secret Key: minioadmin123"
