<?php

class SOCOM_Submit_Approve_model extends CI_Model
{
    protected $log_table = 'USR_DT_SUBMIT_APPROVE_ACTIONS';

    public function save_submit(int $map_id, string $description): bool
    {   
        $this->DBs->SOCOM_UI->trans_start();

        $user_id = (int) $this->session->userdata("logged_in")["id"];

        $data = [
            'TYPE' => 'USER_SUBMIT',
            'ACTION_STATUS' => 1,
            'DESCRIPTION' => $description ?: '',
            'USER_ID' => $user_id,
            'MAP_ID' => $map_id,
        ];

        $this->DBs->SOCOM_UI->insert($this->log_table, $data);

        $afrows = $this->DBs->SOCOM_UI->affected_rows();
        if ($afrows <= 0) {
            $this->DBs->SOCOM_UI->trans_rollback();
            throw new ErrorException('Unable to add submission of upload');
        }

        $upload_metadata = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_with_cap_sponsor($map_id, UploadType::DT_UPLOAD_EXTRACT_UPLOAD);

        if (empty($upload_metadata)) {
            $this->DBs->SOCOM_UI->trans_rollback();
            throw new Error('User was unable to submit this upload, does capability sponsor code of the user match the upload?');
        } else {
            $this->DBs->SOCOM_UI->trans_complete();
            $table_type = '';
            if (str_contains($upload_metadata['DIRTY_TABLE_NAME'], 'ISS')) {
                $table_type = 'iss';
            } elseif (str_contains($upload_metadata['DIRTY_TABLE_NAME'], 'ZBT')) {
                $table_type = 'zbt';
            }

            if ($table_type == '') {
                throw new Error('Unable to add table type');
            }
     
            $row_ids = $this->DBs->SOCOM_UI
                            ->select('ID')
                            ->where('CAPABILITY_SPONSOR_CODE', $upload_metadata['CAP_SPONSOR'])
                            ->where('USR_DT_UPLOADS_ID', $map_id)
                            ->get($upload_metadata['DIRTY_TABLE_NAME'])
                            ->result_array();


            $row_ids = array_column($row_ids, 'ID');

            $submission_status = 'SUBMITTED';
            $res = php_api_call(
                'PATCH',
                'Content-Type: ' . APPLICATION_JSON,
                json_encode($row_ids, true),
                RHOMBUS_PYTHON_URL."/socom/dirty-table/{$table_type}/status?new_status=" . $submission_status
            );

            $result = json_decode($res, true);
            if (isset($result['detail'])) {
                throw new Error($result['detail']);
            }

            // $this->DBs->SOCOM_UI
            //     ->set('SUBMISSION_STATUS','SUBMITTED')
            //     ->set('IS_ACTIVE', 0)
            //     ->where('CAPABILITY_SPONSOR_CODE', $upload_metadata['CAP_SPONSOR'])
            //     ->update($upload_metadata['DIRTY_TABLE_NAME']);

            /*if ($this->DBs->SOCOM_UI->affected_rows()<= 0) {
                $this->DBs->SOCOM_UI->trans_rollback();
                throw new ErrorException('Unable to update table submission');
            }*/
        }

        $this->DBs->SOCOM_UI->trans_complete();
		return $this->DBs->SOCOM_UI->trans_status();
    }

    public function save_approve(int $map_id, string $description): bool
    {
        $this->DBs->SOCOM_UI->trans_start();

        $user_id = (int) $this->session->userdata("logged_in")["id"];

        $data = [
            'TYPE' => 'ADMIN_APPROVAL',
            'ACTION_STATUS' => 1,
            'DESCRIPTION' => $description ?:'',
            'USER_ID' => $user_id,
            'MAP_ID' => $map_id,
        ];

        $this->DBs->SOCOM_UI->insert($this->log_table, $data);

       /* if ($this->DBs->SOCOM_UI->affected_rows() <= 0) {
            $this->DBs->SOCOM_UI->trans_rollback();
            throw new ErrorException('Unable to approve upload.');
        } */

       // $this->set_is_active_dirty_table($map_id);
        $this->set_is_final_table_active($map_id);

        $table_metadata = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_admin_id($map_id);
        
        if (empty($table_metadata)) {
            $this->DBs->SOCOM_UI->trans_rollback();
            throw new Error('User was unable to approve this upload.');
        } else {
            $this->DBs->SOCOM_UI->trans_complete();
            $position = '';
            if (str_contains($table_metadata['DIRTY_TABLE_NAME'], 'ISS')) {
                $position = 'iss';
            } elseif (str_contains($table_metadata['DIRTY_TABLE_NAME'], 'ZBT')) {
                $position = 'zbt';
            }
            
            if ($position == '') {
                throw new Error('Unable to get position type');
            }
  
            $res = php_api_call(
                'POST',
                'Content-Type: ' . APPLICATION_JSON,
                '',
                RHOMBUS_PYTHON_URL."/socom/dt_table/upsert?position=" . $position
            );

            $result = json_decode($res, true);
            if (isset($result['detail'])) {
                throw new Error($result['detail']);
            }

            // $this->DBs->SOCOM_UI
            //     ->set('SUBMISSION_STATUS','APPROVED')
            //     ->set('IS_ACTIVE', 1)
            //     ->where('SUBMISSION_STATUS','SUBMITTED')
            //     ->update($table_metadata['DIRTY_TABLE_NAME']);

                // if ($this->DBs->SOCOM_UI->affected_rows()<= 0) {
                //     $this->DBs->SOCOM_UI->trans_rollback();
                //     throw new ErrorException('Unable to update table approval');
                // }
        }
        
        $this->DBs->SOCOM_UI->trans_complete();
		return $this->DBs->SOCOM_UI->trans_status();
    }

    public function set_is_final_table_active(int $metadata_id): void
    {
            $this->DBs->SOCOM_UI
                ->where('ID', $metadata_id)
                ->update('USR_DT_LOOKUP_TABLE_METADATA', ['IS_FINAL_TABLE_ACTIVE' => 1]);
            
    }
}
