<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Weights_Builder extends CI_Controller {
        protected const APPLICATION_JSON = 'application/json';

        /**
         * Criteria constructor
         */
        public function __construct()
        {
                parent::__construct();
                if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                        $http_status = 403;
                        $response['status'] = "Unauthorized user, access denied.";
                        show_error($response['status'], $http_status);
                }

                $this->load->model('SOCOM_Weights_model');
                $this->load->model('SOCOM_Storm_model');

                $criteria_name_id = get_criteria_name_id();

                $this->criteria = array_column($this->SOCOM_Cycle_Management_model->get_terms_by_criteria_id($criteria_name_id), 'CRITERIA_TERM');
        }

        public function create_weights()
       {
                $page_data = [];
                $page_data['page_title'] = 'Weights Creator';
                $page_data['page_tab'] = 'Weights Creator';
                $page_data['page_navbar'] = true;
                $page_data['page_specific_css'] = [
                        'carbon-light-dark-theme.css',
                        'select2.css',
                        'datatables.css',
                        'jquery.dataTables.min.css',
                        'responsive.dataTables.min.css',
                        'SOCOM/create.css',
                        'ion.rangeSlider.min.css',
                        'handsontable.min.css'
                ];
                $page_data['compression_name']  = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');

                $is_guest = $this->rbac_users->is_guest();
                $is_restricted = $this->rbac_users->is_restricted();
                $page_data['is_guest'] = $is_guest;
                $page_data['is_restricted'] = $is_restricted;
                
                $this->load->view('templates/header_view', $page_data);
                
                $view_data = [];
                foreach ($this->criteria as $criteria) {
                        $view_data['view_data']['criteria'][] = [
                                'CRITERIA' => $criteria,
                                'WEIGHT' => 0.0
                        ];
                }
                $get_active_cycle_with_criteria = $this->DBs->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
                $view_data['get_active_cycle_with_criteria'] = $get_active_cycle_with_criteria;
                $this->load->view('SOCOM/weights/weight_view', $view_data);
                $this->load->view('templates/close_view');
        }

        public function save_weights() {
                $data_check = $this->DB_ind_model->validate_post($this->input->post());
                $response['status'] = false;
                $response['message'] = 'Unable to create weight, check logs';
                $http_status = 500;

                if ($data_check['result']) {
                        
                        $title = $data_check['post_data']['title'] ?? '';
                        $session = [
                                'guidance' => $data_check['post_data']['guidance']['SESSION'] ?? [],
                                'pom' => $data_check['post_data']['pom']['SESSION'] ?? []
                        ];

                        $description = [
                                'guidance' => $data_check['post_data']['guidance']['DESCRIPTION'] ?? '',
                                'pom' => $data_check['post_data']['pom']['DESCRIPTION'] ?? ''
                        ];
                        
                        covert_score_data_keys($session['guidance']);
                        covert_score_data_keys($session['pom']);

                        try {
                                $result = $this->SOCOM_Weights_model->create_weights($title, $session, $description, $this->criteria);

                                if ($result === true) {
                                        $response['status'] = true;
                                        $response['message'] = 'New Weight Created';
                                        $http_status = 201;
                                }
                        } catch (InvalidArgumentException $e) {
                                $response['status'] = false;
                                $response['message'] = $e->getMessage();
                                $http_status = 500;
                        }
                }

                $this->output
                        ->set_status_header($http_status)
                        ->set_content_type(self::APPLICATION_JSON)
                        ->set_output(json_encode($response));
        }

        // --------------------------------------------------------------------

        /**
        * @param	int	    $weight_id
        * @return	Array 
        */
        public function delete_weights($weight_id) 
        {
            if (!is_array($weight_id)) {
                $weight_id = [$weight_id];
            }

            for ($i = 0; $i < count($weight_id); $i++) {
                // add DELETED status in _DELETE_STATUS table
                $this->SOCOM_Weights_model->delete_user_weight($weight_id[$i]);
            }
        }
}
