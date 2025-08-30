<?php

require_once(APPPATH.'libraries/SOCOM/Database_Upload_Base.php');

#[AllowDynamicProperties]
class Program_Import extends Database_Upload_Base {
    public const TYPE = 'PROGRAM_SCORE_UPLOAD';
}