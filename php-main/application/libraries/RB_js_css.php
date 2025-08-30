<?php
/**
 *  Sumit: Created: 22 May 2020 
 * 	The purpose of this file is to get rid of js caching and build css and js protocol on the fly
 *  
 *  Constants requires:
 *  s3_url, FILE_CACHING_CSS_JS, FILE_DOWNLOAD_CSS_JS
 * 
 *  Solution1: Adding time dependent variable to js version force the browers to fetch latest changed js files
 *  Solution2: Most common prebuilt files (js or css such as bootstrap, datatable etc.) are sitting in S3 environment
 * 
 *  Lea: Updated: july 13 2020
 *  Edited to use imports from files stored on s3 bucket
 * 
 *  Sumit: Updated: july 14 2020
 *  Inserted the validation of constants
 * 
 *  Sumit: Updated: August 27 2020
 *  Added a capability of exporting css at global and custom level
 * 
 */

defined('BASEPATH') || exit('No direct script access allowed');
// use \Exception as Exception;

#[AllowDynamicProperties]
class RB_js_css {
    
    private $ci;
    private $constants;
    private $format_error_message;
    private $app_js_address;
    private $app_css_address;
    private $app_sipr_js_address;
    private $app_sipr_css_address;
    private $file_type;
    private $file_tag;
    private $s3_js;
    private $s3_css;
    private $app_p1_js_address;

	public function __construct() {
        
        $this->ci =& get_instance();

        /**
         * 
         * @param array (constant name, condition(s))
         * 
         * constants used in this library 
         * 
         */         
        $this->constants = array(
            array(FILE_CACHING_CSS_JS, array("TRUE", "FALSE")),
            array(FILE_DOWNLOAD_CSS_JS, array("TRUE", "FALSE")),
            array(S3_CSS_JS_URL, array(strpos(S3_CSS_JS_URL, 'http')))  
        ); 
        
        /**
         * 
         * alert error message when input format is wrong
         * 
         */
        $this->format_error_message = '<script>alert("Error: Please check input for: $CI->load->library(\'RB_js_css\')\n\n Accepted input format: \n\n $files[\'key_name\'] = [\'local_file_name\',\'custom\'];\n\n $files[\'key_name\'] = [\'file_name\',\'global\'];")</script>';
        
        /**
         * 
         * global variable used in this library
         * 
         */
        $this->app_js_address = "assets/js/";
        $this->app_css_address = "assets/css/";
        $this->app_sipr_js_address = "assets/sipr/js/";
        $this->app_sipr_css_address = "assets/sipr/css/";
        $this->file_type = array("js","css");
        $this->file_tag = array("global","custom");
        $this->s3_js = S3_CSS_JS_URL.'js/';
        $this->s3_css = S3_CSS_JS_URL.'css/';
        $this->app_p1_js_address = "assets/";
        // $this->app_p1_js_address = "dist/assets/";

        /**
         * 
         * checking whether constants are defined in application/config/constants.php
         * 
         */

       
    }    

    /**
     * 
     * The below function evaluates constant parameter(s) defined or not
     * 
     */
    private function constants_check(){
        foreach ($this->constants as &$value) {
            if(!in_array($value[0],$value[1])){
                echo '<script>alert("Error: '.$value[0].' is not defined in application/config/constants.php")</script>'; 
                return false;
            }
        }
    }
    
    /**
     * 
     * The below function check whether incoming file formats are uniform or not
     * 
     */
    private function files_check($files=null){
        $temp = array();
        $file_ext = "";
        foreach ($files as &$value) {
            /**
             * 
             * Checking an input file extension type
             * 
             */
            $file_ext = pathinfo($value[0], PATHINFO_EXTENSION);
            if(!in_array(strtolower($file_ext),$this->file_type)){
                echo '<script>alert("Error: '.$value[0].' is neither css nor js file")</script>'; 
                return false;
            }
            array_push($temp,$file_ext);
            /**
             * 
             * Checking an input tag: global or custom
             * 
             */
            if(!in_array($value[1],$this->file_tag)){
                echo '<script>alert("Error: '.$value[1].' tag is neither global nor custom")</script>'; 
                return false;
            }
        }
        /**
        * 
        * Compression should handle only one type (css or js) in a compression load
        * 
        */
        if(count(array_unique($temp))!=1){
            echo '<script>alert("Error: Compression can handle only one extension type (css or js) in a compression load.")</script>'; 
            return false;
        }

        return $file_ext;
    }

    /**
     * 
     * The below function download or delete file locallly
     * 
     */
    private function download_delete_file($file,$file_ext){
        
        if($file_ext=="css"){
            $file_address = APPPATH.'../'.$this->app_css_address.$file;
            $s3_address = $this->s3_css;
        } else {
            $file_address = APPPATH.'../'.$this->app_js_address.$file;
            $s3_address = $this->s3_js;
        }
        
        if(FILE_DOWNLOAD_CSS_JS=="FALSE"){
            /**
             * 
             * Delete local files if there any 
             * 
             */
            if (file_exists($file_address)){
                unlink($file_address);
            }
        }
        if(FILE_DOWNLOAD_CSS_JS=="TRUE"){
            /**
             * 
             * Bring back files to local address 
             * 
             */
            if (!file_exists($file_address)){
                file_put_contents($file_address, fopen($s3_address.$file, 'r'));
            }
        }
    }

    /**
     * 
     * The below build a css or js html meta tag
     * 
     */
    private function meta_tag_css_js($location,$file_ext){
        if($file_ext=="js"){
            return '<script src="'.$location.'"></script>';
        }
        if($file_ext=="css"){
            return '<link href="'.$location.'" rel="stylesheet" type="text/css" />';
        }
    }

    public function compress($files=null){
        
        $file_ext = $this->files_check($files);
        $result = "";
        if($file_ext=="css"){
            $local_address = base_url().$this->app_css_address;
            $s3_address = $this->s3_css;
            $local_sipr_address = base_url().$this->app_sipr_css_address;
            $local_p1_address = base_url().$this->app_sipr_css_address;
        } else {
            $local_address = base_url().$this->app_js_address;
            $s3_address = $this->s3_js;
            $local_sipr_address = base_url().$this->app_sipr_js_address;
            $local_p1_address = base_url().$this->app_p1_js_address;
        }

        if(FILE_CACHING_CSS_JS=="TRUE"){
            foreach ($files as &$value) {
            if(isset($value[1]) && $value[1] == 'global'){
                if(P1_FLAG === TRUE){
                    $result .= $this->meta_tag_css_js($local_p1_address.$value[0],$file_ext);
                }
                else if (UI_SIPR_ENVIRONMENT === TRUE) {
                    $result .= $this->meta_tag_css_js($local_sipr_address.$value[0],$file_ext);
                }
                 else {
                    $result .= $this->meta_tag_css_js($s3_address.$value[0],$file_ext);
                    /**
                     * 
                     * Bring file back to local or delete from local
                     * 
                     */
                    $this->download_delete_file($value[0],$file_ext);
                }
            } else if(isset($value[1]) && $value[1] == 'custom'){
                $result .= $this->meta_tag_css_js($local_address.$value[0],$file_ext);
            }else{
                $result .= $this->format_error_message;
            }
            }
        } else {
            $temp = time();
            foreach ($files as &$value) {
            if(isset($value[1]) && $value[1] == 'global'){
                if(P1_FLAG === TRUE){
                    $result .= $this->meta_tag_css_js($local_p1_address.$value[0],$file_ext);
                }
                else if (UI_SIPR_ENVIRONMENT === TRUE) {
                    $result .= $this->meta_tag_css_js($local_sipr_address.$value[0].'?v='.$temp,$file_ext);
                } else {
                    $result .= $this->meta_tag_css_js($s3_address.$value[0].'?v='.$temp,$file_ext);
                    /**
                     * 
                     * Bring file back to local or delete from local
                     * 
                     */
                    $this->download_delete_file($value[0],$file_ext);
                }
            } else if(isset($value[1]) && $value[1] == 'custom'){
                $result .= $this->meta_tag_css_js($local_address.$value[0].'?v='.$temp,$file_ext);
            }else{
                $result .= $this->format_error_message;
            }
            }
        } 

        echo $result;
    
    }

}


?>
