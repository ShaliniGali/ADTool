<?php
/**
 * Rhombus Project - Health Endpoint
 * Simple health check that returns JSON status
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Function to check if a service is accessible
function checkService($url, $timeout = 3) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['status' => 'error', 'message' => $error, 'code' => 0];
    }
    
    if ($httpCode >= 200 && $httpCode < 400) {
        return ['status' => 'healthy', 'message' => "HTTP $httpCode", 'code' => $httpCode];
    } else {
        return ['status' => 'warning', 'message' => "HTTP $httpCode", 'code' => $httpCode];
    }
}

// Check all services using container names and internal network
$services = [
    'php_application' => checkService('http://rhombus-nginx/health'),
    'react_ui' => checkService('http://rhombus-javascript:3001'),
    'python_api' => checkService('http://rhombus-python:8020/health'),
    'javascript_s3' => checkService('http://rhombus-javascript:3000'),
    'nginx' => checkService('http://rhombus-nginx/health'),
    'phpmyadmin' => checkService('http://rhombus-phpmyadmin'),
    'minio' => checkService('http://rhombus-minio:9001')
];

// Calculate overall health
$healthyCount = 0;
$warningCount = 0;
$errorCount = 0;

foreach ($services as $service) {
    if ($service['status'] === 'healthy') $healthyCount++;
    elseif ($service['status'] === 'warning') $warningCount++;
    else $errorCount++;
}

$totalServices = count($services);
$overallHealth = 'healthy';
if ($errorCount > 0) $overallHealth = 'error';
elseif ($warningCount > 0) $overallHealth = 'warning';

// Prepare response
$response = [
    'status' => $overallHealth,
    'timestamp' => date('c'),
    'uptime' => 'running',
    'services' => $services,
    'summary' => [
        'total' => $totalServices,
        'healthy' => $healthyCount,
        'warning' => $warningCount,
        'error' => $errorCount
    ]
];

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
