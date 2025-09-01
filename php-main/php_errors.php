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
    'total_errors' => 0,
    'critical_errors' => 0,
    'warnings' => 0,
    'recent_errors' => [],
    'last_updated' => '',
    'log_file' => ''
];

try {
    $logDir = __DIR__ . '/application/logs/';
    $logFiles = glob($logDir . 'log-*.php');
    
    if (empty($logFiles)) {
        $response['recent_errors'] = ['No log files found'];
        $response['last_updated'] = 'Never';
        $response['log_file'] = 'None';
    } else {
        // Sort by modification time, newest first
        usort($logFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestLogFile = $logFiles[0];
        $logContent = file_get_contents($latestLogFile);
        
        if ($logContent === false) {
            throw new Exception('Unable to read log file: ' . basename($latestLogFile));
        }

        $lines = explode("\n", $logContent);
        $recentErrors = [];
        $criticalErrors = 0;
        $warnings = 0;
        $totalErrors = 0;

        // Get the last 50 lines (most recent)
        $recentLines = array_slice($lines, -50);

        foreach ($recentLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check for critical errors
            if (preg_match('/(ERROR|FATAL|Exception|Severity: error)/i', $line)) {
                $criticalErrors++;
                $totalErrors++;
                $recentErrors[] = $line;
            }
            // Check for warnings
            elseif (preg_match('/(WARNING|Warning|Severity: 2|Notice)/i', $line)) {
                $warnings++;
                $totalErrors++;
                $recentErrors[] = $line;
            }
        }

        // Limit to last 20 errors for display
        $recentErrors = array_slice($recentErrors, -20);

        $response['total_errors'] = $totalErrors;
        $response['critical_errors'] = $criticalErrors;
        $response['warnings'] = $warnings;
        $response['recent_errors'] = $recentErrors;
        $response['last_updated'] = date('Y-m-d H:i:s', filemtime($latestLogFile));
        $response['log_file'] = basename($latestLogFile);
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
