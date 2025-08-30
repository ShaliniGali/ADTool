<?php

require_once(APPPATH.'libraries/SOCOM/Database_Upload_Base.php');

#[AllowDynamicProperties]
class In_POM_Cycle_Data_Upload_Import extends Database_Upload_Base {
    public const TYPE = 'DT_UPLOAD_BASE_UPLOAD';
}