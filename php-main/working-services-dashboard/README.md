# Working Services Dashboard

A standalone, real-time monitoring dashboard for all Rhombus system services, completely isolated from production code.

## 📁 Location

The dashboard is now located at the root level of the Python-PHP 2 project:
```
Python-PHP 2/
├── working-services-dashboard/     ← Standalone dashboard
├── php-main/                      ← Production PHP code (unchanged)
├── python-main/                   ← Production Python code (unchanged)
├── javascript-main/               ← Production JavaScript code (unchanged)
└── ...
```

## 🚀 Access

Open the dashboard in your browser:
```
http://localhost/working-services-dashboard/index.html
```

## ✨ Features

- **Real-time Service Monitoring**: Live status of PHP, Python, JWT, JavaScript, Docker, and Database services
- **Auto-refreshing Logs**: PHP error logs update every 2 minutes automatically
- **Service Health Checks**: Automatic health monitoring of all endpoints
- **Interactive Modals**: View service logs, database tables, and container information
- **Responsive Design**: Works on desktop and mobile devices
- **No Production Code Changes**: Completely isolated from main project files

## 🔧 Configuration

All settings are centralized in `config.js`:
- API endpoints
- Refresh intervals (2 minutes for logs, 1 minute for page)
- Service definitions
- Error log settings

## 📊 Services Monitored

1. **🐘 SOCOM Application** - Production PHP backend
2. **🐍 Python API** - FastAPI endpoints
3. **🔐 JWT Authentication** - Token service
4. **⚛️ JavaScript S3 Service** - File management
5. **🐳 Docker Container Logs** - Container monitoring
6. **🗄️ MySQL Database** - Database connectivity

## 🚨 Error Monitoring

- **Real-time PHP Error Logs**: Displayed at bottom of page
- **Auto-refresh**: Every 2 minutes
- **Status Indicators**: Green (healthy), Yellow (warnings), Red (errors)
- **Critical Error Detection**: Identifies issues causing blank screens

## 🎨 UI Components

- **Service Cards**: Individual tiles for each service with status badges
- **Modals**: Database tables viewer and service logs display
- **Status Badges**: Color-coded working/error indicators
- **Responsive Grid**: Adapts to different screen sizes

## 🔄 Auto-refresh Settings

- **Error Logs**: 2 minutes (120,000 ms)
- **Page Reload**: 1 minute (60,000 ms)
- **Service Health**: On page load and manual refresh

## 🛠️ Technical Details

- **Pure HTML/CSS/JavaScript**: No external dependencies
- **ES6 Classes**: Modern JavaScript architecture
- **Fetch API**: Modern HTTP requests
- **CSS Grid & Flexbox**: Modern layout system
- **Responsive Design**: Mobile-first approach

## 📱 Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 🚫 What This Dashboard Does NOT Do

- ❌ Modify any production code
- ❌ Access production databases directly
- ❌ Change production configurations
- ❌ Interfere with production services

## 🔍 Troubleshooting

### Dashboard Not Loading
- Check if `working-services-dashboard/index.html` exists
- Verify all JavaScript files are present
- Check browser console for errors

### Services Showing as ERROR
- Verify services are running
- Check if endpoints are accessible
- Review network connectivity

### Logs Not Updating
- Check if PHP error logs are accessible
- Verify auto-refresh is enabled
- Check browser console for fetch errors

## 📝 Maintenance

### Adding New Services
1. Add service card to `index.html`
2. Add service definition to `config.js`
3. Add health check method to `dashboard.js`

### Updating Endpoints
- Modify `config.js` endpoints section
- No changes needed to other files

### Changing Refresh Intervals
- Update values in `config.js`
- Changes apply immediately

## 🎯 Benefits

1. **Complete Isolation**: No risk to production code
2. **Easy Maintenance**: Centralized configuration
3. **Real-time Monitoring**: Immediate issue detection
4. **User-friendly**: No complex setup required
5. **Portable**: Can be moved to any location
6. **Customizable**: Easy to modify and extend

## 🔒 Security

- **Read-only Access**: Dashboard only displays information
- **No Database Writes**: Cannot modify production data
- **No Code Execution**: Cannot run arbitrary code
- **Isolated Environment**: Completely separate from production

---

**Note**: This dashboard is designed to be completely independent of the main production codebase. All monitoring and display logic is contained within this folder, ensuring no interference with production services.
