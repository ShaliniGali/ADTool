<?php
defined('BASEPATH') || exit('No direct script access allowed');
require_once('SOCOM_HOME.php');
#[AllowDynamicProperties]
class SOCOM_Document_Export extends CI_Controller {
    protected const APPLICATION_JSON = 'application/json';

    public function __construct(){
        parent::__construct();
        $this->load->model('SOCOM_Program_model');
    }

    public function export() {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        $post_data = $data_check['post_data'];
        $ass_area = $post_data['ass-area'];
        $program = $post_data['program'];
        $use_iss_extract = filter_var($post_data['use_iss_extract'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $type_of_coa = $use_iss_extract ? 'ISS_EXTRACT' : 'ISS';

        $content = [
            'assessment_area_code' => $ass_area ,
            'program_group' => $program,
            'cycle_id' => get_cycle_id(),
            'TYPE_OF_COA' => $type_of_coa
        ];

        $response = php_api_call(
            'POST',
            'Content-Type: ' . self::APPLICATION_JSON,
            json_encode($content),
            RHOMBUS_PYTHON_URL.'/socom/download/scores/excel'
        );
        $filename = sprintf('program_export_%s.xlsx', date('Y_m_d_h_i_s'));

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: inline; filename="'.$filename.'"');
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
    
        echo $response;
        exit();
    }
    
    
}