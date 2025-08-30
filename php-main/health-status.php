<?php
/**
 * Rhombus Project - Health Status Page
 * Checks the status of all services and displays comprehensive health information
 */

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Function to check if a service is accessible
function checkService($url, $timeout = 5) {
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

// Function to check database connection
function checkDatabase() {
    try {
        $host = 'mysql';
        $dbname = 'rhombus_db';
        $username = 'rhombus_user';
        $password = 'rhombus_password';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            return ['status' => 'healthy', 'message' => 'Connected successfully', 'code' => 200];
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage(), 'code' => 0];
    }
}

// Function to check Redis connection
function checkRedis() {
    try {
        $redis = new Redis();
        $redis->connect('redis', 6379, 2);
        $redis->ping();
        return ['status' => 'healthy', 'message' => 'Connected successfully', 'code' => 200];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage(), 'code' => 0];
    }
}

// Check all services - only the working ones
$services = [
    'PHP Application' => ['url' => 'http://rhombus-nginx/health', 'type' => 'http'],
    'Python API' => ['url' => 'http://rhombus-python:8020/health', 'type' => 'http'],
    'MinIO Console' => ['url' => 'http://rhombus-minio:9001', 'type' => 'http'],
    'MySQL Database' => ['type' => 'database']
];

$results = [];
foreach ($services as $name => $service) {
    if ($service['type'] === 'http') {
        $results[$name] = checkService($service['url']);
    } elseif ($service['type'] === 'database') {
        $results[$name] = checkDatabase();
    } elseif ($service['type'] === 'redis') {
        $results[$name] = checkRedis();
    }
}

// Calculate overall health
$healthyCount = 0;
$warningCount = 0;
$errorCount = 0;

foreach ($results as $result) {
    if ($result['status'] === 'healthy') $healthyCount++;
    elseif ($result['status'] === 'warning') $warningCount++;
    else $errorCount++;
}

$totalServices = count($services);
$overallHealth = 'healthy';
if ($errorCount > 0) $overallHealth = 'error';
elseif ($warningCount > 0) $overallHealth = 'warning';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhombus Project - Health Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .overall-status {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }

        .overall-status.healthy { border-left: 5px solid #4CAF50; }
        .overall-status.warning { border-left: 5px solid #FF9800; }
        .overall-status.error { border-left: 5px solid #f44336; }

        .status-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .status-healthy { background-color: #4CAF50; }
        .status-warning { background-color: #FF9800; }
        .status-error { background-color: #f44336; }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            margin: 10px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-card.healthy { border-left: 5px solid #4CAF50; }
        .service-card.warning { border-left: 5px solid #FF9800; }
        .service-card.error { border-left: 5px solid #f44336; }

        .service-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .service-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-left: 10px;
        }

        .service-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .service-status.healthy {
            background-color: #e8f5e8;
            color: #2e7d32;
        }

        .service-status.warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .service-status.error {
            background-color: #ffebee;
            color: #c62828;
        }

        .service-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            background: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease;
        }

        .action-btn:hover {
            background: #1976D2;
        }

        .action-btn.secondary {
            background: #757575;
        }

        .action-btn.secondary:hover {
            background: #616161;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 30px;
            opacity: 0.8;
        }

        .refresh-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin: 20px 0;
            transition: background 0.3s ease;
        }

        .refresh-btn:hover {
            background: #45a049;
        }

        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Rhombus Project Health Status</h1>
            <p>Comprehensive system health monitoring</p>
        </div>

        <div class="overall-status <?php echo $overallHealth; ?>">
            <h2>
                <span class="status-indicator status-<?php echo $overallHealth; ?>"></span>
                Overall System Health: <?php echo ucfirst($overallHealth); ?>
            </h2>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number" style="color: #4CAF50;"><?php echo $healthyCount; ?></div>
                    <div class="stat-label">Healthy Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #FF9800;"><?php echo $warningCount; ?></div>
                    <div class="stat-label">Warning Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #f44336;"><?php echo $errorCount; ?></div>
                    <div class="stat-label">Error Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #2196F3;"><?php echo $totalServices; ?></div>
                    <div class="stat-label">Total Services</div>
                </div>
            </div>

            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Status</button>
        </div>

        <div class="services-grid">
            <?php foreach ($results as $serviceName => $result): ?>
            <div class="service-card <?php echo $result['status']; ?>">
                <div class="service-header">
                    <span class="status-indicator status-<?php echo $result['status']; ?>"></span>
                    <div class="service-name"><?php echo htmlspecialchars($serviceName); ?></div>
                </div>
                <div class="service-status <?php echo $result['status']; ?>">
                    <strong>Status:</strong> <?php echo ucfirst($result['status']); ?><br>
                    <strong>Message:</strong> <?php echo htmlspecialchars($result['message']); ?><br>
                    <?php if ($result['code'] > 0): ?>
                    <strong>Code:</strong> <?php echo $result['code']; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="footer">
            <p>🚀 Rhombus Project - Health Monitoring Dashboard</p>
            <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
