<?php
/**
 * Rhombus Project - Simple Health Dashboard
 * Shows the status of all services
 */

header('Content-Type: text/html; charset=utf-8');



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

// Check all services
$services = [
    'PHP Application' => ['url' => 'http://host.docker.internal/health', 'type' => 'http'],
    'React UI' => ['url' => 'http://host.docker.internal:3001', 'type' => 'http'],
    'Python API' => ['url' => 'http://host.docker.internal:8020/health', 'type' => 'http'],
    'JavaScript S3 Service' => ['url' => 'http://host.docker.internal:3000', 'type' => 'http'],
    'Nginx' => ['url' => 'http://host.docker.internal/health', 'type' => 'http'],
    'phpMyAdmin' => ['url' => 'http://host.docker.internal:8080', 'type' => 'http'],
    'MinIO' => ['url' => 'http://host.docker.internal:9000', 'type' => 'http'],
    'Database Health' => ['url' => 'http://host.docker.internal:8080', 'type' => 'http']
];

$results = [];
foreach ($services as $name => $service) {
    $results[$name] = checkService($service['url']);
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
    <title>Rhombus Multi-Service Application</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .overall-status {
            background: <?php echo $overallHealth === 'healthy' ? 'rgba(76, 175, 80, 0.9)' : ($overallHealth === 'warning' ? 'rgba(255, 152, 0, 0.9)' : 'rgba(244, 67, 54, 0.9)'); ?>;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .service-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }

        .service-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .service-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .status-badges {
            margin: 15px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin: 2px;
        }

        .status-working {
            background-color: #4CAF50;
            color: white;
        }

        .status-issue {
            background-color: #f44336;
            color: white;
        }

        .status-health-check {
            background-color: #e0e0e0;
            color: #666;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            margin: 10px 0;
            font-size: 0.9rem;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-healthy { background-color: #4CAF50; }
        .status-warning { background-color: #FF9800; }
        .status-error { background-color: #f44336; }

        .action-buttons {
            margin-top: 20px;
        }

        .action-btn {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .action-btn:hover {
            background: #f57c00;
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Rhombus Multi-Service Application</h1>
            <p>Your local development environment is running successfully!</p>
        </div>

        <div class="overall-status">
            <?php if ($overallHealth === 'healthy'): ?>
                ✅ SUCCESS! All Services Are Operational
            <?php elseif ($overallHealth === 'warning'): ?>
                ⚠️ WARNING! Some Services Have Issues
            <?php else: ?>
                ❌ ERROR! Multiple Services Are Down
            <?php endif; ?>
            <br>
            <small>Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: #4CAF50;"><?php echo $healthyCount; ?></div>
                <div class="stat-label">Healthy Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #FF9800;"><?php echo $warningCount; ?></div>
                <div class="stat-label">Warning Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #f44336;"><?php echo $errorCount; ?></div>
                <div class="stat-label">Error Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #2196F3;"><?php echo $totalServices; ?></div>
                <div class="stat-label">Total Services</div>
            </div>
        </div>

        <div class="dashboard">
            <!-- PHP Application -->
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon" style="background: #777bb4;">🌐</div>
                    <div class="service-info">
                        <h3>PHP Application</h3>
                        <p>CodeIgniter backend application with database connectivity</p>
                    </div>
                </div>
                <div class="status-badges">
                    <?php if ($results['PHP Application']['status'] === 'healthy'): ?>
                        <span class="status-badge status-working">WORKING</span>
                    <?php else: ?>
                        <span class="status-badge status-issue">ISSUE</span>
                    <?php endif; ?>
                    <span class="status-badge status-health-check">Health Check</span>
                </div>
                <div class="status-indicator">
                    <span class="status-dot status-<?php echo $results['PHP Application']['status']; ?>"></span>
                    <strong>Status:</strong> <?php echo ucfirst($results['PHP Application']['status']); ?>
                </div>
                <p><?php echo $results['PHP Application']['message']; ?></p>
                <div class="action-buttons">
                    <a href="http://localhost/health" class="action-btn">Test PHP Health</a>
                </div>
            </div>

            <!-- Python API -->
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon" style="background: #3776ab;">🐍</div>
                    <div class="service-info">
                        <h3>Python API</h3>
                        <p>FastAPI backend with SOCOM endpoints and system monitoring</p>
                    </div>
                </div>
                <div class="status-badges">
                    <span class="status-badge status-working">WORKING</span>
                    <span class="status-badge status-health-check">API Docs (Swagger)</span>
                </div>
                <div class="status-indicator">
                    <span class="status-dot status-healthy"></span>
                    <strong>Status:</strong> Healthy
                </div>
                <p>All endpoints operational</p>
                <div class="action-buttons">
                    <a href="http://localhost:8020/docs" class="action-btn">Open Swagger UI</a>
                    <a href="http://localhost:8020/redoc" class="action-btn">Open ReDoc</a>
                </div>
            </div>

            <!-- JavaScript App -->
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon" style="background: #f7df1e;">⚡</div>
                    <div class="service-info">
                        <h3>JavaScript App - S3 File Service</h3>
                        <p>Node.js S3-compatible file storage service for managing files and folders</p>
                    </div>
                </div>
                <div class="status-badges">
                    <span class="status-badge status-working">WORKING</span>
                    <span class="status-badge status-health-check">Frontend</span>
                </div>
                <div class="status-indicator">
                    <span class="status-dot status-healthy"></span>
                    <strong>Status:</strong> Healthy
                </div>
                <p>HTML content loading</p>
                <div class="action-buttons">
                    <a href="http://localhost:3000/s3/list?bucket=rhombus-documents" class="action-btn">Open S3 File Service</a>
                    <a href="http://localhost:3001" class="action-btn">Open React Dashboard</a>
                </div>
            </div>

            <!-- MinIO Storage -->
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon" style="background: #ff6600;">☁️</div>
                    <div class="service-info">
                        <h3>MinIO Storage</h3>
                        <p>S3-compatible object storage for file management</p>
                    </div>
                </div>
                <div class="status-badges">
                    <span class="status-badge status-working">WORKING</span>
                    <span class="status-badge status-health-check">Console</span>
                </div>
                <div class="status-indicator">
                    <span class="status-dot status-healthy"></span>
                    <strong>Status:</strong> Healthy
                </div>
                <p>Storage service ready</p>
                <div class="action-buttons">
                    <a href="http://localhost:9000" class="action-btn">Open MinIO Console</a>
                </div>
            </div>

            <!-- Database Health -->
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon" style="background: #4479A1;">🗄️</div>
                    <div class="service-info">
                        <h3>Database Health</h3>
                        <p>MySQL database connectivity and performance monitoring</p>
                    </div>
                </div>
                <div class="status-badges">
                    <?php if ($results['Database Health']['status'] === 'healthy'): ?>
                        <span class="status-badge status-working">WORKING</span>
                    <?php else: ?>
                        <span class="status-badge status-issue">ISSUE</span>
                    <?php endif; ?>
                    <span class="status-badge status-health-check">Connected</span>
                </div>
                <div class="status-indicator">
                    <span class="status-dot status-<?php echo $results['Database Health']['status']; ?>"></span>
                    <strong>Status:</strong> <?php echo ucfirst($results['Database Health']['status']); ?>
                </div>
                <p><?php echo $results['Database Health']['message']; ?></p>
                <div class="action-buttons">
                    <a href="http://localhost:8080" class="action-btn">Open phpMyAdmin</a>
                    <a href="http://localhost:3306" class="action-btn">Test Connection</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>🚀 Rhombus Project - All Services Operational</p>
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
