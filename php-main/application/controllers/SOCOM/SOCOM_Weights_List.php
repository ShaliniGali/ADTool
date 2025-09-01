<?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_Weights_List extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';

    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_Weights_List_model');
        $this->load->model('DBs'); // Added for SOCOM_UI database access
        $this->load->model('DB_ind_model'); // Added for CSRF validation in save_weights
        
        // Set up session data if not exists (for development only)
        if (ENVIRONMENT === 'development' && !$this->session->userdata('logged_in')) {
            $this->session->set_userdata('logged_in', [
                'id' => 1,
                'email' => 'test@example.com',
                'name' => 'Test User',
                'account_type' => 'USER'
            ]);
        }
    }

    // --------------------------------------------------------------------

    /**
     *
     *
     *
     */
    public function get_data(){
        $http_status = 200;
        $response = array('data'=>$this->SOCOM_Weights_List_model->get_criteria_weights_table());
        if (empty($response['data'])) {
            $response['status'] = 'Error User Weights not found';
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($response));
    }

    // --------------------------------------------------------------------

    /**
     * Load the score handsontable for the corresponding criteria.
     *
     * @param	Integer		$weight_id
     * @return json $weight_table
     */
    public function get_weight($weight_id)
    {
        $weight_table = [];
        $weight_table['readOnly'] = false;
        $weight_table['licenseKey'] = RHOMBUS_HANDSONTABLE_LICENSE;
        $weight_table['rowHeaders'] = '';
        $weight_table['colHeaders'] = [
            'Criteria',
            'Weight'
        ];

        $tableData = $this->SOCOM_Weights_List_model->get_saved_weight_data($weight_id);
        $weight_table['title'] = $this->SOCOM_Weights_List_model->get_data($weight_id)['data'][0]['NAME'] ?? null;
        
        $output = [
            'guidance' => array_merge($weight_table, ['id' => 'weight-guidance-div', 'tableData' => $tableData['guidance']]),
            'pom' => array_merge($weight_table, ['id' => 'weight-pom-div', 'tableData' => $tableData['pom']]),
        ];

        $this->output
                ->set_status_header(200)
                ->set_content_type(self::CONTENT_TYPE_JSON)
                ->set_output(json_encode($output));
    }
    
    // --------------------------------------------------------------------

    /**
     * Saves score from criteria list view. Called by clicking the save button.
     */
    public function save_weights()
    {
        // Validate post data.
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $weight_data = json_decode($post_data['weight_data'], true);
        $weight_id = (int)$post_data['weight_id'];
        if ($weight_id === 0) {
            $weight_id = null;
        }

        $response = ['status' => 'success', 'message' => ''];
        $http_status = 200;

        if (isset($weight_data['guidance'], $weight_data['pom'])) {
            $check_weights = function($single_weight) {
                $result = false;
                $weighted_score_values = array_map(function($val) {
                    return floatval($val);
                }, array_column($single_weight, 'weight'));
                $weighted_score = 0.0;
                foreach ($weighted_score_values as $val) {
                    $weighted_score = bcadd($weighted_score, $val, 2);
                }
                if ($weighted_score === "1.00") {
                    $result = true;
                }

                return $result;
            };

            $guidance = true;
            $pom = true;
            if (
                ($guidance = $check_weights($weight_data['guidance'])) &&
                ($pom = $check_weights($weight_data['pom']))
            ) {
                $this->SOCOM_Weights_List_model->save_weight_score_data($weight_id, $weight_data);
            } else {
                $http_status = 406;
                $response['status'] = 'error';
                $text = (!$guidance && !$pom) ? ' both Guidance and POM' : ((!$guidance) ? ' Guidance' : 'POM');
                $response['message'] = sprintf('Weight value sum is not equal to 1.00 for %s', $text);
            }
        }

        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
}

