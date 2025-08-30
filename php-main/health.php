<?php
/**
 * Rhombus Project - Simple Health Endpoint
 * Basic health check that returns PHP status
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Simple health check response
$response = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'uptime' => 'running',
    'php_version' => PHP_VERSION,
    'message' => 'PHP Application is running successfully',
    'services' => [
        'php_application' => [
            'status' => 'healthy',
            'message' => 'PHP-FPM is running',
            'code' => 200
        ]
    ],
    'summary' => [
        'total' => 1,
        'healthy' => 1,
        'warning' => 0,
        'error' => 0
    ]
];

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
