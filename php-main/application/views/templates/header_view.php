<!DOCTYPE html>
<html class="h-100" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
  <meta charset="utf-8" />
  <title><?php echo $page_title;?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta content="" name="description" />
  <meta content="" name="author" />
  
  <meta name="robots" content="noindex,nofollow">
      
  <link rel="apple-touch-icon" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_70x70.png">
  <link rel="apple-touch-icon" sizes="70x70" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_70x70.png">
  <link rel="apple-touch-icon" sizes="144x144" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo_144x144.png">
  <link rel="icon" type="image/x-icon" href="<?php echo base_url(); ?>assets/images/Logos/favicon.ico" />
  <link rel="icon" type="image/png" href="<?php echo base_url(); ?>assets/images/Logos/guardian_logo.png"/>
    

  <?php 
  if(P1_FLAG) {$js_p1_flag = TRUE;} else {$js_p1_flag = FALSE;}
?>
  <?php 
    if (isset($this->session->userdata['logged_in'])) {
      $essential_css = array("bootstrap.css","bootstrap-grid.css","bootstrap-reboot.css","dark-mode.css","rhombus.css","carbon.min.css", "all.min.css");
      $essential_css_msg = "essential_css";
    } else {
      $essential_css = array("bootstrap.css","bootstrap-grid.css","bootstrap-reboot.css","dark-mode-login.css","rhombus-login.css", "all.min.css");
      $essential_css_msg = "essential_css_login";
    }
    
    $essential_css = preg_filter('/^/', 'essential/', $essential_css);
    
    $this->minify->add_css($essential_css, "essential_css");
    echo $this->minify->deploy_css(FALSE, 'auto', "essential_css");
    
    
    $css_flag = "0";
    for($i=0; $i<count($page_specific_css); $i++){
      if(strlen($page_specific_css[$i])>0){
        $css_flag = "1";
        $this->minify->add_css([$page_specific_css[$i]],$compression_name);
      }
    }
    if ($css_flag == "1"){
      echo $this->minify->deploy_css(FALSE, 'auto', $compression_name);
    }
    
    ?>
    
    <?php
      if (isset($g100_flag) && $g100_flag) {
        $css_files = array();
        $css_files['carbon-component-min'] = ['carbon-components-g100.css', 'custom'];
        $css_files['styles'] = ['SB/styles.css', 'custom'];
        $CI = &get_instance();
        $CI->load->library('RB_js_css');
        $CI->rb_js_css->compress($css_files);
      }
    ?>
</head>

<body id="main-container" class="h-90" <?php if($page_navbar==false){ echo 'background="'.base_url().'assets/images/background.jpg" style="background-size: cover;"';} ?>  > 



<style>
  /* Use Font Awesome font for icons */
  i.fas, i.far, i.fab, i.fal {
    font-family: "Font Awesome 5 Pro" !important;
    font-style: normal !important;
  }
  i.fad {
    font-family: "Font Awesome 5 Duotone" !important;
    font-style: normal !important;
  }
  i.fab {
    font-family: "Font Awesome 5 Brands" !important;
  }
</style>
 
<?php if (FALSE): ?>
  <div class="custom-control custom-switch nav-link d-none">
    <input type="checkbox" class="custom-control-input" id="darkSwitch"/>
    <label class="custom-control-label" for="darkSwitch">Dark Mode</label>
  </div>
<?php endif; ?>

<?php

  if(isset($this->session->userdata['logged_in']) 
  && in_array($this->session->userdata['logged_in']['email'], ADMIN_EMAILS)) {
    $page_data['user_account_type'] = USER_TYPE_ADMIN;
  } else {
    $page_data['user_account_type'] = isset($this->session->userdata('logged_in')['account_type']) ?
                                      $this->session->userdata('logged_in')['account_type'] : '';
  }

  if ($page_navbar=="bg-none"){ 

      $this->load->view('templates/navbar_view');
      $this->load->view('templates/sidebar_view', $page_data);
      
    
  } else if ($page_navbar){
      
      $this->load->view('templates/navbar_view');
      $this->load->view('templates/sidebar_view', $page_data);
      
    
  }

?>

<script>
  const P1_FLAG = "<?php echo $js_p1_flag; ?>";
</script>


