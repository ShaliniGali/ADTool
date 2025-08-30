<?php
defined('BASEPATH') ||  exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Score extends CI_Controller {
        // Initializing constants
        protected const CONTENT_TYPE_JSON = 'application/json';

        public function __construct(){
            parent::__construct();

            $this->load->model('SOCOM_model', 'socom_model');
            $this->load->model('SOCOM_Score_model', 'socom_score_model');
            $this->load->model('SOCOM_Cycle_Management_model', 'socom_cycle_management_model');
        }

        public function get() {
            $http_status = 404;
            $msg = 'Option Score could not be found';
            
            $post_check = $this->DB_ind_model->validate_post($this->input->post());
            $post_data = $post_check['post_data'];
            
            $score_id = (int)$post_data['score_id'];
            $program_id = $post_data['program_id'];
            $type_of_coa = TypeOfCoa::tryFrom($post_data['type_of_coa']);
            $user_id = (int)$this->session->userdata['logged_in']['id'];
            $criteria_name_id = get_criteria_name_id();

            $result = [];
            $status = true;
            $result = $this->socom_score_model->get_score($score_id, $program_id, $criteria_name_id, $user_id, $type_of_coa);
            if ($result !== false) {
                $http_status = 200;
                $msg = sprintf('Program Score %s is ready for editing', $result['NAME']);
            } else {
                $status = false;
            }

            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode([
                    'status' => $status,
                    'message' => $msg,
                    'data' => $result
                ]));
        }

        public function edit() {
            $http_status = 406;
            
            $post_check = $this->DB_ind_model->validate_post($this->input->post());
            $post_data = $post_check['post_data'];
            
            $params = [];
            
            covert_score_data_keys($post_data['score_data']);
            $msg = $this->_validate_post($post_data, $params);
            if (
                !isset($post_data['score_id']) ||
                !ctype_digit($post_data['score_id'])
            ) {
                $msg = 'Unable to save Option Score edits.';
            }

            $params['score_id'] = (int)$post_data['score_id'];

            $params['criteria_name_id'] = get_criteria_name_id();

            if ($msg === '') {
                $result = $this->socom_score_model->save_score($params, true);
                if ($result === true) {
                    $http_status = 200;
                    $status = true;
                    $msg = 'Program Score has been updated';
                } else {
                    $status = false;
                    $msg = 'Program Score could not be updated';
                }
            } else {
                $status = false;
            }

            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode([
                    'status' => $status,
                    'message' => $msg
                ]));
        }

        public function create() {
            $http_status = 406;
            
            $post_check = $this->DB_ind_model->validate_post($this->input->post());
            $post_data = $post_check['post_data'];
            
            $params = [];

            covert_score_data_keys($post_data['score_data']);
            $msg = $this->_validate_post($post_data, $params);
            
            if ($msg === '') {
                $params['criteria_name_id'] = get_criteria_name_id();

                $result = $this->socom_score_model->save_score($params);
                if ($result !== false) {
                    $http_status = 201;
                    $status = true;
                    $msg = 'Program Score has been created';
                } else {
                    $status = false;
                    $msg = 'Program Score could not be created';
                }
            } else {
                $status = false;
            }

            $this->output
                ->set_status_header($http_status)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode([
                    'status' => $status,
                    'message' => $msg
                ]));
        }

        private function _validate_post($post_data, &$params = []) {
            $fields = [
                'program_id' => [
                    'name' => 'Program',
                    'validations' => 'required|integer',
                    'messages' => [
                        'required' => 'Uknown Error, please refresh progresm page and try again',
                        'integer' => 'Uknown Error, please refresh progresm page and try again'
                        ]
                ],
                'score_name' => [
                    'name' => 'Score Name',
                    'validations' => 'required|max_length[100]',
                    'messages' => [
                        'required' => 'Score Name is required',
                        'max_length[100]' => 'Score Name must be less than 100 characters'
                    ]
                ],
                'score_description' => [
                    'name' => 'Score Description',
                    'validations' => 'required|max_length[1024]',
                    'messages' => [
                        'required' => 'Score Description is required',
                        'max_length[1024]' => 'Score Description must be less than 1024 characters'
                    ]
                ],
                'iss_extract' => [
                    'name' => 'Optimization Type',
                    'validations' => 'required|in_list[ISS_EXTRACT,RC_T]',
                    'messages' => [
                        'required' => 'Missing Optimization Type',
                        'in_list[ISS_EXTRACT,RC_T]' => 'Optimization Type must be ISS_EXTRACT or RC_T'
                    ]
                ]
            ];

            $status = '';
            
            $criteria = array_column($this->socom_model->get_option_criteria_names(), 'CRITERIA');
            
            $keys = array_keys($post_data['score_data']);
            if (
                count($post_data['score_data']) !== count($criteria) || 
                $keys !== $criteria
            ) {
                $status = 'All Score Data was not in line with the Criteria, refresh the page and try again';
            }
            unset($criteria);

            $score_data = [];

            if ($status === '') {
                foreach ($post_data['score_data'] as $criteria => $data) {
                    if (
                        !is_numeric($data) ||
                        $data > 100 ||
                        $data <= 0
                    ) {
                        $status = 'All Score Data must be greater than 0 and less than 100';
                        break;
                    } else {
                        $score_data[$criteria] = $data;
                    }
                }
                unset($criteria, $data);
            }

            $params['score_data'] = $score_data;
            
            //ksort($params['score_data']);

            if ($status === '') {
                $this->form_validation->set_data($params);
    
                $valid = $this->form_validation->run();
                if ($valid === false) {
                    foreach (array_keys($fields) as $field) {
                        $status = form_error($field, null, null);
                        if ($status !== '') { 
                            break;
                        }
                    }
                }
            }
            $params['score_name'] = $post_data['score_name'];
            $params['score_description'] = $post_data['score_description'];
            $params['program_id'] = $post_data['program_id'];

            $params['user_id'] = (int)$this->session->userdata['logged_in']['id'];

            $params['type_of_coa'] = TypeOfCoa::from($post_data['iss_extract']);

            return $status;
        }
}
