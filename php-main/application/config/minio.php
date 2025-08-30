<?php
defined('BASEPATH') || exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| MinIO Configuration
|--------------------------------------------------------------------------
| Configuration for MinIO S3-compatible object storage
|
*/

// MinIO Server Configuration
$config['minio_endpoint'] = getenv('MINIO_ENDPOINT') ?: 'http://minio:9000';
$config['minio_access_key'] = getenv('MINIO_ACCESS_KEY') ?: 'minioadmin';
$config['minio_secret_key'] = getenv('MINIO_SECRET_KEY') ?: 'minioadmin123';
$config['minio_region'] = getenv('MINIO_REGION') ?: 'us-east-1';
$config['minio_use_ssl'] = getenv('MINIO_USE_SSL') ?: false;
$config['minio_verify_ssl'] = getenv('MINIO_VERIFY_SSL') ?: false;

// Bucket Configuration
$config['minio_buckets'] = [
    'documents' => 'rhombus-documents',
    'uploads' => 'rhombus-uploads',
    'backups' => 'rhombus-backups'
];

// Default bucket for document uploads
$config['minio_default_bucket'] = 'rhombus-documents';

// File upload settings
$config['minio_max_file_size'] = 20971520; // 20MB
$config['minio_allowed_extensions'] = ['xlsx', 'xls', 'csv', 'pdf', 'doc', 'docx'];

// Path configuration
$config['minio_base_path'] = 'SOCOM/documents/';
$config['minio_temp_path'] = APPPATH . 'secure_uploads/temp/';

// Cache settings
$config['minio_cache_enabled'] = true;
$config['minio_cache_ttl'] = 3600; // 1 hour

// Logging
$config['minio_log_enabled'] = true;
$config['minio_log_level'] = 'info'; // debug, info, warning, error
