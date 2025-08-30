<?php
defined('BASEPATH') || exit('No direct script access allowed');

/*
Define your databases for unit testing purposes here
You can define your database variables in your application/tests/.env file
Important note to use the same env variable that the original env is using for your database name.
*/
$active_group = getenv('SOCOM_guardian_users');// database name
$query_builder = TRUE;

$db[getenv('SOCOM_guardian_users')] = array(
	'dsn'	=> '',
	'hostname' => getenv('dummy_db_hostname'),
	'username' => getenv('dummy_db_username'),
	'port' => '',
	'password' => getenv('dummy_db_password'),
	'database' => getenv('SOCOM_guardian_users'),
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
