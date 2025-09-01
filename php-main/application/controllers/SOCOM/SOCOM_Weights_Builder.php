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
                
                // Load required models and libraries
                $this->load->model('SOCOM_Weights_model');
                $this->load->model('SOCOM_Storm_model');
                $this->load->model('SOCOM_Cycle_Management_model');
                $this->load->model('DB_ind_model'); // Added for CSRF validation in save_weights
                $this->load->library('SOCOM/RBAC_Users', null, 'rbac_users');
                
                // Set up session data if not exists (for development only)
                if (ENVIRONMENT === 'development' && !$this->session->userdata('logged_in')) {
                        $this->session->set_userdata('logged_in', [
                                'id' => 1,
                                'email' => 'test@example.com',
                                'name' => 'Test User',
                                'account_type' => 'USER'
                        ]);
                }
                
                // Check authentication with fallback (disabled in development)
                if (ENVIRONMENT !== 'development') {
                        try {
                                if(auth_coa_role_guest()!= null || auth_coa_role_restricted()!= null) {
                                        $http_status = 403;
                                        $response['status'] = "Unauthorized user, access denied.";
                                        show_error($response['status'], $http_status);
                                }
                        } catch (Exception $e) {
                                log_message('error', 'Auth check failed: ' . $e->getMessage());
                        }
                } else {
                        log_message('info', 'Authentication check disabled in development environment');
                }
                
                // Get criteria with fallback
                try {
                        $criteria_name_id = get_criteria_name_id();
                        $this->criteria = array_column($this->SOCOM_Cycle_Management_model->get_terms_by_criteria_id($criteria_name_id), 'CRITERIA_TERM');
                } catch (Exception $e) {
                        log_message('error', 'Criteria setup failed: ' . $e->getMessage());
                        $this->criteria = [];
                }
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
                $view_data['criteria'] = []; // Initialize criteria array
                
                // If criteria is empty, provide some default criteria for testing (development only)
                if (empty($this->criteria) && ENVIRONMENT === 'development') {
                        $view_data['criteria'] = [
                                ['CRITERIA' => 'Cost', 'WEIGHT' => 0.0],
                                ['CRITERIA' => 'Performance', 'WEIGHT' => 0.0],
                                ['CRITERIA' => 'Risk', 'WEIGHT' => 0.0],
                                ['CRITERIA' => 'Schedule', 'WEIGHT' => 0.0]
                        ];
                } else {
                        foreach ($this->criteria as $criteria) {
                                $view_data['criteria'][] = [
                                        'CRITERIA' => $criteria,
                                        'WEIGHT' => 0.0
                                ];
                        }
                }
                
                // Try to get active cycle with criteria, fallback to empty array if it fails
                try {
                        $get_active_cycle_with_criteria = $this->SOCOM_Cycle_Management_model->get_active_cycle_with_criteria();
                } catch (Exception $e) {
                        $get_active_cycle_with_criteria = [];
                }
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
