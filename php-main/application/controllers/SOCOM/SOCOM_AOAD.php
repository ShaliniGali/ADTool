

            <?php
defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class SOCOM_AOAD extends CI_Controller
{
    protected const CONTENT_TYPE_JSON = 'application/json';
    protected const DROP_DOWN_CHOICES = [
        'zbt_summary' => [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ],
        'issue' => [
            'Approve',
            'Approve at Scale',
            'Disapprove'
        ]
    ];
    
    public function __construct() {
        parent::__construct();
        $this->load->model('SOCOM_model');
        $this->load->model('SOCOM_AOAD_model');
        $this->load->model('SOCOM_Event_Funding_Lines_model');
        $this->load->model('SOCOM_Program_model');
        $this->load->model('SOCOM_Dynamic_Year_model');
    }

    public function save_ao_ad_dropdown($page, $ao_ad) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $value = trim($post_data['value']) ?? '';
        $type = trim($post_data['type']) ?? '';
        $event_id = trim($post_data['event_id']) ?? '';

        $result['status'] = false;
        if (empty($value)) {
            $result['message'] = 'Empty value is not allowed';
        } elseif ($type === 'dropdown') {
            if ($ao_ad === 'ao' && in_array($value, self::DROP_DOWN_CHOICES[$page], true)) {
                $result['status'] = true;
                $save = ['AO_RECOMENDATION' => $value];
            } elseif ($ao_ad === 'ad' && in_array($value, self::DROP_DOWN_CHOICES[$page], true)) {
                $result['status'] = true;
                $save = ['AD_RECOMENDATION' => $value];
            } elseif ($ao_ad === 'final_ad' && in_array($value, self::DROP_DOWN_CHOICES[$page], true)) {
                $result['status'] = true;
                $save = ['AD_RECOMENDATION' => $value];
            }
        }

        try {
            if ($result['status'] === true) {
                if ($ao_ad === 'ao' || $ao_ad === 'ad') {
                    $result['status'] = $this->SOCOM_AOAD_model->save_ao_ad_data($save, $event_id, $page, $ao_ad);
                } elseif ($ao_ad === 'final_ad') {
                    $result['status'] = $this->SOCOM_AOAD_model->save_final_ad_data($save, $event_id, $page, $ao_ad);
                }

                if ($ao_ad === 'ao') {
                    $result['dropdown'] = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_BOTH);
                    $result['comments'] = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_BOTH);
                    //$result['comments'] = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_id, $page, $user_id = null);
                } elseif ($ao_ad === 'ad') {
                    $result['dropdown'] = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_BOTH);
                    $result['comments'] = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_BOTH);
                    //$result['comments'] = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_id, $page, $user_id = null);
                } elseif ($ao_ad === 'final_ad') {
                    $result['comments'] = $this->SOCOM_AOAD_model->get_final_ad_by_event_id_user_id($event_id, $page, $user_id = null, false);
                    if ($result['comments'] !== false) {
                        $result['comments'] = [$result['comments']];
                    } else {
                        $result['comments'] = [];
                    }
                }
            }
        } catch(ErrorException $e) {
            $result['status'] = false;
            $result['message'] = 'Unable to save user recommendation';
        }

        $this->output
            ->set_status_header($result['status'] === true ? 200 : 406)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result, JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    public function save_ao_ad_comment($page, $ao_ad) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $value = trim($post_data['value']) ?? '';
        $event_id = trim($post_data['event_id']) ?? '';

        $result['status'] = false;
        if (empty($value)) {
            $result['message'] = 'Empty value is not allowed';
        } elseif ($ao_ad === 'ao') {
            $result['status'] = true;
            $save = ['AO_COMMENT' => $value];
        } elseif ($ao_ad === 'ad') {
            $result['status'] = true;
            $save = ['AD_COMMENT' => $value];
        }

        try {
            if ($result['status'] === true) {
                $result['status'] = $this->SOCOM_AOAD_model->save_ao_ad_data($save, $event_id, $page, $ao_ad);

                if ($ao_ad === 'ao') {
                    $result['comments'] = $this->SOCOM_AOAD_model->get_ao_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_COMMENT);
                } elseif ($ao_ad = 'ad') {
                    $result['comments'] = $this->SOCOM_AOAD_model->get_ad_by_event_id_user_id($event_id, $page, $user_id = null, SOCOM_AOAD_DELETED_COMMENT);
                }
            }
        } catch (ErrorException $e) {
            $result['status'] = false;
            $result['message'] = 'Unable to save user comment';
        }

        $this->output
            ->set_status_header($result['status'] === true ? 200 : 406)
            ->set_content_type(self::CONTENT_TYPE_JSON)
            ->set_output(json_encode($result, JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    private function save_final_ad_approve_table($page, $event_id, $funding_lines) {
        $pom_year_result = $this->SOCOM_Dynamic_Year_model->getCurrentPom();
        $pom_position = $pom_year_result['LATEST_POSITION'];

        if ($page  == 'issue') {
            [$year, $year_list] = get_years_issue_summary();
        } else {
            [$year, $year_list] = get_years_zbt_summary();
        }

        return $this->SOCOM_AOAD_model->save_final_ad_approve_table(
            $page, $event_id, $pom_position, $year_list, $funding_lines
        );
    }

    public function save_final_ad_table_data($page, $event_id) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());

        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            return;
        }

        $post_data = $data_check['post_data'];
        $funding_lines = json_decode($post_data['approve_table_data'], true) ?? [];
        $event_id = urldecode($event_id);

        // make sure event is Approve at Scale
        $event_status = $this->SOCOM_AOAD_model->get_event_status($page, $event_id);
        if ($event_status === 'Approve at Scale') {
            // save granted table into backend
            $result = $this->save_final_ad_approve_table($page, $event_id, $funding_lines);
        } else {
            $result = false;
        }

        if ($result) {
            $status = 200;
            $message = 'Successfully saved';
        }
        else {
            $status = 400;
            $message = 'Failed to save';
        }

        $this->output->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode(['message' => $message]))
            ->_display();
        exit();
        
    }

    public function get_final_ad_table_data($page, $event_name) {
        $granted_table_data = $this->SOCOM_AOAD_model->get_final_ad_granted_data($page, $event_name);

        $response = [
            'tableData' => $granted_table_data['APPROVE_TABLE'],
            'all_years' => json_decode($granted_table_data['YEAR_LIST'], true),
        ];

        $this->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($response))
            ->_display();
        exit();
    }
    public function delete_item_comment($page, $ao_ad) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            return;
        }
        $post_data = $data_check['post_data'];
        $id = $post_data['id'] ?? null;
        $event_id = $post_data['event_id'] ?? null;
        
        if (empty($id)) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'ID is missing.']);
            return;
        }
        $field = null;
        if ($ao_ad === 'ao') {
            $field = 'AO_COMMENT';
        } elseif ($ao_ad === 'ad') {
            $field = 'AD_COMMENT';
        } else {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Invalid AO/AD type provided.']);
            return;
        }
        try {
            $result = $this->SOCOM_AOAD_model->delete_ao_ad_item($event_id, $field, $page, $ao_ad, SOCOM_AOAD_DELETED_COMMENT);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete item.']);
            }
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function delete_item_dropdown($page, $ao_ad) {
        $data_check = $this->DB_ind_model->validate_post($this->input->post());
        if (!$data_check['result']) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            return;
        }
        $post_data = $data_check['post_data'];
        $id = $post_data['id'] ?? null;
        $event_id = $post_data['event_id'] ?? null;

        
        if (empty($id)) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'ID is missing.']);
            return;
        }
        $field = null;
        if ($ao_ad === 'ao') {
            $field = 'AO_RECOMENDATION';
        } elseif ($ao_ad === 'ad' || $ao_ad === 'final_ad') {
            $field = 'AD_RECOMENDATION';
        } else {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'message' => 'Invalid AO/AD type provided.']);
            return;
        }
        try {
            $result = $this->SOCOM_AOAD_model->delete_ao_ad_item($event_id, $field, $page, $ao_ad, SOCOM_AOAD_DELETED_DROPDOWN);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete item.']);
            }
        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}