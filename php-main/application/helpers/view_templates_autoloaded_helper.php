<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('datatable_card')) {
    function datatable_card($columns = array(), $tableId = 'apiTable', $buttons = 2, $title = array('pageTitle', '. . .')) {
        $th = '';
        foreach($columns as $column) {
            $th .= '<th class = "th-hover">' . $column . '</th>';
        }
        while($buttons > 0) {
            $th .= '<th class = "th-hover buttonColumn"></th>';
            --$buttons;
        }
        return '
        <div class="px-3 py-4">
            <div class="pt-4">
                <h4 id = "' . $title[0] . '" class="float-left">' . $title[1] . '</h4>
            </div>
            <table id = "' . $tableId . '" class = "table table-dark table-borderless table-striped table-hover">
                <thead>
                    <tr>' . $th . '</tr>
                </thead>
            </table>
        </div>
        ';
    }
}