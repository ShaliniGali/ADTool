<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Php_Errors extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
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
            $logData = $this->getRecentErrorLogs();
            $response = array_merge($response, $logData);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        $this->output
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    private function getRecentErrorLogs()
    {
        $logDir = APPPATH . 'logs/';
        $logFiles = glob($logDir . 'log-*.php');
        
        if (empty($logFiles)) {
            return [
                'total_errors' => 0,
                'critical_errors' => 0,
                'warnings' => 0,
                'recent_errors' => ['No log files found'],
                'last_updated' => 'Never',
                'log_file' => 'None'
            ];
        }

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

        return [
            'total_errors' => $totalErrors,
            'critical_errors' => $criticalErrors,
            'warnings' => $warnings,
            'recent_errors' => $recentErrors,
            'last_updated' => date('Y-m-d H:i:s', filemtime($latestLogFile)),
            'log_file' => basename($latestLogFile)
        ];
    }
}
