<?php
defined('BASEPATH')|| exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Memcached settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|	See: https://codeigniter.com/user_guide/libraries/caching.html#memcached
|
*/
$config = array(
	'default' => array(
		'hostname' => 'ec2-52-61-203-71.us-gov-west-1.compute.amazonaws.com',
		'port'     => '11211',
		'weight'   => '1',
	),
);
