<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('printout_message')) {
    function printout_message($icon,$message){
        $msg = '
        <div class="d-table w-100" style="height:50vh;">
            <div style="display: table-cell;vertical-align: middle;">
                     <div style="text-align: center">
                      '.$icon.'
                      '.$message.'
                     </div>
            </div>
        </div>
        ';
        return $msg;
    }
}