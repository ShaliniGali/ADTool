<?php

defined('BASEPATH') || exit('No direct script access allowed');
class SOCOM_API_Upload_Notification extends CI_Controller {

    protected const CONTENT_TYPE_JSON = 'application/json';
    public function get_messages() {
        $user_id = (string) $this->session->userdata('logged_in')['id'];
        $limit = '10';

        $headers = [
            'accept: application/json',
            'Content-Type: application/json',
        ];
        $api_params = '';
        $api_endpoint = RHOMBUS_PYTHON_URL.'/stream/dt/notif/messages/'.$limit.'?user_id='.$user_id;

        $res = php_api_call(
            'GET',
            $headers,
            $api_params,
            $api_endpoint
        );

        echo json_encode($res);
    }

    public function acknowledge_message() {
        $user_id = (string) $this->session->userdata('logged_in')['id'];

        $this->form_validation->set_rules('message_id', 'Message ID', 'required');

        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        if ($this->form_validation->run() !== false && $data_check['result']) {

            $post_data = $data_check['post_data'];
            $message_id = $post_data['message_id'];

            $headers = [
                'accept: application/json',
                'Content-Type: application/json'

            ];
            $api_params = array();
            
            $api_params['user_id'] = $user_id;
            $api_params['message_ids'] = [$message_id];
    
            $api_endpoint = RHOMBUS_PYTHON_URL.'/stream/dt/notif/acknowledge';

            try {
                $res = php_api_call(
                    'POST',
                    $headers,
                    json_encode($api_params),
                    $api_endpoint
                );

                $http_status = 200;
            } catch (ErrorException $e) {
                $http_status = 500;
            }
        }

        if ($http_status === 500) {
            $output = json_encode([
                'messages' => ['Acknowledge unsuccessful'],
                'status' => false
            ]);
        } else {
            $http_status = 200;
            $output = json_encode([
                'status' => true,
                'messages' => ['Acknowledge successful']
                ]
            );
        }
    
            $this->output
            ->set_status_header($http_status)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($output));
    }
}