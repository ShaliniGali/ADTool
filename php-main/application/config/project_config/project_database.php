<?php

/**
 * Project Specific Database
 */

if(SHOW_SOCOM){
	$db[getenv('SOCOM_UI')] = array(
		'dsn'	=> '',
		'hostname' => H_N_PRODUCTS_0,
		'username' => U_N_PRODUCTS_0, 
		'port' => PORT_PRODUCTS_0,
		'password' => P_W_PRODUCTS_0,
		'database' => getenv('SOCOM_UI'),
		'dbdriver' => 'mysqli',
		'dbprefix' => '',
		'pconnect' => FALSE,
		'db_debug' => (ENVIRONMENT !== 'production'),
		'cache_on' => FALSE,
		'cachedir' => '',
		'char_set' => 'utf8',
		'dbcollat' => 'utf8_general_ci',
		'swap_pre' => '',
		'encrypt' => FALSE,
		'compress' => FALSE,
		'stricton' => FALSE,
		'failover' => array(),
		'save_queries' => FALSE
	);
}