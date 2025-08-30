<?php

/**
 * 
 * created june 15 2021 by lea
 * creates a file of the current structure of the databases for this UI
 * 
 */

defined('BASEPATH') || exit('No direct script access allowed');
#[AllowDynamicProperties]
class RB_Auditing
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    function database()
    {
        $info = [];
        $this->ci->load->dbutil();
        $dbs = $this->ci->dbutil->list_databases();
        require(APPPATH . 'config/database.php');
        $dbs = array_keys($db);

        foreach ($dbs as $database) {
            $currDb = $this->ci->load->database($database, TRUE);
            echo ($database . '<br>');
            $info[$currDb->database] = [];
            $tables = $currDb->list_tables(true);

            foreach ($tables as $table) {
                $info[$currDb->database][$table] = [];
                $fields = $currDb->field_data($table);

                foreach ($fields as $field) {
                    $info[$currDb->database][$table][$field->name] = ['name' => $field->name, 'type' => $field->type];
                }
            }
        }
        
        $today = glob(APPPATH . '/libraries/RB_database_audits/datatable_audit_' . date("Y-m-d") . '*');
        $myfile = fopen(APPPATH . "/libraries/RB_database_audits/datatable_audit_" . date("Y-m-d") . "_V" . (count($today) + 1) . ".json", "w") or die("Unable to open file!");
        fwrite($myfile, json_encode($info, JSON_PRETTY_PRINT));
        fclose($myfile);
        echo "/libraries/RB_database_audits/datatable_audit_" . date("Y-m-d") . "_V" . (count($today) + 1) . ".json";
        // var_dump($info);
    }

    function display($num)
    {
        if ($num < 1) {
            echo 'please pick a number greater than 0, e.g. comare the most recent to the nth previous audit';
        }
        $files = glob(APPPATH . '/libraries/RB_database_audits/datatable_audit_*');
        if (!isset($files[count($files) - 1 - $num])) {
            echo $num . ' previous audits do not exits';
            exit();
        }
        $file1 = $files[count($files) - 1];
        $file2 = $files[count($files) - 1 - $num];
        $current = json_decode(file_get_contents($file1), true);
        $previous = json_decode(file_get_contents($file2), true);

        $differences = '<table style="width:100%"><thead><th>Previous</th><th>Current</th><th>Change</th></thead>';
        foreach ($current as $dbName => $db) {
            if (!isset($previous[$dbName])) {
                $differences .= '<tr><td></td><td>schema: ' . $dbName . '</td><td>Schema Added</td><tr>';
            } else {
                foreach ($db as $tableName => $table) {
                    if (!isset($previous[$dbName][$tableName])) {
                        $differences .= '<tr><td></td><td>' . $dbName . '->' . $tableName . '</td><td>Table Added</td><tr>';
                    } else {
                        foreach ($table as $fieldName => $field) {
                            if (!isset($previous[$dbName][$tableName][$fieldName])) {
                                $differences .= '<tr><td></td><td>' . $dbName . '->' . $tableName . '->' . $fieldName . '</td><td>Column Added</td><tr>';
                            } else {
                                if ($previous[$dbName][$tableName][$fieldName]['type'] != $field['type']) {
                                    $differences .= '<tr><td>' . $dbName . '->' . $tableName . '->' . $fieldName . ':' . $previous[$dbName][$tableName][$fieldName]['type'] . '</td><td>' . $dbName . '->' . $tableName . '->' . $fieldName . ':' . $field['type'] . '</td><td>Column Type Changed</td><tr>';
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($previous as $dbName => $db) {
            if (!isset($current[$dbName])) {
                $differences .= '<tr><td>schema: ' . $dbName . '</td><td></td><td>Schema Removed</td><tr>';
            } else {
                foreach ($db as $tableName => $table) {
                    if (!isset($current[$dbName][$tableName])) {
                        $differences .= '<tr><td>' . $dbName . '->' . $tableName . '</td><td></td><td>Table Removed</td><tr>';
                    } else {
                        foreach ($table as $fieldName => $field) {
                            if (!isset($current[$dbName][$tableName][$fieldName])) {
                                $differences .= '<tr><td>' . $dbName . '->' . $tableName . '->' . $fieldName . '</td><td></td><td>Column Removed</td><tr>';
                            }
                        }
                    }
                }
            }
        }
        $differences .= '</table>';
        echo $differences;
    }
}
