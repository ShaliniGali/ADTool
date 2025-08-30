<?php
$page_data['page_title'] = "Reset Password";
$page_data['page_tab'] = "Reset Password";
$page_data['page_navbar'] = false;
$page_data['page_specific_css'] = array();
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);

echo '<script> var username = "' . $email . '";</script>';
?>      

<div class="px-3 text-center pt-5 mt-5">
    <div class="card col-md-4 align-center mt-5" style="margin: 0 auto; /* Added */float: none; /* Added */margin-bottom: 10px;">
        <div class="card-body">
            <div id="reset-card">
                <h5 class="card-title text-muted mt-4">PASSWORD RESET</h5>
                <form id="rhombus_password_reset" class="needs-validation py-3" novalidate>
                    <label class="col-md-12 text-dark text-left">New Password *</label>
                    <div class="col-md-12 mb-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Password"><em class="fas fa-lock"></em></span>
                            </div>
                            <input type="password" class="form-control border-0" id="user_password_reset" style="border-radius:1px;" placeholder="New password" autocomplete="off" value="" required>
                            <div class="input-group-append show-password">
                                <span class="input-group-text text-muted bg-dark border-dark input_icons">
                                    <a href="" class="text-muted input_icons"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </span>
                            </div>
                        </div>
                        <div class="d-none text-danger small pt-1" id="user_password_reset_msg">
                            Please follow the instructions(Hint) given below and enter a valid password 
                        </div>
                    </div>
                    <label class="col-md-12 text-dark text-left">Confirm New Password *</label>
                    <div class="col-md-12 mb-3 pb-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text text-muted bg-dark border-dark input_icons" data-toggle="tooltip" data-placement="top" title="Retype Password"><em class="fas fa-lock"></em></span>
                            </div>
                            <input type="password" class="form-control border-0" id="user_password_reset_confirm" style="border-radius:1px;"  placeholder="Confirm New password" autocomplete="off" value="" required>
                            <div class="input-group-append show-password">
                                <span class="input-group-text text-muted bg-dark border-dark input_icons">
                                    <a href="" class="text-muted input_icons"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </span>
                            </div>
                        </div>
                        <div class="d-none text-danger small pt-1" id="user_password_reset_confirm_msg"></div>
                    </div>
                    <div>
                        <p class="text-muted text-center" style="font-size:13px;">
                            Hint: The password should contain at least one upper case letter (A – Z), at least one lower case letter(a-z), at least one digit (0 – 9), at least one special characters of !@#$%&*().
                        </p>
                    </div>
                    <button class="btn btn-success mt-2 w-100" type="submit" id="rhombus_password_reset_submit"><i class="fa fa-key pr-2" aria-hidden="true"></i>Reset Password</button>
                </form>
            </div>
            <div id="success-card">
            </div>
        </div>
    </div>
</div>


<?php
$this->load->view('templates/essential_javascripts');
?>

<?php
$js_files = array();

if(UI_USERNAME_PASS_AUTH === 'TRUE' && UI_EMAIL_SEND === 'TRUE'){
    $js_files['login'] = ["login/reset_login_password.js", 'custom'];

    $CI = &get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
}

?>

<?php
$data = array(); // Reset the data array.... Just to be safe..
$data['modal_id'] = "basic_modal";
$data['basic_modal_id'] = "reset_password_modal";
$data['basic_modal_title_id'] = "reset_password_modal_title";
$data['basic_modal_body_id'] = "reset_password_modal_body";
$data['basic_modal_button_1_id'] = "reset_password_modal_button1";
$data['basic_modal_button_2_id'] = "reset_password_modal_button2";
$this->load->view('templates/modals', $data);
?>

<?php
$this->load->view('templates/close_view');
?>