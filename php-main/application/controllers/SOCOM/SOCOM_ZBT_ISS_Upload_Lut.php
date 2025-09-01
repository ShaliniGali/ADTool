<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_ZBT_ISS_Upload_Lut extends CI_Controller
{

    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct()
    {
        parent::__construct();
        

        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->load->model('SOCOM_DT_Editor_model');
        $this->load->model('SOCOM_Git_Data_model');
        $this->load->model('SOCOM_Database_Upload_model');
        $this->load->model('SOCOM_Database_Upload_Metadata_model');
        $this->load->model('SOCOM_ZBT_ISS_Upload_Lut_model');
        $this->load->model('Login_model');
    }

    public function get_all_dropdowns()
    {
        $this->output->set_content_type(self::CONTENT_TYPE_JSON);
        
        try {
            $dropdownData = [
                'ExecutionManager'       => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_execution_managers(),
                'POMSponsor'             => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_pom_sponsors(),
                'AssessmentArea'         => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_assessment_areas(),
                'ProgramGroup'           => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_program_groups(),
                'ProgramCode'            => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_program_codes(),
                'EOCCode'                => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_eoc_codes(),
                'ResourceCategoryCode'   => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_resource_category_codes(),
                'OSDPECode'              => $this->SOCOM_ZBT_ISS_Upload_Lut_model->get_osd_pe_codes(),
            ];
            $this->output->set_output(json_encode([
                'status' => 'success',
                'data' => $dropdownData,
            ]));
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            $this->output->set_output(json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]));
        }
    }
}