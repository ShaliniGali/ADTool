<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            margin: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .status {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .token-display {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">SOCOM JWT Authentication</div>
        <p>Click the button below to generate a JWT token and access SOCOM</p>
        
        <button class="btn" onclick="generateToken()">Generate JWT Token</button>
        <button class="btn" onclick="goToSOCOM()">Go to SOCOM Dashboard</button>
        
        <div id="status" class="status"></div>
        <div id="token-display" class="token-display"></div>
    </div>

    <script>
        function generateToken() {
            fetch('<?php echo base_url("socom/jwt_login/generate_token"); ?>')
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('status');
                    const tokenDiv = document.getElementById('token-display');
                    
                    if (data.status === 'success') {
                        statusDiv.className = 'status success';
                        statusDiv.textContent = data.message;
                        statusDiv.style.display = 'block';
                        
                        // Store token in localStorage for easy access
                        localStorage.setItem('socom_jwt_token', data.token);
                        
                        // Display token
                        tokenDiv.textContent = 'Token: ' + data.token;
                        tokenDiv.style.display = 'block';
                        
                        // Auto-redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 2000);
                    } else {
                        statusDiv.className = 'status error';
                        statusDiv.textContent = data.message;
                        statusDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    const statusDiv = document.getElementById('status');
                    statusDiv.className = 'status error';
                    statusDiv.textContent = 'Error: ' + error.message;
                    statusDiv.style.display = 'block';
                });
        }
        
        function goToSOCOM() {
            const token = localStorage.getItem('socom_jwt_token');
            if (token) {
                // Add token as query parameter
                window.location.href = '<?php echo base_url("socom/index"); ?>?jwt_token=' + token;
            } else {
                alert('Please generate a JWT token first');
            }
        }
        
        // Check if token exists on page load
        window.onload = function() {
            const token = localStorage.getItem('socom_jwt_token');
            if (token) {
                const tokenDiv = document.getElementById('token-display');
                tokenDiv.textContent = 'Existing Token: ' + token;
                tokenDiv.style.display = 'block';
            }
        };
    </script>
</body>
</html>
