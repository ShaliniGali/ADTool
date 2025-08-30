<?php
//
//Lea created June 9th 2020
//

#[AllowDynamicProperties]
class Email_body{
    public $status ='success';
    public $message ='';
    public $body= "";    private $template = array(
      'welcome'=>
        ['greeting'=>'Welcome',
        'content'=>[['type'=>'special',
        'text'=>'Guardian addresses Big Data and Sensing Problems in Security, Energy & Health Care.'],
        ['type'=>'text','text'=>'One of our international clients is involved in the safe, timely and cost-effective decommissioning of nuclear reactors<br><br>
          Guardian predicts adverse outcomes for members with high accuracy and augments the client’s decision processes<br><br>
          Combining authentic Air Force data on Manpower, Money and Materiel (Assets) with Guardian’s global context data, we have configured Guardian to assist in the US Air Force’s strategic and budget planning processes',
        ]]],
      'resetPassword'=>['title'=>'Reset Your Password',
        'content'=>[['type'=>'text','text'=>'You recently requested to reset your password for your Rhombus Power account. Click the button below to reset it. If you did not request a password reset please ignore this email. The password reset is only valid for the next 30 minutes.'],
        ['type'=>'button','linkText'=>'Reset Password']]],
      'verifyEmail'=>['title'=>'Reset Your Password',
        'content'=>[['type'=>'text','text'=>'Thank you for registering an account with Rhombus Power! Before we get started, we\'ll need to verify your email'],
        ['type'=>'button','linkText'=>'Verify Email']]],
      );

    function customError($errno, $errstr) {
      $this->status = 'error!';
      $this->message = $errstr;
    }
    //{
    //date:"TRUE|FALSE", uses the current date
    //Operateing System:string 'OS Being Used'
    //Browser: string "Browser Being Used"
    //location: string "Loction Being sent from"
    //}
    //default constructor returns default header (no input)


    function __construct() {
        set_error_handler( array($this, 'customError'));
        $data = func_get_args();
        
        if(!isset($data[0]['template']) || $data[0]['template']=='custom'){
            $this->body = $this->getBody($data[0],$data[0]);
        }else{
          if(isset($this->template[$data[0]['template']])){
            $this->body = $this->getBody($data[0],$this->template[$data[0]['template']]);
          }else{
            $this->status ='error!';
            $this->message = 'Invalid Template';
            return;
          }
        }
    }


    private function getBody($data,$template){
        $body = '
        <div style="background-color:#DADFE1;">  
        <div style="margin:0px auto;max-width:600px; background-color:white;"> 
        <table style="width:600px;font-size:13px;line-height:22px;cellpadding:0;cellspacing:0;color:#130f40;font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, \'Noto Sans\', sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\', \'Noto Color Emoji\';">
        <tbody style="margin:0px auto;max-width:100%;">';
        //title receiverName title and greeting are outside the body array
        //these 3 are always in this order
        if(isset($template['title'])){
          $body .= '<tr>'.$this->getTitle($template['title'])."</tr>";
        }
        if(isset($data['receiverName'])){
          if(isset($template['greeting'])){
            $body .= $this->getGreeting($template['greeting'].' '.$data['receiverName']);
          }else{
            $body .= $this->getGreeting("Hello ".$data['receiverName']);
          }
        }else{
          if(isset($template['greeting'])){
            $body .= $this->getGreeting($template['greeting']);
          }else{
            $body .= $this->getGreeting("Hello");
          }
        }
        foreach($template['content'] as $val){
          if($val['type'] == 'row'){
            $row = '<tr><td><table style="width:100%;">';
            foreach($val['row'] as $val2){
              $row .= $this->getTD($val2['type'],$val2);
            }
            $body .= $row.'</table></td></tr>';
          }else{
            $body .= '<tr>'.$this->getTD($val['type'],$val,$data).'</tr>';
          }

        }
        return $body.'</tbody></table></div></div>';
    }


    private function getTD($type,$data,$general=''){
      if($type == 'special'){
        return $this->getSpecial($data['text']);
      }
      if($type == 'text'){
        return $this->getText($data['text']);
      }
      if($type == 'button'){
        //if the link if given in the template use it
        if(isset($data['link'])){
          return $this->getButton($data['link'],$data['linkText']);
        //otherwise if a link if given in general data use that
        }else if(isset($general['link'])){
          return $this->getButton($general['link'],$data['linkText']);
        }else{
          //if no link is provided throw an error
          $this->status ='error!';
          $this->message = 'The chosen template requires a link to be provided, or no link is provided when button is added';
          return;
        }
      }
      if($type == 'image'){
        $alt = "";
        $width = "500";
        if(isset($data['altText'])){
          $alt = $data['altText'];
        }
        if(isset($data['width']) && $data['width'] < 500){
          $width = $data['width'];
        }
        if(isset($data['src'])){
          return $this->getImage($data['src'],$alt,$width);
        }else{
          //if no link is provided throw an error
          $this->status ='error!';
          $this->message = 'Attribute "link" was not provided for image';
          return;
        }
      }
    }

    private function getTitle($title){
      return '
            <td align="left">
              <div style="margin:0px 40px;color:black;font-size:28px;font-weight:400;line-height:32px;text-align:center;">
                        '.$title.'
              </div>
            </td>
            ';
    }

    private function getGreeting($name){
      return '
            <td align="left">
              <div style="margin:20px 50px;color:black;font-size:28px;font-weight:200;line-height:32px;text-align:left;">
                        '.$name.',
              </div>
            </td>';
    }

    private function getSpecial($text){
      return '
            <td align="center">
              <div style="margin:20px 50px;background-color:#DADFE1;border-radius:0.5em;font-weight:300;font-size:18px;padding:10px 25px;padding-bottom:10px;word-break:break-word;">
                        '.$text.'
              </div>
            </td>';
    }

    private function getText($text){
      return '
            <td align="left" style="margin: 0px;">
              <div style="margin:0px 20px 30px 20px;line-height:30px;font-weight:400;font-size:16px;padding:10px 25px;padding-top:0px;word-break:break-word;">
                        '.$text.'
              </div>
            </td>';
    }


    private function getButton($link,$text){
      return '
            <td align="center">
              <a href="'.$link.'" style="margin:0px">
                  <div style="margin:0px 0px 50px 0px;display: inline-block;width:auto;background-color:#DADFE1;border-radius:0.5em;font-weight:300;font-size:18px;padding:10px 25px;padding-bottom:10px;word-break:break-word;">
                    '.$text.'
                  </div>
              </a>
            </td>';
    }


    private function getImage($image,$altText="",$width=""){
      return '
      <td align="center">
        <div style="margin:0px 20px 50px 20px;line-height:30px;font-weight:400;font-size:16px;padding:10px 25px;padding-top:0px;word-break:break-word;">
          <img alt="'.$altText.'" src="'.$image.'"  width="'.$width.'px" style="border:none;display:block;font-size:13px;outline:none;text-decoration:none;""/>
        </div>
      </td>';
  }
}