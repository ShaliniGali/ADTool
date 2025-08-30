<?php

/**
 * Project Specific Constants
 */

 if (!getenv('Unit_test_indicator') && DEPLOYMENT_ENVIRONMENT !== 'NIPR') {
	$products[0]['host_name'] = getenv('CI_PRODUCTS_0_HOST');
	$products[0]['user_name'] = getenv('CI_PRODUCTS_0_USERNAME');
	$products[0]['password'] = getenv('CI_PRODUCTS_0_PASSWORD');

	defined('H_N_CREDENTIALS') || define('H_N_CREDENTIALS', getenv('CI_CREDENTIALS_HOST'));
	defined('U_N_CREDENTIALS') || define('U_N_CREDENTIALS', getenv('CI_CREDENTIALS_USERNAME'));
	defined('P_W_CREDENTIALS') || define('P_W_CREDENTIALS', getenv('CI_CREDENTIALS_PASSWORD'));
	defined('PORT_CREDENTIALS') || define('PORT_CREDENTIALS', 3306);

	for($p = 0; $p < count($products); $p++) {
		defined('H_N_PRODUCTS_'.$p) OR define('H_N_PRODUCTS_'.$p, $products[$p]['host_name']);
		defined('U_N_PRODUCTS_'.$p) OR define('U_N_PRODUCTS_'.$p, $products[$p]['user_name']);
		defined('P_W_PRODUCTS_'.$p) OR define('P_W_PRODUCTS_'.$p, $products[$p]['password']);
		defined('PORT_PRODUCTS_'.$p) OR define('PORT_PRODUCTS_'.$p, 3306);
	}
}

defined('LOGIN_ROUTE') || define('LOGIN_ROUTE', 'Login/index');

defined('USER_TYPE_ADMIN') || define('USER_TYPE_ADMIN', 'ADMIN');
defined('USER_TYPE_MODERATOR') || define('USER_TYPE_MODERATOR', 'MODERATOR');
defined('USER_TYPE_USER') || define('USER_TYPE_USER', 'USER');
defined('SOCOM_TAG') || define('SOCOM_TAG', getenv('SOCOM_TAG'));

defined('SOCOM_ADMIN_USERS') || define('SOCOM_ADMIN_USERS', explode('::::', getenv('SOCOM_ADMIN_USERS')));

defined('SOCOM_S3_BUCKET') OR define('SOCOM_S3_BUCKET', getenv('SOCOM_S3_BUCKET'));
defined('SOCOM_S3_REGION') or define('SOCOM_S3_REGION', getenv('SOCOM_S3_REGION'));


defined('SOCOM_AOAD_DELETED_COMMENT') OR define('SOCOM_AOAD_DELETED_COMMENT', 1);
defined('SOCOM_AOAD_DELETED_DROPDOWN') OR define('SOCOM_AOAD_DELETED_DROPDOWN', 2);
defined('SOCOM_AOAD_DELETED_BOTH') OR define('SOCOM_AOAD_DELETED_BOTH', 3);


defined('SOCOM_JWT_SECRET_KEY') OR define('SOCOM_JWT_SECRET_KEY', getenv('SOCOM_JWT_SECRET_KEY'));
defined('SOCOM_JWT_ALGORITHM') OR define('SOCOM_JWT_ALGORITHM', getenv('SOCOM_JWT_ALGORITHM'));