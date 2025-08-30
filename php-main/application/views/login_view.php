<?php
$page_data['page_title'] = "Login";
$page_data['page_tab'] = "Login";
$page_data['page_navbar'] = false;
$page_data['page_specific_css'] = array("terms-of-service-modal.css", "tfa.css");
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);
?>


<div class="container h-100">
  <div class="row h-100" id="particles-js"  style="z-index: 1;">
    <div class="d-flex align-items-center justify-content-center min-vh-100"  style="z-index: 2;">
      <div class="card card-block shadow-lg">
        <div id="login_card">
          <div class="row no-gutters pt-md-5">
            <div class="d-none d-md-block col-sm-10 offset-sm-1 col-md-6 offset-md-0 col-lg-4 offset-lg-1  align-self-center">
              <div class="col-sm-6 offset-sm-3 col-md-10 offset-md-1 text-center">
                <img alt="logo" src="<?php echo base_url() . 'assets/images/Logos/guardian_logo.png'; ?>" class="w-100 px-5 pb-5 pb-md-0 logo_tilt">
              </div>
            </div>
            <div class="col-sm-12 offset-sm-0 col-md-6 offset-md-0 col-lg-5 offset-lg-1 align-self-center">
              <ul class="nav nav-tabs nav-pills nav-fill flex-column flex-lg-row justify-content-center" id="nav-tab">
                <li class="nav-item">
                  <?php if(UI_USERNAME_PASS_AUTH === 'TRUE'){
                    echo'
                  <a class="nav-link active" id="nav-login-tab" data-toggle="tab" href="#nav-login" role="tab" aria-controls="nav-login" aria-selected="true">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                  </a>';
                  }
                  ?>
                </li>
                <?php
                   if(UI_USERNAME_PASS_AUTH === 'TRUE'){
                      if(
                        UI_EMAIL_SEND_SMTP === 'TRUE' ||
                        (UI_EMAIL_SEND === 'TRUE' && (RB_EMAIL_API_KEY!=""))
                      ){
                echo '<li class="nav-item">
                  <a class="nav-link" id="nav-registration-tab" data-toggle="tab" href="#nav-registration" role="tab" aria-controls="nav-registration" aria-selected="true">
                    <i class="fas fa-user-plus mr-2"></i>Registration
                  </a>
                </li>';
                    }
                  }
                    ?>
              </ul>
              <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-login" role="tabpanel" aria-labelledby="nav-login-tab">
                  <?php 
                  if(RHOMBUS_SSO ==='TRUE'){
                    $this->load->view('login_saml_form');
                  } else if (RHOMBUS_SSO_KEYCLOAK === 'TRUE') {
                      $this->load->view('keycloak/login_keycloak_form');
                  } else if (RHOMBUS_SSO_PLATFORM_ONE === 'TRUE') {
                    $this->load->view('platform_one/login_platform_one_form');
                  } else {
                    $this->load->view('login_form'); 
                  }
                  ?>
                </div>
                <?php
                  if(UI_USERNAME_PASS_AUTH === 'TRUE'){
                    if(
                      UI_EMAIL_SEND_SMTP === 'TRUE' ||
                      (UI_EMAIL_SEND === 'TRUE' && (RB_EMAIL_API_KEY!=""))
                    ){
                      echo '<div class="tab-pane fade" id="nav-registration" role="tabpanel" aria-labelledby="nav-registration-tab">';
                      $this->load->view('register_form');
                      echo '</div>';
                    }
                  }

                ?>
                
              </div>
            </div>
            <div class="col-12" id="copy_rights">
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

<!-- CAC AUTH MODAL -->
<?php
    $message = '
    <div class = "col-12">
      Insert your CAC into your CAC Reader. Then press CONTINUE.
    </div>';
    $base = "cac_modal";
    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "noticeModal";
    $data['basic_modal_id'] = $base."_modal"; 
    $data['modal_header_id'] = $base."_header";  
    $data['modal_header_value'] = "Client Authentication";  
    $data['modal_footer_id'] = $base."_footer";  
    $data['cancel_button_id'] = $base."_cancel";
    $data['modal_body_id'] = $base."_body";   
    $data['modal_body_value'] = $message;  
    $data['continue_button_id'] = "cac_continue_btn";
    $data['button_string'] = "CONTINUE"; 

    $this->load->view('templates/modals', $data);
?>



<?php
  $this->load->view('templates/essential_javascripts');
?>

<?php 
    $js_files = array();
    $js_files['login'] = ["login/login.js",'custom'];
    $js_files['particles'] = ["particles.js",'global'];
    $js_files['particle'] = ["actions/particle.js",'custom'];
    if(UI_USERNAME_PASS_AUTH === 'TRUE'){
        $js_files['register'] = ["actions/register.js",'custom'];
        $js_files['recovery'] = ["login/recovery_code.js",'custom'];
        $js_files['login_token'] = ["login/login_token.js",'custom'];
    }
    $CI =& get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
    
?>






<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "basic_modal";
  $data['basic_modal_id'] = "login_modal"; 
  $data['basic_modal_title_id'] = "login_modal_title";  
  $data['basic_modal_body_id'] = "login_modal_body";  
  $data['basic_modal_button_1_id'] = "login_modal_button1";  
  $data['basic_modal_button_2_id'] = "login_modal_button2";
  $this->load->view('templates/modals', $data);
  

 
  
  /*
    Updated: Moheb, June 24th, 2020
    ToS modal data
  */
  $data = array();
  $data['modal_id'] = "terms_of_service_modal_id";
  $data['terms_of_service_modal_id'] = '"terms_of_service_modal"';
  $data['terms_of_service_modal_title_id'] = '"terms_of_service_modal_title"';
  $data['terms_of_service_modal_title'] = 'Rhombus Power Terms of Service and Privacy Policy';
  $data['terms_of_service_modal_footer'] = 'By logging in, you acknowledge and agree to all of Rhombus Power\'s terms and conditions.';
  $this->load->view('templates/modals', $data);

 

  // modal loadings after picking any of the authentication methods.
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "tfa_modal";
  $data['tfa_modal_id'] = "selected_tfa_modal";
  $data['tfa_modal_header_id'] = "selected_tfa_modal_header";
  $data['tfa_modal_body_id'] = "selected_tfa_modal_body";
  $data['tfa_modal_footer_id'] = "selected_tfa_modal_footer";
  $this->load->view('templates/modals', $data);


  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "basic_modal";
  $data['basic_modal_id'] = "reset_key_modal"; 
  $data['basic_modal_title_id'] = "reset_key_modal_title";  
  $data['basic_modal_body_id'] = "reset_key_modal_body";  
  $data['basic_modal_button_1_id'] = "reset_key_modal_button1";  
  $data['basic_modal_button_2_id'] = "reset_key_modal_button2";
  $this->load->view('templates/modals', $data);

  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "basic_modal";
  $data['basic_modal_id'] = "reset_pwd_modal"; 
  $data['basic_modal_title_id'] = "reset_pwd_modal_title";  
  $data['basic_modal_body_id'] = "reset_pwd_modal_body";  
  $data['basic_modal_button_1_id'] = "reset_pwd_modal_button1";  
  $data['basic_modal_button_2_id'] = "reset_pwd_modal_button2";
  $this->load->view('templates/modals', $data);
?>


<?php

$this->load->view('templates/close_view');

?>
