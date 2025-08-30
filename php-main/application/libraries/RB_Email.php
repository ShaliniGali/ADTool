<?php
/**
 * 
 * created june 16 2020 Lea
 * edited june 26 2020 Lea
 * edited sept 28 2020 Lea
 * add attatchement capabilities
 * format -> attatchements => [{'name':<url or path or file>},{'name':<url or path or file>}]
 * 
 * Edited October 27 2021 Ian
 * added CC capabilities. with 'cc'=>'email' or 'cc' => array
 * 
 */

defined('BASEPATH') OR exit('No direct script access allowed');
use \Exception as Exception;

#[\AllowDynamicProperties]
class RB_Email {
    private $CI;
    public $status = 'success';
    public $message = '';
    public $content = '';
    public $companies = array(
        'rhombuspower.com' => array('title'=>'Rhombus Power Inc', 
                                    'logoAlt'=>'Rhombus', 
                                    'logoWidth'=>70,
                                    'logo'=>'/Logos/rhombus_logo_70x70',
                                    'from_name' => 'Rhombus Power',
                                    'from_email' => ''
                                )
    );
    public $default_company = array();
    function set_company_values($email){
        $domain = explode('@', $email)[1];
        $this->default_company = $this->companies[$domain] ?? $this->companies['rhombuspower.com'];
    }

    function customError($errno, $errstr = "") {
        $this->status = 'error!';
        $this->message = $errno." ".$errstr;
        // echo($this->status);
        // echo($this->message);
        return;
    }
    

    public function __construct(){
        $this->companies['rhombuspower.com']['from_email'] = (UI_EMAIL_SMTP_FROM != '' ? UI_EMAIL_SMTP_FROM : 'it@rhombuspower.com');

        $this->CI =& get_instance();
        set_error_handler( array($this, 'customError'));
        set_exception_handler( array($this, 'customError'));
        require_once(APPPATH . 'libraries/Rhombus_email/Email_footer.php');
        require_once(APPPATH . 'libraries/Rhombus_email/Email_header.php');
        require_once(APPPATH . 'libraries/Rhombus_email/Email_body.php');
    }

    //{receiver:'receiverEmail',
    //type:'login|resetPassword|etc 
    //content:{receiverName, text, special, link, linkName, title}
    //if not type-> message:a string and subject: a string
    //header:FALSE|{logo,logoalt,logoHeight,logoWdith,title}
    //footer:FALSE|{date|OS|browser|location}    }

    public function rhombus_email($email){
        

        if($email == 'documentation'){
            $this->status = 'documentation';
            $this->message = $this->documentation();
            return $this->documentation();
        }
        
        if($this->status != 'test'){
            $this->status = 'success';
            $this->message = '';
        }

        if(!is_array($email)){
            $this->status = 'error!';
            $this->message = 'Please check the documentation for the format the input should be in';
            return;
        }

        $this->CI->form_validation->set_data($email);
        //form validation
        $valid= $this->CI->form_validation->run_rules(array(
            'receiverEmail' => array('rules'=>array('required', 'valid_email')),
            'subject' => array('rules'=>array('required'))
            ));
            if ($valid !== 'success')
            {
                $this->status = 'error!';
                $this->message .= form_error('receiverEmail');
                $this->message .= form_error('subject');
                return;
            }

        $this->set_company_values($email['receiverEmail']);

        if(!isset($email['content']) && (!isset($email['template']) || $email['template'] == 'custom')){
            $this->status = 'error!';
            $this->message = 'you must provide a valid template or content';
            return;
        }
        
        //reset content to blank
        $this->content = "";

        if(isset($email['header'])){
            if( !is_bool($email['header'])){
                $h = new Email_header($this->default_company, $email['header']);
                if($h->status == 'success'){
                    $this->content .= $h->header;
                }else{
                    $this->status = 'error!';
                    $this->message = $h->message;
                    return;
                }
            } else if($email['header'] == TRUE){
                $h = new Email_header($this->default_company);
                if($h->status == 'success'){
                    $this->content .= $h->header;
                }else{
                    $this->status = 'error!';
                    $this->message = $h->message;
                    return;
                }
            }
        }else{
            $h = new Email_header($this->default_company);
            if($h->status == 'success'){
                $this->content .= $h->header;
            }else{
                $this->status = 'error!';
                $this->message = $h->message;
                return;
            }
        }

        if(isset($email['type']) && $email['type'] != 'custom'){
            $b = new Email_body($email);
            if($b->status == 'success'){
                $this->content .= $b->body;
            }else{
                $this->status = 'error!';
                $this->message = $b->message;
                return;
            }
            //do something
        }else{
            //custom message template
            $b = new Email_body($email);
            if($b->status == 'success'){
                $this->content .= $b->body;
            }else{
                $this->status = 'error!';
                $this->message = $b->message;
                return;
            }
        }

        if(isset($email['footer'])){
            if( !is_bool($email['footer']) ){
                $h = new Email_footer($email['footer']);
                if($h->status == 'success'){
                    $this->content .= $h->footer;
                }else{
                    $this->status = 'error!';
                    $this->message = $h->message;
                    return;
                }
            } else if($email['footer'] == TRUE){
                $h = new Email_footer();
                if($h->status == 'success'){
                    $this->content .= $h->footer;
                }else{
                    $this->status = 'error!';
                    $this->message = $h->message;
                    return;
                }
            }
        }else{
            $h = new Email_footer();
            if($h->status == 'success'){
                $this->content .= $h->footer;
            }else{
                $this->status = 'error!';
                $this->message = $h->message;
                return;
            }
        }

            //
            //  Clear email
            //
            $this->CI->email->clear();
            $this->CI->email->from($this->default_company['from_email'], $this->default_company['from_name']);
            $this->CI->email->to($email['receiverEmail']);
            $this->CI->email->subject($email['subject']);
            $this->CI->email->message($this->content);
            if(isset($email['attachment'])){
                foreach($email['attachment'] as $attachment){
                    if(isset($attachment['newName'])){
                        $this->CI->email->attach($attachment['name'],'attachment', $attachment['newName']);
                    }else{
                        $this->CI->email->attach($attachment['name']);
                    }
                }
            }
            if(isset($email['cc'])){
                $this->CI->email->cc($email['cc']);
            }
            if($this->status == 'success' && (UI_EMAIL_SEND ==='TRUE' || UI_EMAIL_SEND_SMTP === 'TRUE')){
                if ( ! $this->CI->email->send())
                {
                    $this->status = 'error!';
                    $this->message = $this->CI->email->print_debugger(array('headers'));
                    return;
                }else{
                    $this->status = 'success';
                    $this->message = 'Email is sent successfully';
                }
            }else if($this->status == 'success' && (UI_EMAIL_SEND === 'FALSE' || UI_EMAIL_SEND_SMTP === 'FALSE')){
                $this->status = 'error!';
                $this->message = "Email is not sent. Please ask admin (it@rhombuspower.com) to update constant.";
            }
    }

    //
    //Lea June 26 2020
    //returndocumentation as a php array, a string representation of a php string and a json string
    //
    public function documentation(){
        $phpArray =  [
            'Using a Custom Template' => [
                'receiverEmail'=>'it@rhombuspower.com',
                'subject'=>'Rhombus Document Request',
                'receiverName'=>'test',
                'template'=> 'custom',
                    'greeting'=>'Congratulations',
                    'content' => [
                   ['type'=>'text','text'=>"hello this is a test email"],
                   ['type'=>'special','text'=>"hello im in a box"],
                   ['type'=>'image','src'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg'],
                   ['type'=>'button','link'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg','linkText'=>'Hello Im a button'],
                   ]],
            'Using the Row Element'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'subject'=>'Rhombus Document Request',
                'receiverName'=>'test',
                'template'=> 'custom',
                'content' => [
                    ['type'=>'special','text'=>"there is a row below!"],
                    ['type'=>'row','row'=>[['type'=>'text','text'=>'theres an image to the right'],['type'=>'image','width'=>'70','src'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg']]],
                    ]],
            'Edit the header' => [
                'receiverEmail'=>'it@rhombuspower.com',
                'subject'=>'Rhombus Document Request',
                'receiverName'=>'test',
                'header' => ['title'=>':0','logoSrc'=>'https://linkshare2.flippydemos.com/uploaded_images/dogs_497551602.jpg'],
                'template'=> 'custom',
                'content' => [
                    ['type'=>'special','text'=>"look at that header!"],
                    ]],
            'Edit the footer'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'subject'=>'Rhombus Document Request',
                'receiverName'=>'test',
                'footer' => ['os'=>':0',
                        'date'=>false,
                        'browser'=>false,
                        'ipAddress'=>'nearby'],
                'template'=> 'custom',
                'content' => [
                    ['type'=>'special','text'=>"look at that footer!"],
                    ]],
            'Using the welcome template'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'receiverName'=>'Test',
                'subject'=>'welcome',
                'template'=>'welcome'],
            'Using the resetPassword template'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'receiverName'=>'Test',
                'subject'=>'Reset Password',
                'template'=>'resetPassword',
                'link'=>'#'],
            'Using the verifyEmail template'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'receiverName'=>'Test',
                'subject'=>'verify Email',
                'template'=>'verifyEmail',
                'link' => '#'],

            'Attaching a File'=>[
                'receiverEmail'=>'it@rhombuspower.com',
                'subject'=>'Rhombus Document Request',
                'receiverName'=>'Test',
                'template'=> 'custom',
                'attachment'=>[['name'=>'https://outwardhound.com/furtropolis/wp-content/uploads/2020/03/Doggo-Lingo-Post.jpg','newName'=>'file.jpg'],['name'=>"assets/images/background.jpg"]],
                'content' => [
                    ['type'=>'text','text'=>"2 images are attahced to this email"]
                ]]
                ];
            $data = ['phpString'=>[],'jsonString' => []];

        foreach($phpArray as $k => $v){
            $data['phpString'][$k] = $this->getphpString($v);
            $data['jsonString'][$k] = json_encode($v);
        }

        return json_encode($data);
    
    }

    //
    //june 26 2020 Lea
    //turns a 2d or 3d etc. php array into a string representation (with quptation marks around the strings)
    //
    private function getphpString($array){
        //return(3);
        $body = '[';
        foreach($array as $k=>$v){
            $content = '';
            if(is_array($v)){
                $content = $this->getphpString($v);
            }else{
                if(is_bool($v)){
                    $content = $v ? 'true' : 'false';
                }else{
                    $content = "'".$v."'";
                }
            }
            if(is_integer($k)){
                $body .= $content.", \n ";
            }else{
                $body .= "'".$k."' => ".$content.", \n ";
            }
        }
        return substr($body, 0, -4).']';
    }

}

