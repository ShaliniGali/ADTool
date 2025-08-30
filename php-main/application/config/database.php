<?php
defined('BASEPATH') || exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Database Configuration for Docker Environment
|--------------------------------------------------------------------------
| This is a simplified configuration for the Docker setup that matches
| the original application's database group structure
|
*/

$active_group = 'socom_guardian_users';
$query_builder = TRUE;

// Default connection (kept for compatibility)
$db['default'] = array(
    'dsn'          => '',
    'hostname'     => 'rhombus-mysql',
    'username'     => 'rhombus_user',
    'password'     => 'rhombus_password',
    'database'     => 'rhombus_db',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => TRUE,
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);

// SOCOM guardian users connection (what the app actually expects)
$db['socom_guardian_users'] = array(
    'dsn'          => '',
    'hostname'     => 'rhombus-mysql',
    'username'     => 'rhombus_user',
    'password'     => 'rhombus_password',
    'database'     => 'rhombus_db',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => TRUE,
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);

// SOCOM_UI database connection
$db['socom_ui'] = array(
    'dsn'          => '',
    'hostname'     => 'rhombus-mysql',
    'username'     => 'rhombus_user',
    'password'     => 'rhombus_password',
    'database'     => 'SOCOM_UI',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => TRUE,
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);

// Rhombus database connection (what the app is looking for)
$db['rhombus_db'] = array(
    'dsn'          => '',
    'hostname'     => 'rhombus-mysql',
    'username'     => 'rhombus_user',
    'password'     => 'rhombus_password',
    'database'     => 'rhombus_db',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => TRUE,
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);
