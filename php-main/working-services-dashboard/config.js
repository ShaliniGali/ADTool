// Working Services Dashboard Configuration
const DASHBOARD_CONFIG = {
    // API Endpoints
    endpoints: {
        phpErrors: 'http://localhost/socom/php_errors', // PHP error logs endpoint
        phpHealth: 'http://localhost/health.php',
        pythonHealth: 'http://localhost:5000/health',
        databaseHealth: 'http://localhost/health-database.php'
    },
    
    // Refresh Intervals (in milliseconds)
    refresh: {
        errorLogs: 120000,    // 2 minutes
        pageReload: 60000,    // 1 minute
        serviceStatus: 30000  // 30 seconds
    },
    
    // Service Definitions
    services: [
        {
            id: 'php',
            name: 'PHP Backend',
            icon: '🐘',
            description: 'Core PHP application server handling web requests and business logic',
            endpoint: 'phpHealth',
            statusColor: '#007bff'
        },
        {
            id: 'python',
            name: 'Python API',
            icon: '🐍',
            description: 'Python-based API services for data processing and external integrations',
            endpoint: 'pythonHealth',
            statusColor: '#ffc107'
        },
        {
            id: 'jwt',
            name: 'JWT Authentication',
            icon: '🔐',
            description: 'JSON Web Token service for secure user authentication',
            endpoint: null,
            statusColor: '#17a2b8'
        },
        {
            id: 'javascript',
            name: 'JavaScript S3',
            icon: '📁',
            description: 'JavaScript-based S3 file management and storage service',
            endpoint: null,
            statusColor: '#6f42c1'
        },
        {
            id: 'database',
            name: 'MySQL Database',
            icon: '🗄️',
            description: 'Primary database server for application data storage',
            endpoint: 'databaseHealth',
            statusColor: '#e83e8c'
        }
    ],
    
    // Error Log Settings
    errorLogs: {
        maxDisplayErrors: 20,
        criticalKeywords: ['Exception', 'Severity: error', 'Fatal error'],
        warningKeywords: ['Warning', 'Severity: 2', 'Notice']
    },
    
    // UI Settings
    ui: {
        theme: 'auto', // auto, light, dark
        animations: true,
        showTimestamps: true,
        compactMode: false
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DASHBOARD_CONFIG;
}
