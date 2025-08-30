<?php
$page_data['page_title'] = "First Admin";
$page_data['page_tab'] = "First Admin";
$page_data['page_navbar'] = false;
$page_data['page_specific_css'] = array("terms-of-service-modal.css", "tfa.css");
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);
?>


<div class="container h-100">
    <div class="row h-100" id="particles-js">
        <div class="col-sm-12 align-self-center" style="z-index: 1;">
            <div class="card card-block shadow-lg">
                <div id="login_card">
                    <div class="py-3 d-flex justify-content-center align-items-center">
                        <img alt="logo" src="<?php echo base_url() . 'assets/images/Logos/guardian_logo.png'; ?>" class="px-5 pb-5 pb-md-0 logo_tilt d-inline" style="height:50px">
                        <p class="text-muted d-inline m-0">Welcome to <?php echo RHOMBUS_BASE_URL ?></p>
                    </div>
                </div>
                <div class="row no-gutters ">
                    <div class="d-none d-md-block col-sm-10 offset-sm-1 col-md-6 offset-md-0 col-lg-4 offset-lg-1  align-self-center">
                        <div class="col-12">
                        </div>
                        <div class="col-sm-6 offset-sm-3 col-md-10 offset-md-1 text-muted">
                            <div class="lead mb-5">You are seeing this page because this is a new UI with no registered users. If this is not the case, please contact <text class="text-info">it@rhombuspower.com.</text></div>
                            <div class="mb-2">This page is visible only because <text class="text-danger">users table</text> is empty.</div>
                            <div class="mb-2">The user who will register here will automatically be granted <text class="text-danger">Admin privileges.</text></div>
                            <div class="mb-2">Do not forget to add this registered email to <text class="text-danger">RB_ADMIN_EMAILS in the .env file.</text></div>
                        </div>
                    </div>
                    <div class="col-sm-12 offset-sm-0 col-md-6 offset-md-0 col-lg-5 offset-lg-1 align-self-center">
                        <form id="rhombus_register" class="needs-validation pb-5 px-3 pt-2" novalidate>
                            <div class="form-row">
                                <div class="col-md-12 mb-3 pb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Email"><em class="far fa-envelope"></em></span>
                                        </div>
                                        <input type="email" class="form-control border-0" name="user_email_register" id="user_email_register" placeholder="Enter email" value="" required>
                                    </div>
                                    <div class="valid-feedback"></div>
                                    <div class="d-none text-danger small pt-1" id="user_email_register_msg"></div>
                                </div>
                                <div class="col-md-12 mb-3 pb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Name"><em class="far fa-user"></em></span>
                                        </div>
                                        <input type="text" class="form-control border-0" name="user_name_register" id="user_name_register" placeholder="Enter name" value="" required>
                                    </div>
                                    <div class="valid-feedback"></div>
                                </div>
                                <div class="col-md-12 mb-3 pb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Password"><em class="fas fa-lock"></em></span>
                                        </div>

                                        <input type="password" class="form-control border-0" name="user_password_register" id="user_password_register" style="border-radius:1px;" placeholder="Enter password" value="" autocomplete="off" required>

                                        <div class="input-group-append show-password">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons">
                                                <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-none text-danger small pt-1" id="user_password_register_msg">
                                        i) at least one upper case letter (A – Z).<br>
                                        ii) at least one lower case letter(a-z).<br>
                                        iii) at least one digit (0 – 9).<br>
                                        iv) at least one special characters of !@#$%&*()
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3 pb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Password"><em class="fas fa-lock"></em></span>
                                        </div>

                                        <input type="password" class="form-control border-0" name="user_password_again_register" id="user_password_again_register" style="border-radius:1px;" placeholder="Enter password again" value="" autocomplete="off" required>

                                        <div class="input-group-append show-password">
                                            <span class="input-group-text text-muted bg-dark border-dark input_icons">
                                                <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="valid-feedback"></div>
                                    <div class="d-none text-danger small pt-1" id="user_password_again_register_msg"></div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="checkboxcontainer text-muted small">
                                        <span id="tos_link" class="pl-3 d-block" data-toggle="modal" data-target="#terms_of_service_modal">I agree to the Rhombus Power Terms of Service and Privacy Policy</span>
                                        <input class="form-check-input" type="checkbox" value="" id="tos_agreement_checkbox">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <textarea class="d-none form-control" id="user_personal_message"> placeholder</textarea>
                            </div>
                            <button class="btn btn-success mt-4 w-100" type="submit" id="rhombus_register_submit" style="cursor:default">CREATE ADMIN</button>

                        </form>
                        <div id="register_confirmation"></div>
                    </div>
                    <div class="col-12" id="copy_rights">
                        <div class="form-group text-muted small text-center mt-md-5">
                            GUARDIAN © 2011-<?php echo date('Y'); ?> Rhombus Power Inc. <span class="text-success pl-2"><em class="fab fa-product-hunt mr-2"></em><?php echo RHOMBUS_PROJECT_NAME; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    const controller = '/first_admin/create_accounts';
</script>
<?php
$this->load->view('templates/essential_javascripts');
?>

<?php
$js_files = array();
$js_files['particles'] = ["particles.js", 'global'];
$js_files['particle'] = ["actions/particle.js", 'custom'];
$js_files['register'] = ["actions/register.js", 'custom'];
$CI = &get_instance();
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

?>


<?php

$this->load->view('templates/close_view');

?>