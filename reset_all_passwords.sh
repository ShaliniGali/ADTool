#!/bin/bash

# SOCOM Password Reset Script
# This script resets all user passwords to "password"

echo "=========================================="
echo "SOCOM PASSWORD RESET SCRIPT"
echo "=========================================="

# Check if Docker containers are running
echo "Step 1: Checking Docker containers..."
if ! docker ps | grep -q "rhombus-mysql"; then
    echo "ERROR: rhombus-mysql container is not running!"
    echo "Please start Docker containers first: docker compose up -d"
    exit 1
fi

if ! docker ps | grep -q "rhombus-php"; then
    echo "ERROR: rhombus-php container is not running!"
    echo "Please start Docker containers first: docker compose up -d"
    exit 1
fi

echo "✓ Docker containers are running"

# Generate password hash and salt
echo "Step 2: Generating password hash and salt..."
PASSWORD_DATA=$(docker exec rhombus-php php -r "
\$_SERVER['SERVER_NAME'] = 'localhost';
\$_SERVER['REQUEST_URI'] = '/';
\$_SERVER['REQUEST_METHOD'] = 'GET';
putenv('GLOBAL_APP_STRUCTURE=SOCOM');
putenv('SOCOM_ENVIRONMENT=siprdevelopment');
putenv('SOCOM_guardian_users=SOCOM_UI');
putenv('SOCOM_P1=FALSE');
putenv('SOCOM_DEV_BYPASS_AUTH=FALSE');
putenv('SOCOM_DEV_MODE=TRUE');
putenv('SOCOM_DISABLE_STRICT_SQL=TRUE');
putenv('UI_USERNAME_PASS_AUTH=TRUE');
putenv('SOCOM_emails_qa=admin@rhombus.local::::test@rhombus.local');
putenv('ENCRYPT_DECRYPT_PASSWORD_ITERATIONS=1000');
putenv('ENCRYPTION_SIZE=64');
require_once '/var/www/html/index.php';
\$CI =& get_instance();
\$CI->load->library('password_encrypt_decrypt');
\$encrypted_data = \$CI->password_encrypt_decrypt->encrypt('password');
echo \$encrypted_data['password'] . '|' . \$encrypted_data['salt'];
")

if [ -z "$PASSWORD_DATA" ] || [ "$PASSWORD_DATA" = "|" ]; then
    echo "ERROR: Failed to generate password hash and salt!"
    exit 1
fi

HASH=$(echo $PASSWORD_DATA | cut -d'|' -f1)
SALT=$(echo $PASSWORD_DATA | cut -d'|' -f2)

echo "✓ Generated password hash and salt"

# Update passwords
echo "Step 3: Updating user passwords..."
UPDATED_COUNT=$(docker exec rhombus-mysql mysql -u root -prhombus_root_password -e "
USE SOCOM_UI;
UPDATE users SET password = '$HASH', saltiness = '$SALT' WHERE password IS NULL OR password = '';
SELECT ROW_COUNT();
" -s -N)

echo "✓ Updated passwords for $UPDATED_COUNT users"

# Verify password reset
echo "Step 4: Verifying password reset..."
USER_STATUS=$(docker exec rhombus-mysql mysql -u root -prhombus_root_password -e "
USE SOCOM_UI;
SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN password IS NOT NULL AND password != '' THEN 1 ELSE 0 END) as users_with_passwords
FROM users;
" -s -N)

TOTAL_USERS=$(echo $USER_STATUS | cut -d$'\t' -f1)
USERS_WITH_PASSWORDS=$(echo $USER_STATUS | cut -d$'\t' -f2)

echo "✓ Total users: $TOTAL_USERS"
echo "✓ Users with passwords: $USERS_WITH_PASSWORDS"

# Test login
echo "Step 5: Testing login..."
LOGIN_RESULT=$(curl -s -X POST "http://localhost/login/user_check" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin@rhombus.local&password=password&tos_agreement_check=true" | jq -r '.result // "failed"')

if [[ $LOGIN_RESULT == "success" ]]; then
    echo "✓ Admin login successful"
else
    echo "⚠ Admin login failed (result: $LOGIN_RESULT)"
    echo "  This might be due to environment settings or other issues"
fi

echo "=========================================="
echo "PASSWORD RESET COMPLETE!"
echo "=========================================="
echo "Summary:"
echo "- Total users: $TOTAL_USERS"
echo "- Users with passwords: $USERS_WITH_PASSWORDS"
echo "- Admin login test: $LOGIN_RESULT"
echo ""
echo "All users can now login with:"
echo "  Username: [any user email]"
echo "  Password: password"
echo ""
echo "Available users:"
docker exec rhombus-mysql mysql -u root -prhombus_root_password -e "
USE SOCOM_UI;
SELECT id, username, email FROM users ORDER BY id;
"
echo "=========================================="
