<?php

require_once(APPPATH.'libraries/SOCOM/Database_Upload_Base.php');

#[AllowDynamicProperties]
class Out_of_POM_Cycle_Data_Upload_Import extends Database_Upload_Base {
    public const TYPE = 'DT_OUT_POM';
}