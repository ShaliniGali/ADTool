<?php
//
//Lea created June 9th 2020
//
#[AllowDynamicProperties]
class Email_footer{
    private $date = "";
    private $OS = "";
    private $Browser = "";
    private $ipAddress = "";
    public $footer="";
    public $status = "success";
    public $message = "";

    function customError($errno, $errstr) {
      $this->status = 'error!';
      $this->message = $errstr;
      return;
    }
    //{
    //date:"TRUE|FALSE", uses the current date
    //Operateing System:string 'OS Being Used'
    //Browser: string "Browser Being Used"
    //ipAddress: string "Loction Being sent from"
    //}
    //default constructor returns default header (no input)

    function __construct() {
        $this->CI =& get_instance();
        set_error_handler( array($this, 'customError'));
        $headerarray = func_get_args();
		    $agent = $this->CI->agent;
        if(isset($headerarray[0]['date']) && $headerarray[0]['date']!== TRUE){
            $this->date = $headerarray[0]['date'];
        } else{
          $this->date = date("F j, Y g:i (e)");
        }
        if(isset($headerarray[0]['os']) && $headerarray[0]['os']!== TRUE){
            $this->OS = $headerarray[0]['os'];
        }else{
          $this->OS = $agent->platform();
        }
        if(isset($headerarray[0]['browser']) && $headerarray[0]['browser']!== TRUE){
            $this->Browser = $headerarray[0]['browser'];
        }else{
          $this->Browser = $agent->browser();
        }
        if(isset($headerarray[0]['ipAddress']) && $headerarray[0]['ipAddress']!== TRUE){
            $this->ipAddress = $headerarray[0]['ipAddress'];
        }else{
        if(isset($_SERVER['HTTP_CLIENT_IP'])){
          $this->ipAddress = $_SERVER['HTTP_CLIENT_IP']; 
       }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
          $this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR']; 
       }elseif(isset($_SERVER['HTTP_X_FORWARDED'])){
          $this->ipAddress = $_SERVER['HTTP_X_FORWARDED']; 
       }elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])){
          $this->ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP']; 
       }elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])){
          $this->ipAddress = $_SERVER['HTTP_FORWARDED_FOR']; 
       }elseif(isset($_SERVER['HTTP_FORWARDED'])){
          $this->ipAddress = $_SERVER['HTTP_FORWARDED']; 
       }elseif(isset($_SERVER['REMOTE_ADDR'])){
          $this->ipAddress = $_SERVER['REMOTE_ADDR']; 
       }else{ $this->ipAddress = 'UNKNOWN'; }

        }
        $this->footer = $this->getBody();

    }

    private function getRow($label,$info){
        return '
        <div align="left" style="margin:0px;width:40%;padding:0px 25px;font-size:10px">
          <b><p style="margin-bottom:0px;">'.$label.'</p></b>
          <p style="margin-top:5px">'.$info.'</p>
        </div>';
    }

    private function getBody(){
        $body = '<div style="background-color:#DADFE1;">  
        <div style="margin:0px auto;max-width:600px; background-color:#6C7A89">

        <div width="100%" style="display:flex;flex-wrap:wrap;padding:15px;color:white;max-width:100%;text-align:left;font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, \'Noto Sans\', sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\', \'Noto Color Emoji\';">
        ';

        if($this->date){
            $body = $body.$this->getRow('Date',$this->date);
        } 
        if($this->OS){
          $body = $body.$this->getRow('Operating System',$this->OS);
        } 
        if($this->Browser){
          $body = $body.$this->getRow('Browser',$this->Browser);
        } 
        if($this->ipAddress){
          $body = $body.$this->getRow('IP Address',$this->ipAddress);
        } 
        return $body.'</div></div></div>';
    }

}