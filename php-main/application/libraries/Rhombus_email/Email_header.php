<?php
//
//Lea created June 9th 2020
//
#[AllowDynamicProperties]
class Email_header{
    public $default_logo_file='/Logos/rhombus_logo_70x70';
    public $logo = "";
    public $logoAlt = "Rhombus";
    public $title = "Rhombus Power Inc";
    public $logoWidth = 70;
    public $header = "";
    public $status = "success";
    public $message = "";

// edited by Alex Mercer July 2 
// removed "height:auto" from line 24

    private $css ='
    <style type="text/css">
    #outlook a{padding: 0;}
          .ReadMsgBody{width: 100%;}
          .ExternalClass{width: 100%;}
          .ExternalClass *{line-height: 100%;}
          body{margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;}
          table, td{border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;}
          img{border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;}
          p{display: block; margin: 13px 0;}

          @media only screen and (max-width: 420px) {
            td {
              display: block !important;
              width: 100% !important;
            }
            tr {
              display: block !important;
              width: 100% !important;
            }
          }
            @media only screen and (min-width:480px) {
            .dys-column-per-100 {
              width: 100#C5C6C7% !important;
              max-width: 100#C5C6C7%;
            }
            }
            @media only screen and (max-width:480px) {
            
                    table.full-width-mobile { width: 100% !important; }
                    td.full-width-mobile { width: auto !important; }
            
            }
            @media only screen and (min-width:480px) {
            .dys-column-per-50 {
              width: 50#C5C6C7% !important;
              max-width: 50#C5C6C7%;
            }
            }
  <!--[if !mso]><!-->
    @media only screen and (max-width:480px) {
                @-ms-viewport {width: 320px;}
                @viewport {	width: 320px; }
            }
  <!--<![endif]-->
  <!--[if mso]> 
  <xml> 
    <o:OfficeDocumentSettings> 
      <o:AllowPNG/> 
      <o:PixelsPerInch>96</o:PixelsPerInch> 
    </o:OfficeDocumentSettings> 
  </xml>
  <![endif]-->
  <!--[if lte mso 11]> 
    .outlook-group-fix{width:100% !important;}
  <![endif]-->
  </style>
    ';


    function customError($errno, $errstr) {
      $this->status = 'error!';
      $this->message = $errstr;
      return;
    }

    //{
    //logo:"image URL",
    //logoAlt:'logo image alt text'
    //title:"top of the email"
    //}
    //default constructor returns default header (no input)
    function __construct($default_company) {
      $this->default_logo_file = $default_company['logo'];
      $this->logoAlt = $default_company['logoAlt'];
      $this->title = $default_company['title'];
      $this->logoWidth = $default_company['logoWidth'];

      $array = file_get_contents(dirname(__FILE__).$this->default_logo_file);
      $this->logo = $array;
        set_error_handler( array($this, 'customError'));
        $a = func_get_args();
        $i = func_num_args();
        if ($i >= 2) {
            $this->__construct_custom_header($a[1]);
        }else{
            $this->header =  $this->getHeader().$this->getBody();
        }
    }

    function __construct_custom_header($headerarray) {
        if(isset($headerarray['title']) && $headerarray['title']!== TRUE){
            $this->title = $headerarray['title'];
        }
        if(isset($headerarray['logoSrc']) && $headerarray['logoSrc'] !== TRUE){
          $this->logo = $headerarray['logoSrc'];
        }
        if(isset($headerarray['logoAlt']) && $headerarray['logoAlt']!== TRUE){
            $this->title = $headerarray['logoAlt'];
        }
        if(isset($headerarray['logoWidth']) && $headerarray['logoWidth'] !== TRUE){
            $this->logoWidth = $headerarray['logoWidth'];
        }
        $this->header =  $this->getHeader().$this->getBody();
    }

    private function getTitleRow(){

        return '
        <tr>
            <td align="center" style = "font-size:30px; padding:1em">
                '.$this->title.'
            </td>
        </tr>';
    }

    private function getLogoRow(){
        return '<tr>
        <td align="center" style="padding-bottom:20px">
        <div>
          <img alt="'.$this->logoAlt.'" src="'.$this->logo.'"  width="'.$this->logoWidth.'px" style="border:none;display:block;font-size:13px;outline:none;text-decoration:none;""/>
        </div>
        </td>
      </tr>';
    }

    public function getHeader(){
        return  '
        <head>
        <title>
          email
        </title>
        '.$this->getIcons().'
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        '.$this->css.'
        </head>';
    }

    private function getBody(){
        $body = '<body>
        <div style="background-color:#DADFE1;">  
        <div style="margin:0px auto;max-width:600px; background-color:white;"> 
        <table width="100%" style=";cellpadding:0;cellspacing:0;color:#130f40;font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, \'Noto Sans\', sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\', \'Noto Color Emoji\';font-size:13px;line-height:22px;table-layout:auto;width:100%;">';

        if($this->title){
            $body = $body.$this->getTitleRow();
        } 
        if($this->logo){
            $body = $body.$this->getLogoRow();

        }
        return $body.'</table></div></div></body>';
    }

    private function getIcons(){
        
        return'
        <link rel="apple-touch-icon" href="'.base_url().'assets/images/Logos/guardian_logo_70x70.png">
        <link rel="apple-touch-icon" sizes="70x70" href="'.base_url().'assets/images/Logos/guardian_logo_70x70.png">
        <link rel="apple-touch-icon" sizes="144x144" href="'.base_url().'assets/images/Logos/guardian_logo_144x144.png">
        <link rel="icon" type="image/x-icon" href="'.base_url().'assets/images/Logos/favicon.ico" />
        <link rel="icon" type="image/png" href="'.base_url().'assets/images/Logos/guardian_logo.png"/>';    
    }
}