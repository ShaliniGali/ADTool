<?php
// Simple test script to verify document upload functionality
// require_once 'php-main/application/config/minio.php';

echo "=== Rhombus Document Upload Test ===\n\n";

// Test database connection
try {
    $pdo = new PDO(
        'mysql:host=mysql;port=3306;dbname=rhombus_db',
        'rhombus_user',
        'rhombus_password'
    );
    echo "✅ Database connection successful\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM SOCOM_UI.users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database query successful - Users count: " . $result['user_count'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test MinIO connection (if available)
echo "\n=== MinIO Test ===\n";
$minio_endpoint = 'http://minio:9000';
$ch = curl_init($minio_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200 || $http_code == 403) {
    echo "✅ MinIO server accessible (HTTP $http_code)\n";
} else {
    echo "❌ MinIO server not accessible (HTTP $http_code)\n";
}

// Test file upload directory
echo "\n=== File System Test ===\n";
$upload_dir = 'php-main/application/secure_uploads/documents/';
if (is_dir($upload_dir)) {
    echo "✅ Upload directory exists: $upload_dir\n";
} else {
    echo "⚠️  Upload directory missing, creating: $upload_dir\n";
    mkdir($upload_dir, 0755, true);
    if (is_dir($upload_dir)) {
        echo "✅ Upload directory created successfully\n";
    } else {
        echo "❌ Failed to create upload directory\n";
    }
}

// Test writability
if (is_writable($upload_dir)) {
    echo "✅ Upload directory is writable\n";
} else {
    echo "❌ Upload directory is not writable\n";
}

echo "\n=== Test Complete ===\n";
echo "You can now test the document upload functionality at:\n";
echo "- Main app: http://localhost\n";
echo "- Document upload: http://localhost/SOCOM/Document_Upload\n";
echo "- Health dashboard: health-dashboard.html\n";
echo "- MinIO console: http://localhost:9001\n";
echo "- phpMyAdmin: http://localhost:8080\n";
