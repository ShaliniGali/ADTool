<?php
$page_data['page_title'] = "Login";
$page_data['page_tab'] = "Login";
$page_data['page_navbar'] = false;
$page_data['page_specific_css'] = array("terms-of-service-modal.css", "tfa.css");
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);
?>


<div class="container h-100">
  <div class="row h-100" id="particles-js">
    <div class="col-sm-12 align-self-center" style="z-index: 1;">
      <div class="card card-block shadow-lg">
        <div id="login_card" class="py-5 mt-5">
          <div class="row no-gutters pt-md-5">
            <div class="d-none d-md-block col-sm-10 offset-sm-1 col-md-6 offset-md-0 col-lg-4 offset-lg-1  align-self-center">
              <div class="col-sm-6 offset-sm-3 col-md-10 offset-md-1 text-center">
                <img alt="logo" src="<?php echo base_url() . 'assets/images/Logos/guardian_logo.png'; ?>" class="w-100 px-5 pb-5 pb-md-0 logo_tilt">
              </div>
            </div>
            
            <div class="col-sm-12 text-center text-muted offset-sm-0 col-md-6 offset-md-0 col-lg-5 offset-lg-1 align-self-center">
                <div class="h1 text-danger mb-4"> Access Denied!</div>
                <div> You do not have permission to access this website beyond this point. </div>
            </div>
            <div class="col-12  pt-5 mt-2" id="copy_rights">
              <div class="form-group text-muted small text-center mt-md-5">
                GUARDIAN © 2011-<?php echo date('Y'); ?> Rhombus Power Inc. <span class="text-success pl-2"><em class="fab fa-product-hunt mr-2"></em><?php echo RHOMBUS_PROJECT_NAME; ?></span>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade show active d-none" id="tfa_login">
          <?php $this->load->view('login_tfa_view'); ?>
        </div>
        <div class="tab-pane fade show active d-none" id="tfa_register">
          <?php $this->load->view('register_tfa_view'); ?>
        </div>
      </div>
    </div>
  </div>
</div>




<?php
  $this->load->view('templates/essential_javascripts');
?>

<?php 
    $js_files = array();
    $js_files['particles'] = ["particles.js",'global'];
    $js_files['particle'] = ["actions/particle.js",'custom'];
    $CI =& get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>


<?php

$this->load->view('templates/close_view');

?>