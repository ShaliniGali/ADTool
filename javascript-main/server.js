const express = require('express');
const cors = require('cors');
const { S3Client, ListObjectsV2Command, GetObjectCommand, PutObjectCommand, DeleteObjectCommand } = require('@aws-sdk/client-s3');
const { Upload } = require('@aws-sdk/lib-storage');
const multer = require('multer');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;
const REACT_PORT = 3001;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// S3 Client configuration
const s3Client = new S3Client({
    endpoint: process.env.MINIO_ENDPOINT_URL || 'http://rhombus-minio:9000',
    region: 'us-east-1',
    credentials: {
        accessKeyId: process.env.MINIO_ACCESS_KEY || 'minioadmin',
        secretAccessKey: process.env.MINIO_SECRET_KEY || 'minioadmin'
    },
    forcePathStyle: true
});

const bucketName = process.env.MINIO_BUCKET_NAME || 'rhombus-bucket';

// Routes
app.get('/', (req, res) => {
    res.send(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Rhombus S3 File Service</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .container { max-width: 800px; margin: 0 auto; }
                .header { background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .upload-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                .files-section { margin: 20px 0; }
                .file-item { padding: 10px; border-bottom: 1px solid #eee; }
                .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚀 Rhombus S3 File Service</h1>
                    <p>Connected to MinIO at: ${process.env.MINIO_ENDPOINT_URL || 'http://rhombus-minio:9000'}</p>
                    <p>Bucket: ${bucketName}</p>
                </div>
                
                <div class="upload-section">
                    <h3>📁 Upload File</h3>
                    <form action="/upload" method="post" enctype="multipart/form-data">
                        <input type="file" name="file" required>
                        <button type="submit" class="btn">Upload</button>
                    </form>
                </div>
                
                <div class="files-section">
                    <h3>📋 Files in Bucket</h3>
                    <div id="files-list">Loading...</div>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="http://localhost:3001" class="btn" target="_blank">Open React UI Dashboard</a>
                </div>
            </div>
            
            <script>
                // Load files list
                fetch('/files')
                    .then(response => response.json())
                    .then(data => {
                        const filesList = document.getElementById('files-list');
                        if (data.files && data.files.length > 0) {
                            filesList.innerHTML = data.files.map(file => 
                                \`<div class="file-item">
                                    <strong>\${file.Key}</strong> (\${file.Size} bytes)
                                    <button onclick="downloadFile('\${file.Key}')" class="btn" style="margin-left: 10px;">Download</button>
                                    <button onclick="deleteFile('\${file.Key}')" class="btn" style="background: #dc3545; margin-left: 5px;">Delete</button>
                                </div>\`
                            ).join('');
                        } else {
                            filesList.innerHTML = '<p>No files found in bucket</p>';
                        }
                    })
                    .catch(error => {
                        document.getElementById('files-list').innerHTML = '<p>Error loading files: ' + error.message + '</p>';
                    });
                
                function downloadFile(key) {
                    window.open(\`/download/\${encodeURIComponent(key)}\`);
                }
                
                function deleteFile(key) {
                    if (confirm('Are you sure you want to delete this file?')) {
                        fetch(\`/delete/\${encodeURIComponent(key)}\`, { method: 'DELETE' })
                            .then(() => location.reload())
                            .catch(error => alert('Error deleting file: ' + error.message));
                    }
                }
            </script>
        </body>
        </html>
    `);
});

// List files in bucket
app.get('/files', async (req, res) => {
    try {
        const command = new ListObjectsV2Command({
            Bucket: bucketName
        });
        const response = await s3Client.send(command);
        res.json({ files: response.Contents || [] });
    } catch (error) {
        console.error('Error listing files:', error);
        res.status(500).json({ error: error.message });
    }
});

// Upload file
app.post('/upload', multer().single('file'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ error: 'No file uploaded' });
        }

        const upload = new Upload({
            client: s3Client,
            params: {
                Bucket: bucketName,
                Key: req.file.originalname,
                Body: req.file.buffer,
                ContentType: req.file.mimetype
            }
        });

        await upload.done();
        res.json({ message: 'File uploaded successfully', filename: req.file.originalname });
    } catch (error) {
        console.error('Error uploading file:', error);
        res.status(500).json({ error: error.message });
    }
});

// Download file
app.get('/download/:key', async (req, res) => {
    try {
        const command = new GetObjectCommand({
            Bucket: bucketName,
            Key: req.params.key
        });
        const response = await s3Client.send(command);
        
        res.setHeader('Content-Type', response.ContentType || 'application/octet-stream');
        res.setHeader('Content-Disposition', `attachment; filename="${req.params.key}"`);
        
        response.Body.pipe(res);
    } catch (error) {
        console.error('Error downloading file:', error);
        res.status(500).json({ error: error.message });
    }
});

// Delete file
app.delete('/delete/:key', async (req, res) => {
    try {
        const command = new DeleteObjectCommand({
            Bucket: bucketName,
            Key: req.params.key
        });
        await s3Client.send(command);
        res.json({ message: 'File deleted successfully' });
    } catch (error) {
        console.error('Error deleting file:', error);
        res.status(500).json({ error: error.message });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({ 
        status: 'healthy', 
        service: 'Rhombus S3 File Service',
        timestamp: new Date().toISOString(),
        port: PORT
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 Rhombus S3 File Service running on port ${PORT}`);
    console.log(`📁 Connected to MinIO bucket: ${bucketName}`);
    console.log(`🌐 React UI available at: http://localhost:${REACT_PORT}`);
});

// Start React app on different port
const { spawn } = require('child_process');
const reactApp = spawn('npm', ['start'], { 
    cwd: path.join(__dirname, 'sso-tile-app'),
    stdio: 'inherit',
    env: { ...process.env, PORT: REACT_PORT }
});

reactApp.on('error', (error) => {
    console.log('React app not started (this is normal if sso-tile-app doesn\'t exist)');
});




