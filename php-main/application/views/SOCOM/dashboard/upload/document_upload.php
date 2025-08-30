<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_data['page_title']; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .upload-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .upload-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9fa;
        }
        
        .upload-area:hover, .upload-area.dragover {
            border-color: #667eea;
            background: #e3f2fd;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .file-info {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none;
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #e0e0e0;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #8bc34a);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .upload-history {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .file-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .file-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .file-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-new { background: #e3f2fd; color: #1976d2; }
        .status-processing { background: #fff3e0; color: #f57c00; }
        .status-completed { background: #e8f5e8; color: #388e3c; }
        .status-error { background: #ffebee; color: #d32f2f; }
        
        .btn-upload {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-upload:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <div class="container">
            <!-- Header -->
            <div class="text-center text-white mb-4">
                <h1><i class="fas fa-cloud-upload-alt"></i> SOCOM Document Upload</h1>
                <p class="lead">Upload and manage your documents securely</p>
                <p><strong>Active Cycle:</strong> <?php echo $cycle_name; ?></p>
            </div>

            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number" id="total-files">0</div>
                        <div class="stats-label">Total Files</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number" id="total-size">0 MB</div>
                        <div class="stats-label">Total Size</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number" id="pending-files">0</div>
                        <div class="stats-label">Pending</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <div class="stats-number" id="completed-files">0</div>
                        <div class="stats-label">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="upload-card">
                <div class="card-body">
                    <h4 class="card-title mb-3">
                        <i class="fas fa-upload"></i> Upload Documents
                    </h4>
                    
                    <div class="upload-area" id="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Drag & Drop files here</h5>
                        <p class="text-muted">or click to browse</p>
                        <p class="small text-muted">
                            Max file size: <?php echo $max_file_size; ?> | 
                            Allowed types: <?php echo $allowed_extensions; ?>
                        </p>
                        <input type="file" id="file-input" multiple accept=".xlsx,.xls,.csv,.pdf,.doc,.docx" style="display: none;">
                    </div>

                    <!-- File Info Display -->
                    <div class="file-info" id="file-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 id="file-name"></h6>
                                <small class="text-muted" id="file-size"></small>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-upload btn-sm" id="upload-btn">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div id="alert-container"></div>
                </div>
            </div>

            <!-- Upload History -->
            <div class="upload-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-history"></i> Upload History
                        </h4>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshHistory()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="upload-history" id="upload-history">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p>Loading upload history...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        let selectedFile = null;
        let uploadInProgress = false;

        // Initialize
        $(document).ready(function() {
            setupEventListeners();
            loadUploadHistory();
        });

        function setupEventListeners() {
            // Click to browse
            $('#upload-area').click(function() {
                $('#file-input').click();
            });

            // File input change
            $('#file-input').change(function(e) {
                if (e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0]);
                }
            });

            // Drag and drop
            $('#upload-area').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $('#upload-area').on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $('#upload-area').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                if (e.originalEvent.dataTransfer.files.length > 0) {
                    handleFileSelection(e.originalEvent.dataTransfer.files[0]);
                }
            });

            // Upload button
            $('#upload-btn').click(function() {
                if (selectedFile && !uploadInProgress) {
                    uploadFile(selectedFile);
                }
            });
        }

        function handleFileSelection(file) {
            selectedFile = file;
            
            // Validate file
            if (!validateFile(file)) {
                return;
            }

            // Display file info
            $('#file-name').text(file.name);
            $('#file-size').text(formatFileSize(file.size));
            $('#file-info').show();
            
            // Reset progress
            $('#progress-fill').css('width', '0%');
            $('#upload-btn').prop('disabled', false);
        }

        function validateFile(file) {
            const maxSize = <?php echo self::MAX_FILE_SIZE; ?>;
            const allowedTypes = <?php echo json_encode(self::ALLOWED_EXTENSIONS); ?>;
            
            // Check file size
            if (file.size > maxSize) {
                showAlert('File size exceeds maximum allowed size of <?php echo $max_file_size; ?>', 'danger');
                return false;
            }

            // Check file type
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                showAlert('File type not allowed. Allowed types: <?php echo $allowed_extensions; ?>', 'danger');
                return false;
            }

            return true;
        }

        function uploadFile(file) {
            uploadInProgress = true;
            $('#upload-btn').prop('disabled', true);
            $('#upload-btn').html('<i class="fas fa-spinner fa-spin"></i> Uploading...');

            const formData = new FormData();
            formData.append('document', file);

            $.ajax({
                url: '<?php echo site_url("SOCOM/Document_Upload/upload_file"); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            $('#progress-fill').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        $('#file-info').hide();
                        selectedFile = null;
                        loadUploadHistory();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    let message = 'Upload failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showAlert(message, 'danger');
                },
                complete: function() {
                    uploadInProgress = false;
                    $('#upload-btn').prop('disabled', false);
                    $('#upload-btn').html('<i class="fas fa-upload"></i> Upload');
                }
            });
        }

        function loadUploadHistory() {
            $.ajax({
                url: '<?php echo site_url("SOCOM/Document_Upload/get_upload_history"); ?>',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        displayUploadHistory(response.uploads);
                        updateStats(response.uploads);
                    } else {
                        $('#upload-history').html('<div class="text-center text-muted py-4"><p>Failed to load upload history</p></div>');
                    }
                },
                error: function() {
                    $('#upload-history').html('<div class="text-center text-muted py-4"><p>Failed to load upload history</p></div>');
                }
            });
        }

        function displayUploadHistory(uploads) {
            if (!uploads || uploads.length === 0) {
                $('#upload-history').html('<div class="text-center text-muted py-4"><p>No uploads found</p></div>');
                return;
            }

            let html = '';
            uploads.forEach(function(upload) {
                const statusClass = getStatusClass(upload.FILE_STATUS);
                const statusText = getStatusText(upload.FILE_STATUS);
                const uploadDate = new Date(upload.CREATED_TIMESTAMP).toLocaleDateString();
                
                html += `
                    <div class="file-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1">${upload.FILE_NAME}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> ${uploadDate} | 
                                    <i class="fas fa-user"></i> User ID: ${upload.USER_ID}
                                </small>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="file-status ${statusClass}">${statusText}</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="downloadFile(${upload.USR_DT_UPLOADS_ID})">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(${upload.USR_DT_UPLOADS_ID})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#upload-history').html(html);
        }

        function updateStats(uploads) {
            const totalFiles = uploads.length;
            const totalSize = uploads.reduce((sum, upload) => sum + (upload.FILE_SIZE || 0), 0);
            const pendingFiles = uploads.filter(upload => upload.FILE_STATUS === 'NEW' || upload.FILE_STATUS === 'PROCESSING').length;
            const completedFiles = uploads.filter(upload => upload.FILE_STATUS === 'COMPLETED').length;

            $('#total-files').text(totalFiles);
            $('#total-size').text(formatFileSize(totalSize));
            $('#pending-files').text(pendingFiles);
            $('#completed-files').text(completedFiles);
        }

        function getStatusClass(status) {
            const statusMap = {
                'NEW': 'status-new',
                'PROCESSING': 'status-processing',
                'COMPLETED': 'status-completed',
                'ERROR': 'status-error'
            };
            return statusMap[status] || 'status-new';
        }

        function getStatusText(status) {
            const statusMap = {
                'NEW': 'New',
                'PROCESSING': 'Processing',
                'COMPLETED': 'Completed',
                'ERROR': 'Error'
            };
            return statusMap[status] || 'New';
        }

        function downloadFile(fileId) {
            window.open('<?php echo site_url("SOCOM/Document_Upload/download_file/"); ?>' + fileId, '_blank');
        }

        function deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file?')) {
                $.ajax({
                    url: '<?php echo site_url("SOCOM/Document_Upload/delete_file"); ?>',
                    type: 'POST',
                    data: { file_id: fileId },
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            loadUploadHistory();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Failed to delete file', 'danger');
                    }
                });
            }
        }

        function refreshHistory() {
            loadUploadHistory();
        }

        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-custom alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('#alert-container').html(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>
