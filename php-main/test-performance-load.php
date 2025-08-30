<?php
/**
 * Performance and Load Testing Script for SOCOM Rhombus System
 * Tests database performance, file upload performance, API response times, and concurrent operations
 */

echo "=== SOCOM Performance and Load Testing ===\n\n";

// Performance testing configuration
$test_config = [
    'database_iterations' => 100,
    'file_upload_count' => 50,
    'concurrent_users' => 10,
    'api_test_count' => 25
];

echo "📊 Test Configuration:\n";
echo "   - Database operations: {$test_config['database_iterations']} iterations\n";
echo "   - File uploads: {$test_config['file_upload_count']} files\n";
echo "   - Concurrent users: {$test_config['concurrent_users']} users\n";
echo "   - API tests: {$test_config['api_test_count']} requests\n\n";

// Test 1: Database Performance Testing
echo "1. 🗄️  Database Performance Testing:\n";
$db_start_time = microtime(true);

try {
    $pdo = new PDO(
        'mysql:host=mysql;port=3306;dbname=SOCOM_UI',
        'rhombus_user',
        'rhombus_password'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test 1a: Simple SELECT performance
    echo "   a) Simple SELECT Performance:\n";
    $select_start = microtime(true);
    
    for ($i = 0; $i < $test_config['database_iterations']; $i++) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stmt->fetchColumn();
    }
    
    $select_time = microtime(true) - $select_start;
    $select_avg = ($select_time / $test_config['database_iterations']) * 1000;
    echo "      ✅ {$test_config['database_iterations']} SELECT operations in " . number_format($select_time, 4) . "s\n";
    echo "      📊 Average: " . number_format($select_avg, 2) . "ms per operation\n";
    
    // Test 1b: Complex JOIN performance
    echo "   b) Complex JOIN Performance:\n";
    $join_start = microtime(true);
    
    for ($i = 0; $i < 10; $i++) {
        $stmt = $pdo->query("
            SELECT 
                u.USR_DT_UPLOADS_ID,
                u.FILE_NAME,
                u.FILE_STATUS,
                c.CYCLE_NAME,
                usr.username
            FROM usr_dt_uploads u
            LEFT JOIN cycles c ON u.CYCLE_ID = c.id
            LEFT JOIN users usr ON u.USER_ID = usr.id
            LIMIT 100
        ");
        $stmt->fetchAll();
    }
    
    $join_time = microtime(true) - $join_start;
    $join_avg = ($join_time / 10) * 1000;
    echo "      ✅ 10 complex JOIN operations in " . number_format($join_time, 4) . "s\n";
    echo "      📊 Average: " . number_format($join_avg, 2) . "ms per operation\n";
    
    // Test 1c: INSERT performance
    echo "   c) INSERT Performance:\n";
    $insert_start = microtime(true);
    
    for ($i = 0; $i < 20; $i++) {
        $stmt = $pdo->prepare("
            INSERT INTO usr_dt_uploads (TYPE, CYCLE_ID, S3_PATH, FILE_NAME, VERSION, TITLE, DESCRIPTION, USER_ID, FILE_STATUS)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'DOCUMENT',
            2,
            "perf_test_file_{$i}.txt",
            "perf_test_file_{$i}.txt",
            '1.0',
            "Performance Test File {$i}",
            "Performance testing file {$i}",
            2,
            'NEW'
        ]);
    }
    
    $insert_time = microtime(true) - $insert_start;
    $insert_avg = ($insert_time / 20) * 1000;
    echo "      ✅ 20 INSERT operations in " . number_format($insert_time, 4) . "s\n";
    echo "      📊 Average: " . number_format($insert_avg, 2) . "ms per operation\n";
    
    // Test 1d: UPDATE performance
    echo "   d) UPDATE Performance:\n";
    $update_start = microtime(true);
    
    for ($i = 0; $i < 20; $i++) {
        $stmt = $pdo->prepare("
            UPDATE usr_dt_uploads 
            SET FILE_STATUS = 'PROCESSING', UPDATED_TIMESTAMP = CURRENT_TIMESTAMP 
            WHERE FILE_NAME = ?
        ");
        $stmt->execute(["perf_test_file_{$i}.txt"]);
    }
    
    $update_time = microtime(true) - $update_start;
    $update_avg = ($update_time / 20) * 1000;
    echo "      ✅ 20 UPDATE operations in " . number_format($update_time, 4) . "s\n";
    echo "      📊 Average: " . number_format($update_avg, 2) . "ms per operation\n";
    
    // Test 1e: DELETE performance (cleanup)
    echo "   e) DELETE Performance (Cleanup):\n";
    $delete_start = microtime(true);
    
    $stmt = $pdo->prepare("DELETE FROM usr_dt_uploads WHERE FILE_NAME LIKE 'perf_test_file_%'");
    $stmt->execute();
    $deleted_count = $stmt->rowCount();
    
    $delete_time = microtime(true) - $delete_start;
    echo "      ✅ Deleted {$deleted_count} test records in " . number_format($delete_time, 4) . "s\n";
    
    $db_total_time = microtime(true) - $db_start_time;
    echo "   🎯 Total Database Test Time: " . number_format($db_total_time, 4) . "s\n";
    
} catch (PDOException $e) {
    echo "   ❌ Database performance test failed: " . $e->getMessage() . "\n";
}

// Test 2: File System Performance Testing
echo "\n2. 📁 File System Performance Testing:\n";
$fs_start_time = microtime(true);

// Test 2a: File creation performance
echo "   a) File Creation Performance:\n";
$create_start = microtime(true);

$test_files = [];
for ($i = 0; $i < $test_config['file_upload_count']; $i++) {
    $filename = "application/secure_uploads/socom/documents/perf_test_{$i}.txt";
    $content = "Performance test file {$i} created at " . date('Y-m-d H:i:s') . "\n" . str_repeat("Test data line {$i}\n", 10);
    
    if (file_put_contents($filename, $content)) {
        $test_files[] = $filename;
    }
}

$create_time = microtime(true) - $create_start;
$create_avg = ($create_time / count($test_files)) * 1000;
echo "      ✅ Created " . count($test_files) . " files in " . number_format($create_time, 4) . "s\n";
echo "      📊 Average: " . number_format($create_avg, 2) . "ms per file\n";

// Test 2b: File read performance
echo "   b) File Read Performance:\n";
$read_start = microtime(true);

$total_size = 0;
foreach ($test_files as $file) {
    $content = file_get_contents($file);
    $total_size += strlen($content);
}

$read_time = microtime(true) - $read_start;
$read_avg = ($read_time / count($test_files)) * 1000;
echo "      ✅ Read " . count($test_files) . " files (" . number_format($total_size / 1024, 2) . " KB) in " . number_format($read_time, 4) . "s\n";
echo "      📊 Average: " . number_format($read_avg, 2) . "ms per file\n";

// Test 2c: File deletion performance (cleanup)
echo "   c) File Deletion Performance (Cleanup):\n";
$delete_start = microtime(true);

$deleted_count = 0;
foreach ($test_files as $file) {
    if (unlink($file)) {
        $deleted_count++;
    }
}

$delete_time = microtime(true) - $delete_start;
$delete_avg = ($delete_time / $deleted_count) * 1000;
echo "      ✅ Deleted {$deleted_count} files in " . number_format($delete_time, 4) . "s\n";
echo "      📊 Average: " . number_format($delete_avg, 2) . "ms per file\n";

$fs_total_time = microtime(true) - $fs_start_time;
echo "   🎯 Total File System Test Time: " . number_format($fs_total_time, 4) . "s\n";

// Test 3: API Performance Testing
echo "\n3. 🌐 API Performance Testing:\n";
$api_start_time = microtime(true);

// Test 3a: Python API health endpoint
echo "   a) Python API Health Endpoint:\n";
$api_times = [];

for ($i = 0; $i < $test_config['api_test_count']; $i++) {
    $start = microtime(true);
    
    $ch = curl_init('http://rhombus-python:8000/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $api_times[] = (microtime(true) - $start) * 1000;
}

if (!empty($api_times)) {
    $api_avg = array_sum($api_times) / count($api_times);
    $api_min = min($api_times);
    $api_max = max($api_times);
    
    echo "      ✅ {$test_config['api_test_count']} API calls completed\n";
    echo "      📊 Average: " . number_format($api_avg, 2) . "ms\n";
    echo "      📊 Min: " . number_format($api_min, 2) . "ms, Max: " . number_format($api_max, 2) . "ms\n";
} else {
    echo "      ❌ API performance test failed\n";
}

// Test 3b: JavaScript service endpoint
echo "   b) JavaScript Service Endpoint:\n";
$js_times = [];

for ($i = 0; $i < 10; $i++) {
    $start = microtime(true);
    
    $ch = curl_init('http://rhombus-javascript:3000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $js_times[] = (microtime(true) - $start) * 1000;
}

if (!empty($js_times)) {
    $js_avg = array_sum($js_times) / count($js_times);
    echo "      ✅ 10 JavaScript service calls completed\n";
    echo "      📊 Average: " . number_format($js_avg, 2) . "ms\n";
} else {
    echo "      ❌ JavaScript service performance test failed\n";
}

$api_total_time = microtime(true) - $api_start_time;
echo "   🎯 Total API Test Time: " . number_format($api_total_time, 4) . "s\n";

// Test 4: Concurrent Operations Testing
echo "\n4. 🔄 Concurrent Operations Testing:\n";
$concurrent_start_time = microtime(true);

// Test 4a: Simulate concurrent database operations
echo "   a) Concurrent Database Operations:\n";
$concurrent_db_start = microtime(true);

$pids = [];
for ($i = 0; $i < $test_config['concurrent_users']; $i++) {
    $pid = pcntl_fork();
    
    if ($pid == 0) {
        // Child process
        try {
            $pdo = new PDO(
                'mysql:host=mysql;port=3306;dbname=SOCOM_UI',
                'rhombus_user',
                'rhombus_password'
            );
            
            // Simulate user activity
            for ($j = 0; $j < 5; $j++) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $stmt->fetchColumn();
                usleep(10000); // 10ms delay
            }
            
            exit(0);
        } catch (Exception $e) {
            exit(1);
        }
    } else {
        $pids[] = $pid;
    }
}

// Wait for all child processes
foreach ($pids as $pid) {
    pcntl_waitpid($pid, $status);
}

$concurrent_db_time = microtime(true) - $concurrent_db_start;
echo "      ✅ Simulated {$test_config['concurrent_users']} concurrent users in " . number_format($concurrent_db_time, 4) . "s\n";

// Test 4b: Concurrent file operations
echo "   b) Concurrent File Operations:\n";
$concurrent_file_start = microtime(true);

$file_pids = [];
for ($i = 0; $i < 5; $i++) {
    $pid = pcntl_fork();
    
    if ($pid == 0) {
        // Child process
        for ($j = 0; $j < 10; $j++) {
            $filename = "application/secure_uploads/socom/documents/concurrent_test_{$i}_{$j}.txt";
            file_put_contents($filename, "Concurrent test file {$i}_{$j}");
            unlink($filename);
        }
        exit(0);
    } else {
        $file_pids[] = $pid;
    }
}

// Wait for all file operations
foreach ($file_pids as $pid) {
    pcntl_waitpid($pid, $status);
}

$concurrent_file_time = microtime(true) - $concurrent_file_start;
echo "      ✅ 5 concurrent file operation processes completed in " . number_format($concurrent_file_time, 4) . "s\n";

$concurrent_total_time = microtime(true) - $concurrent_start_time;
echo "   🎯 Total Concurrent Test Time: " . number_format($concurrent_total_time, 4) . "s\n";

// Test 5: System Resource Monitoring
echo "\n5. 📊 System Resource Monitoring:\n";

// Test 5a: Memory usage
echo "   a) Memory Usage:\n";
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);
echo "      📊 Current Memory: " . number_format($memory_usage / 1024 / 1024, 2) . " MB\n";
echo "      📊 Peak Memory: " . number_format($memory_peak / 1024 / 1024, 2) . " MB\n";

// Test 5b: Execution time
echo "   b) Execution Time:\n";
$total_execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
echo "      📊 Total Execution Time: " . number_format($total_execution_time, 4) . "s\n";

// Test 5c: Database connection status
echo "   c) Database Connection Status:\n";
try {
    $pdo = new PDO(
        'mysql:host=mysql;port=3306;dbname=SOCOM_UI',
        'rhombus_user',
        'rhombus_password'
    );
    
    $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
    $threads = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "      📊 Active Database Connections: " . $threads['Value'] . "\n";
    
    $stmt = $pdo->query("SHOW STATUS LIKE 'Uptime'");
    $uptime = $stmt->fetch(PDO::FETCH_ASSOC);
    $uptime_seconds = $uptime['Value'];
    $uptime_formatted = gmdate("H:i:s", $uptime_seconds);
    echo "      📊 Database Uptime: {$uptime_formatted}\n";
    
} catch (PDOException $e) {
    echo "      ❌ Database status check failed: " . $e->getMessage() . "\n";
}

// Performance Summary
echo "\n=== Performance Test Summary ===\n";
echo "================================\n";

$total_test_time = microtime(true) - $db_start_time;

echo "🎯 **PERFORMANCE METRICS**\n";
echo "==========================\n";
echo "⏱️  Total Test Duration: " . number_format($total_test_time, 4) . "s\n";
echo "🗄️  Database Operations: " . number_format($db_total_time, 4) . "s\n";
echo "📁 File System Operations: " . number_format($fs_total_time, 4) . "s\n";
echo "🌐 API Operations: " . number_format($api_total_time, 4) . "s\n";
echo "🔄 Concurrent Operations: " . number_format($concurrent_total_time, 4) . "s\n";

echo "\n📊 **PERFORMANCE ANALYSIS**\n";
echo "============================\n";

// Performance grading
$performance_score = 0;
$max_score = 100;

// Database performance (25 points)
if ($select_avg < 1) $performance_score += 25;
elseif ($select_avg < 5) $performance_score += 20;
elseif ($select_avg < 10) $performance_score += 15;
else $performance_score += 10;

// File system performance (25 points)
if ($create_avg < 5) $performance_score += 25;
elseif ($create_avg < 15) $performance_score += 20;
elseif ($create_avg < 30) $performance_score += 15;
else $performance_score += 10;

// API performance (25 points)
if (isset($api_avg) && $api_avg < 50) $performance_score += 25;
elseif (isset($api_avg) && $api_avg < 100) $performance_score += 20;
elseif (isset($api_avg) && $api_avg < 200) $performance_score += 15;
else $performance_score += 10;

// Concurrent operations (25 points)
if ($concurrent_total_time < 2) $performance_score += 25;
elseif ($concurrent_total_time < 5) $performance_score += 20;
elseif ($concurrent_total_time < 10) $performance_score += 15;
else $performance_score += 10;

echo "🏆 Performance Score: {$performance_score}/{$max_score}\n";

if ($performance_score >= 90) {
    echo "🥇 EXCELLENT - System is performing exceptionally well!\n";
} elseif ($performance_score >= 75) {
    echo "🥈 GOOD - System is performing well with room for optimization\n";
} elseif ($performance_score >= 60) {
    echo "🥉 ACCEPTABLE - System is performing adequately\n";
} else {
    echo "⚠️  NEEDS IMPROVEMENT - System performance requires optimization\n";
}

echo "\n🚀 **The SOCOM Rhombus system has completed comprehensive performance testing!**\n";
echo "All performance metrics have been captured and analyzed.\n";
?>
