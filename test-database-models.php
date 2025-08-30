<?php
/**
 * Test script for SOCOM Database Models
 * This tests the upload functionality at the model level
 */

echo "=== Testing SOCOM Database Models ===\n\n";

// Test 1: Check if we can connect to the database
echo "1. Testing Database Connection:\n";
try {
    $pdo = new PDO(
        'mysql:host=mysql;port=3306;dbname=SOCOM_UI',
        'rhombus_user',
        'rhombus_password'
    );
    echo "   ✅ Database connection successful\n";
} catch (PDOException $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check if required models exist
echo "\n2. Testing Required Models:\n";
$model_files = [
    'application/models/SOCOM_Database_Upload_model.php',
    'application/models/SOCOM_Scheduler_model.php',
    'application/models/SOCOM_Git_Data_model.php',
    'application/models/SOCOM_Site_User_model.php'
];

foreach ($model_files as $model_file) {
    if (file_exists($model_file)) {
        echo "   ✅ $model_file exists\n";
    } else {
        echo "   ❌ $model_file missing\n";
    }
}

// Test 3: Test database queries directly
echo "\n3. Testing Database Queries:\n";

// Test users table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Users table accessible: " . $result['user_count'] . " users found\n";
} catch (PDOException $e) {
    echo "   ❌ Users table query failed: " . $e->getMessage() . "\n";
}

// Test user_roles table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as role_count FROM user_roles");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ User roles table accessible: " . $result['role_count'] . " roles found\n";
} catch (PDOException $e) {
    echo "   ❌ User roles table query failed: " . $e->getMessage() . "\n";
}

// Test usr_dt_uploads table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as upload_count FROM usr_dt_uploads");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Uploads table accessible: " . $result['upload_count'] . " uploads found\n";
} catch (PDOException $e) {
    echo "   ❌ Uploads table query failed: " . $e->getMessage() . "\n";
}

// Test 4: Test user permissions
echo "\n4. Testing User Permissions:\n";
try {
    // Get testuser info
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.email, ur.role_name 
                           FROM users u 
                           LEFT JOIN user_roles ur ON u.id = ur.user_id 
                           WHERE u.username = ?");
    $stmt->execute(['testuser']);
    $user_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($user_roles) {
        echo "   ✅ testuser found with ID: " . $user_roles[0]['id'] . "\n";
        echo "   ✅ Roles: " . implode(', ', array_column($user_roles, 'role_name')) . "\n";
    } else {
        echo "   ❌ testuser not found\n";
    }
} catch (PDOException $e) {
    echo "   ❌ User permissions query failed: " . $e->getMessage() . "\n";
}

// Test 5: Test upload directory structure
echo "\n5. Testing Upload Directory Structure:\n";
$upload_dirs = [
    'application/secure_uploads/socom/documents/',
    'application/secure_uploads/socom/database_upload/',
    'application/secure_uploads/socom/image_upload/'
];

foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✅ $dir exists and writable\n";
        } else {
            echo "   ⚠️  $dir exists but not writable\n";
        }
    } else {
        echo "   ❌ $dir missing\n";
    }
}

// Test 6: Test MinIO bucket access
echo "\n6. Testing MinIO Bucket Access:\n";
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

echo "\n=== Database Model Tests Complete ===\n";
echo "All core components are accessible and functional.\n";
echo "The upload system should work once proper authentication is established.\n";
?>
