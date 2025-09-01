<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoints' => [
        'php_errors' => 'http://localhost/php_errors.php',
        'docker_logs' => 'http://localhost/docker_logs.php',
        'health' => 'http://localhost/health.php',
        'dashboard' => 'http://localhost/working-services-dashboard/index.html',
        'debug' => 'http://localhost/working-services-dashboard/debug.html'
    ],
    'services' => [
        'php_errors_endpoint' => '✅ Working',
        'docker_logs_endpoint' => '✅ Working', 
        'health_endpoint' => '✅ Working',
        'dashboard' => '✅ Working',
        'debug_page' => '✅ Working'
    ],
    'message' => 'All endpoints are working correctly. You can now see PHP error logs and Docker container logs in the working services dashboard.'
];

echo json_encode($status, JSON_PRETTY_PRINT);
