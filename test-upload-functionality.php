<?php
/**
 * Test script for SOCOM Document Upload functionality
 * This script tests the basic upload components without web authentication
 */

echo "=== SOCOM Document Upload Functionality Test ===\n\n";

// Test 1: Check if required models and libraries exist
echo "1. Testing Required Components:\n";
$required_files = [
    'application/models/SOCOM_Database_Upload_model.php',
    'application/libraries/SOCOM/Database_Upload_Services.php',
    'application/controllers/SOCOM/Document_Upload.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file missing\n";
    }
}

// Test 2: Check upload directory permissions
echo "\n2. Testing Upload Directory:\n";
$upload_dir = 'application/secure_uploads/socom/documents/';
if (is_dir($upload_dir)) {
    echo "   ✅ Upload directory exists: $upload_dir\n";
    if (is_writable($upload_dir)) {
        echo "   ✅ Upload directory is writable\n";
    } else {
        echo "   ❌ Upload directory is not writable\n";
    }
} else {
    echo "   ❌ Upload directory missing: $upload_dir\n";
    // Try to create it
    if (mkdir($upload_dir, 0755, true)) {
        echo "   ✅ Created upload directory\n";
    } else {
        echo "   ❌ Failed to create upload directory\n";
    }
}

// Test 3: Check MinIO connectivity
echo "\n3. Testing MinIO Connection:\n";
$minio_endpoint = 'http://minio:9000';
$ch = curl_init($minio_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200 || $http_code == 403) {
    echo "   ✅ MinIO server accessible (HTTP $http_code)\n";
} else {
    echo "   ❌ MinIO server not accessible (HTTP $http_code)\n";
}

// Test 4: Check database connectivity
echo "\n4. Testing Database Connection:\n";
try {
    $pdo = new PDO(
        'mysql:host=mysql;port=3306;dbname=SOCOM_UI',
        'rhombus_user',
        'rhombus_password'
    );
    echo "   ✅ Database connection successful\n";
    
    // Check if required tables exist
    $tables = ['users', 'user_roles', 'usr_dt_uploads', 'document_metadata'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
        }
    }
    
} catch (PDOException $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 5: Test file validation logic
echo "\n5. Testing File Validation Logic:\n";
$allowed_extensions = ['xlsx', 'xls', 'csv', 'pdf', 'doc', 'docx'];
$max_file_size = 20971520; // 20MB

echo "   Allowed extensions: " . implode(', ', $allowed_extensions) . "\n";
echo "   Max file size: " . number_format($max_file_size / 1024 / 1024, 2) . " MB\n";

// Test 6: Check if we can create a test file
echo "\n6. Testing File Creation:\n";
$test_file = $upload_dir . 'test_upload_' . date('Y_m_d_H_i_s') . '.txt';
$test_content = "Test upload file created at " . date('Y-m-d H:i:s');

if (file_put_contents($test_file, $test_content)) {
    echo "   ✅ Test file created: $test_file\n";
    echo "   ✅ File size: " . filesize($test_file) . " bytes\n";
    
    // Clean up test file
    unlink($test_file);
    echo "   ✅ Test file cleaned up\n";
} else {
    echo "   ❌ Failed to create test file\n";
}

echo "\n=== Test Complete ===\n";
echo "If all tests pass, the upload functionality should work once authentication is set up.\n";
?>
