<?php
defined('BASEPATH') || exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['display_override'][] = array(
  'class' => '',
  'function' => 'compress',
  'filename' => 'compress.php',
  'filepath' => 'hooks'
);

$hook['post_controller_constructor'][] = array(
  'class' => 'P1DbCheck',
  'function' => 'checkStatus',
  'filename' => 'P1DbCheck.php',
  'filepath' => 'hooks',
);

// DEVELOPMENT MODE: Authentication hooks disabled
// $hook['post_controller_constructor'][] = array(
//   'class' => 'LoginStatus',
//   'function' => 'checkStatus',
//   'filename' => 'LoginStatus.php',
//   'filepath' => 'hooks',
// );

// $hook['post_controller_constructor'][] = array(
//   'class' => 'RoleAccess',
//   'function' => 'checkRoleStatus',
//   'filename' => 'RoleAccess.php',
//   'filepath' => 'hooks',
// );

// DEVELOPMENT MODE: Session verification disabled
// $hook['post_controller'] = array(
//   'class' => 'LoginStatus',
//   'function' => 'verifySession',
//   'filename' => 'LoginStatus.php',
//   'filepath' => 'hooks',
// );

// DEVELOPMENT MODE: Site user check disabled
// $hook['post_controller_constructor'][] = array(
//   'class'    => 'checkSiteUser',
//   'function' => 'isSiteUser',
//   'filename' => 'checkSiteUser.php',
//   'filepath' => 'hooks'
// );

if (
  RHOMBUS_SSO_KEYCLOAK=='TRUE' || 
  RHOMBUS_SSO_PLATFORM_ONE === 'TRUE'
){
  $hook['post_controller'] = array(
    'class' => 'LoginStatus',
    'function' => 'checkStatusTile',
    'filename' => 'LoginStatus.php',
    'filepath' => 'hooks',
  );
}



// DEVELOPMENT MODE: SOCOM authentication disabled
// $hook['post_controller_constructor'][] = array(
//   'class'    => 'SetSOCOMAuth',
//   'function' => 'setUserAuth',
//   'filename' => 'SetSOCOMAuth.php',
//   'filepath' => 'hooks'
// );

$hook['post_controller_constructor'][] = array(
  'class' => 'SetDynamicYear',
  'function' => 'fetchDynamicYear',
  'filename' => 'SetDynamicYear.php',
  'filepath' => 'hooks',
);