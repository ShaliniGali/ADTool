<?php

#[AllowDynamicProperties]
class SOCOM_Scheduler_model extends CI_Model {

    public function add_to_pipeline(UploadType $type) {
      
      $cycle_id = get_cycle_id();
      
      $result = $this->DBs->SOCOM_UI
          ->set('type', $type->name)
          ->set('CRON_STATUS', CRON_STATUS_NEW)
          ->set('CRON_PROCESSED', CRON_PROCESSED_NEW)
          ->set('CYCLE_ID', $cycle_id)
          ->insert('USR_DT_SCHEDULER');

        return $result ? $this->DBs->SOCOM_UI->insert_id() : false;
	}

    public function add_to_map(UploadType $type, $pipeline_id, $map_id) {
        $result = $this->DBs->SOCOM_UI
          ->set('type', $type->name)
          ->set('DT_SCHEDULER_ID', (int)$pipeline_id)
          ->set('MAP_ID', (int)$map_id)
          ->insert('USR_DT_SCHEDULER_MAP');
        
        return $result;
    }

}