<?php
$page_data['page_title'] = "Activate";
$page_data['page_tab'] = "Activate";
$page_data['page_navbar'] = true;
$page_data['page_specific_css'] = array('rhombus_datatable.css', 'select2.css', 'bootstrap-datepicker.standalone.css');
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);

$decrypt_data = encrypted_string($hash, "decode");
if (empty($decrypt_data)) {
  redirect($base_url);
}

$user_info = $this->Login_model->user_info($decrypt_data['id'])[0];


//
//  Mark status
//
if ($user_info['status'] == "Rejected") {
  $data = array();
  $data['template'] = printout_message('<i class="fas fa-exclamation-triangle fa-5x"></i>', '<h4 class="pt-4 mt-4 text-capitalize text-muted">This account has been rejected. Link expired</h4>');
  $this->load->view('message_view', $data);
  exit();
}

//
//  Mark status
//
if ($user_info['status'] != AccountStatus::RegistrationPending) {
  $data = array();
  $data['template'] = printout_message('<i class="text-muted far fa-check-circle fa-5x"></i>', '<h4 class="pt-4 mt-4 text-capitalize text-muted">This request has been processed.</h4>');
  $this->load->view('message_view', $data);
  exit();
}



//
//  NOT made for right admin email (ADMIN LEVEL); in case Admin exchange email sent links with each other 
//
if ($decrypt_data['email'] != $this->session->userdata('logged_in')['email']) {
  $data = array();
  $data['template'] = printout_message('<i class="text-muted far fa-frown fa-5x"></i>', '<h4 class="pt-4 mt-4 text-capitalize text-muted">This request was not made for your login credentials.</h4>');
  $this->load->view('keycloak/message_view', $data);
  exit();
}

//
//  Create Activate account link
//
$data = array();
$data['id'] = $decrypt_data['id'];
$data['email'] = $user_info['email'];
$data['type'] = "admin_verify";
$data['time'] = time();
$data['account_type'] = $decrypt_data['account_type'];
$url_hash = encrypted_string($data, "encode");
echo '<script>var siteUrl = "' . $url_hash . '";</script>';
echo '<script>var hash = "' . $hash . '";</script>';
?>

<div class="container text-muted pt-5 overflow-auto h-100">
  <form class="needs-validation pt-5" novalidate id="activate_register">
    <h4 class="text-center">Activate Account</h4>
    <div class="py-4">The following email account has been requested for UI (<?php echo base_url(); ?>) registration.</div>

    <div class="row">

      <div class="col-12">
        <div class="form-group">
          <em class="fas fa-envelope fa-fw mr-3"></em><span data-toggle="tooltip" data-placement="top" title="Requested email address"><?php echo $user_info['email']; ?></span>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group">
          <em class="fas fa-id-card fa-fw mr-3"></em><span data-toggle="tooltip" data-placement="top" title="Requested user's name"><?php echo $user_info['name']; ?></span>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group"><span data-toggle="tooltip" data-placement="top" title="Requested account type"><em class="far fa-user fa-fw mr-3"></em><span id="account_type"><?php echo $decrypt_data['account_type']; ?></span>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group">
          <em class="fas fa-comment-alt fa-fw mr-3"></em><span data-toggle="tooltip" data-placement="top" title="Message from user"><?php echo $decrypt_data['message']; ?></span>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group text-info py-3">
          As a Super-Admin, you can control the above requested user account previlege type and secure login authentication method.
        </div>
      </div>

      <div class="col-12">
        <div class="form-group">
          <div class="d-flex">
            <div class="pr-3 w-25">
              <?php
              echo $this->useraccounttype->generateAccountTypeMenu();
              ?>
            </div>
            <div class="w-50">
              <input type="text" autocomplete="off" class="form-control" name="admin_expiry" id="admin_expiry" placeholder="Expiration date" required>
              <div class="pt-1 text-info" id="disp_expiry"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group">
          <label class="checkboxcontainer">
            <p class="pl-1">Enable Two-Factor Authentication (TFA) Secure Login Layer</p>
            <input class="form-check-input" type="checkbox" value="" id="activate_tfa">
            <span class="checkmark"></span>
          </label>
        </div>
      </div>

      <div class="col-12" id="tfa_group">
        <div class="position-relative border rounded pt-5 py-3">
          <div class="position-absolute ml-4 border rounded px-3 py-1 bg-light" style="top: -14px;">TFA layers</div>
          <div class="row no-gutters px-3">
            <div class="col-md-4">
              <label class="checkboxcontainer">
                <p class="text-muted">Google Authenticator</p>
                <input class="form-check-input" type="checkbox" value="" id="activate_Gauth">
                <span class="checkmark"></span>
              </label>
            </div>
            <div class="col-md-4">
              <label class="checkboxcontainer">
                <p class="text-muted">Yubikey</p>
                <input class="form-check-input" type="checkbox" value="" id="activate_Yubikey">
                <span class="checkmark"></span>
              </label>
            </div>
            <div class="d-none text-danger small pt-2" id="valid_tfa_message">
              please enable atleast one tfa layer.
            </div>
          </div>
        </div>
      </div>
      <div class="col-12">
        <div class="form-group text-center pt-5">
          <button class="btn btn-success btn-lg" id="activate_register_submit" type="submit" value="Submit"> Activate Account </button>
          <button class="btn btn-secondary btn-lg ml-4" id="reject_register_submit" type="button" value="Submit"> Reject Account </button>
        </div>
      </div>
  </form>
</div>


<?php
$this->load->view('templates/essential_javascripts');
?>

<?php
$js_files = array();
$js_files["select"] = ["select2.full.js", 'global'];
$js_files['bootstrap-datepicker.js'] = ["bootstrap-datepicker.js", 'global'];
$js_files['activate_register'] = ["actions/activate_register.js", 'custom'];
$CI = &get_instance();
$CI->load->library('RB_js_css');
$CI->rb_js_css->compress($js_files);
?>

<?php // delete confirmation modal confirmation
$data = array(); // Reset the data array.... Just to be safe..
$data['modal_id'] = "confirm_modal";
$data['confirm_modal_id'] = "confirm_reject_modal";
$data['confirm_modal_title_id'] = "confirm_reject_modal_title";
$data['confirm_modal_body_id'] = "confirm_reject_modal_body";
$this->load->view('templates/modals', $data);
?>

<?php // account reject modal
$data = array(); // Reset the data array.... Just to be safe..
$data['modal_id'] = "delete_modal";
$data['delete_modal_id'] = "account_reject_modal";
$data['title'] = "account_reject_modal_title";
$data['message'] = "account_reject_modal_body";
$data['cancel'] = "account_modal_cancel";
$data['delete'] = "account_reject_submit";
$this->load->view('templates/modals', $data);
?>


<?php
$data = array(); // Reset the data array.... Just to be safe..
$data['modal_id'] = "basic_modal";
$data['basic_modal_id'] = "error_modal";
$data['basic_modal_title_id'] = "error_modal_title";
$data['basic_modal_body_id'] = "error_modal_body";
$data['basic_modal_button_1_id'] = "error_modal_button1";
$data['basic_modal_button_2_id'] = "error_modal_button2";
$this->load->view('templates/modals', $data);
?>


<?php
$this->load->view('templates/close_view');
?>