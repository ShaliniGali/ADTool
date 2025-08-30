#!/bin/bash

#######################
# Configure .env file
#######################

sed -i "s#{BASE_URL}#"$BASE_URL"#g" /code/.env
sed -i "s#{BASE_PORT}#"$BASE_PORT"#g" /code/.env

sed -i "s#{FACS_URL}#"$FACS_URL"#g" /code/.env
sed -i "s#{FACS_PORT}#"$FACS_PORT"#g" /code/.env
sed -i "s#{FACS_KEY}#"$FACS_KEY"#g" /code/.env
sed -i "s#{FACS_NAME}#"$FACS_NAME"#g" /code/.env

sed -i "s#{DB_HOST}#"$DB_HOST"#g" /code/.env
sed -i "s#{DB_USER}#"$DB_USER"#g" /code/.env
sed -i "s#{DB_PASS}#"$DB_PASS"#g" /code/.env
sed -i "s#{DB_NAME}#"$DB_NAME"#g" /code/.env

sed -i "s#{P1_DEPLOYMENT}#"$P1_DEPLOYMENT"#g" /code/.env

php-fpm
