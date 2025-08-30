echo "#####################################################"
echo "# Creating JS files needed"

npm i webpack

echo "#####################################################"
echo "# webpack installed"
echo "#####################################################"

npm run build

echo "#####################################################"
echo "# JS files created"
echo "#####################################################"


echo "#####################################################"
echo "# Removing unwanted libraries in P1"
echo "#####################################################"
sudo rm -rf /var/www/html/application/libraries/WebAuthn
sudo rm -rf /var/www/html/application/simplesamlphp
echo "#####################################################"
echo "# Completed"
echo "#####################################################"