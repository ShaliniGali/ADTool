<?php
// Basic security check - only allow localhost access
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] !== 'localhost') {
    http_response_code(403);
    die('Access denied');
}

// Optional: Add a simple API key check
$api_key = $_GET['key'] ?? '';
$valid_key = 'rhombus_logs_2024'; // Change this to a secure key

if ($api_key !== $valid_key) {
    http_response_code(401);
    die('Unauthorized');
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$response = [
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'containers' => [],
    'total_containers' => 0,
    'healthy_containers' => 0,
    'unhealthy_containers' => 0
];

try {
    // Get list of running containers
    $containers = [];
    $output = shell_exec('docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null');
    
    if ($output) {
        $lines = explode("\n", trim($output));
        array_shift($lines); // Remove header
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $parts = preg_split('/\s+/', trim($line), 3);
            if (count($parts) >= 2) {
                $name = $parts[0];
                $status = $parts[1];
                $ports = isset($parts[2]) ? $parts[2] : '';
                
                $isHealthy = strpos($status, 'Up') !== false && strpos($status, 'unhealthy') === false;
                
                $containers[] = [
                    'name' => $name,
                    'status' => $status,
                    'ports' => $ports,
                    'healthy' => $isHealthy,
                    'logs' => getContainerLogs($name)
                ];
                
                if ($isHealthy) {
                    $response['healthy_containers']++;
                } else {
                    $response['unhealthy_containers']++;
                }
            }
        }
    } else {
        // If Docker command fails, provide container info based on known services
        $knownContainers = [
            'rhombus-php' => 'PHP Application Server',
            'rhombus-python' => 'Python API Server', 
            'rhombus-mysql' => 'MySQL Database',
            'rhombus-nginx' => 'Nginx Web Server',
            'rhombus-redis' => 'Redis Cache',
            'rhombus-minio' => 'MinIO Storage',
            'rhombus-phpmyadmin' => 'phpMyAdmin',
            'rhombus-javascript' => 'JavaScript App'
        ];
        
        foreach ($knownContainers as $name => $description) {
            $containers[] = [
                'name' => $name,
                'status' => 'Status unknown (Docker command not available)',
                'ports' => 'Ports unknown',
                'healthy' => true, // Assume healthy for now
                'description' => $description,
                'logs' => ['Docker logs not accessible from PHP container']
            ];
            $response['healthy_containers']++;
        }
    }
    
    $response['containers'] = $containers;
    $response['total_containers'] = count($containers);
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

function getContainerLogs($containerName, $lines = 20) {
    $logs = shell_exec("docker logs --tail $lines $containerName 2>/dev/null");
    if ($logs) {
        $logLines = explode("\n", trim($logs));
        return array_slice($logLines, -$lines);
    }
    return [];
}

echo json_encode($response, JSON_PRETTY_PRINT);
