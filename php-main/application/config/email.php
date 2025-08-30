<?php

$config['protocol'] = 'smtp';
$config['smtp_host'] = RHOMBUS_SMTP_HOST;
$config['smtp_port'] = RHOMBUS_SMTP_PORT;
$config['smtp_user'] = RHOMBUS_SMTP_USER;
$config['smtp_pass'] = RHOMBUS_SMTP_PASS;
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
$config['smtp_keepalive'] = FALSE;
$config['useragent'] = 'Codeignitor';
$config['charset'] = 'iso-8859-1';
$config['mailtype'] = 'html';
$config['priority'] = '1'; //1,2,3,4,5 - 1 highest
$config['dsn'] = TRUE;
$config['validate'] = TRUE;
