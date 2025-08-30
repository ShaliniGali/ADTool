<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<?= json_encode(['status' => false, 'type' => $heading, 'message' => $message]); ?>