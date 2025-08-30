<?php
$page_data['page_title'] = "Account Manager";
$page_data['page_tab'] = "Account Manager";
$page_data['page_navbar'] = true;
$page_data['page_specific_css'] = array('rhombus_datatable.css', 'datatables.css', 'select2.css','bootstrap-datepicker.standalone.css');
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);
?>

<div class="w-100 text-center">
  <a class = 'btn btn-dark' href="/facs_manager">FACS Manager</a>
</div>
<?php
if (isset($sso)) {
    if (RHOMBUS_SSO_KEYCLOAK) {
      echo '<div id="app_name" class="d-flex ml-3"><span class="badge-success badge badge-pill badge-secondary mr-2"></span></div>';
    }
    $title = RHOMBUS_SSO_KEYCLOAK=='TRUE'?'Account Manager (SSO Keycloak)':'Account Manager (SSO)';
    if(HAS_SUBAPPS){
      echo datatable_card(array('ID', 'User Info', 'Status', 'Account Type', 'Expired Date', 'Requested Apps', 'Approved Apps'), 'accountManagerTable', 2, array('accountManager', $title));
    }
    else{
      echo datatable_card(array('ID', 'User Info', 'Status', 'Account Type', 'Expired Date', 'Requested Apps', 'Approved Apps','Account', 'Expiry'), 'accountManagerTable', 3, array('accountManager', $title));
    }
    echo '<script>var sso = ' . json_encode($sso) . ';</script>';
} else {
    echo datatable_card(array('ID', 'Email', 'Status', 'Account Type', 'Expired Date', 'Account', 'Expiry'), 'accountManagerTable', 2, array('accountManager', 'Account Manager'));
    echo '<script>var sso = false;var accountData = ' . json_encode($accounts) . ';</script>';
}
echo '<script>var columns = ' . json_encode($columns) . ';</script>';
$this->load->view('templates/essential_javascripts');
?>
<script>
  var account_type_select = <?php echo "'".$this->useraccounttype->generateAccountTypeMenu(null, true)."'";?>;
  var facs = <?php echo json_encode(defined(RHOMBUS_FACS) && RHOMBUS_FACS == 'TRUE')?>;
  var app_name = '<?= RHOMBUS_PROJECT_NAME; ?>';
  var HAS_SUBAPPS = '<?= HAS_SUBAPPS; ?>'
</script>

<?php
    $js_files = array();
    $CI = &get_instance();
    $js_files['bootstrap-datepicker.js'] = ["bootstrap-datepicker.js", 'global'];
    $js_files['select2'] = ["select2.full.js", 'global'];
    $js_files["datatables"] = ["datatables.min.js", 'global'];
    
    $js_files["datatables_features"] = ["global/datatables_features.js", 'custom'];
    $js_files['account_manager'] = ["actions/account_manager.js", 'custom'];
    $js_files['activate_register'] = ["actions/activate_register.js", 'custom'];
    
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
?>

<?php
  $this->load->view('templates/modals/subapps_modal');
?>
<?php
$data = array(); // Reset the data array.... Just to be safe..
$data['modal_id'] = "basic_modal";
$data['basic_modal_id'] = "account_update_modal";
$data['basic_modal_title_id'] = "account_update_modal_title";
$data['basic_modal_body_id'] = "account_update_modal_body";
$data['basic_modal_button_1_id'] = "account_update_modal_button1";
$data['basic_modal_button_2_id'] = "account_update_modal_button2";
$this->load->view('templates/modals', $data);
?>


<?php // delete confirmation modal confirmation
    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "confirm_modal";
    $data['confirm_modal_id'] = "confirm_delete_modal";
    $data['confirm_modal_title_id'] = "confirm_delete_modal_title";
    $data['confirm_modal_body_id'] = "confirm_delete_modal_body";
    $this->load->view('templates/modals', $data);
?>

<?php // account delete modal
    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "delete_modal";
    $data['delete_modal_id'] = "account_delete_modal";
    $data['title'] = "account_delete_modal_title";
    $data['message'] = "account_delete_modal_body";
    $data['cancel'] = "account_modal_cancel";
    $data['delete'] = "account_delete_submit";
    $this->load->view('templates/modals', $data);
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

<?php // register modal

  $formHtml = '
  <form class="needs-validation pt-5" novalidate id="activate_register">

  <h4 class="text-center">Activate Account</h4>
  <div class="py-4">The following email account has been requested for UI registration.</div>

  <div class="row">

    <div class="col-12">
      <div class="form-group">
        <i class="fas fa-envelope fa-fw mr-3"></i><span id = "input_email" data-toggle="tooltip" data-placement="top" title="Requested email address">tempEmail</span>
      </div>
    </div>

    <div class="col-12">
      <div class="form-group"><span data-toggle="tooltip" data-placement="top" title="Requested account type"><i class="far fa-user fa-fw mr-3"></i><span id="account_type">tempAccountType</span>
      </div>
    </div>

    <div class="col-12">
      <div class="form-group">
        <i class="fas fa-comment-alt fa-fw mr-3"></i><span id = "input_notes" data-toggle="tooltip" data-placement="top" title="Message from user">tempNotesOrMessage</span>
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
            ';
            $formHtml .= $this->useraccounttype->generateAccountTypeMenu();
            $formHtml .='
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

    <div class="col-12 d-none" id="tfa_group">
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
          <div class="col-md-4">
            <label class="checkboxcontainer">
              <p class="text-muted">CAC Reader</p>
              <input class="form-check-input" type="checkbox" value="" id="activate_CAC">
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
  </form>';

  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "register_sso";
  $data['basic_modal_id'] = "registerSSO"; 
  $this->load->view('templates/modals', $data);

  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "form_modal";
  $data['basic_modal_id'] = "formModal"; 
  $data['basic_modal_title_id'] = "formModalTitle";  
  $data['basic_modal_body_id'] = "formModalBody";   
  $data['cancel_button_id'] = "closeFormBtn"; 
  $data['modal_form_html'] = $formHtml;
  $data['modal_size'] = "modal-xl";
  $this->load->view('templates/modals', $data);


?>

<?php if (P1_FLAG): ?>
<script>
  CarbonComponents.NavigationMenu.init();
</script>
<?php endif; ?>

<?php
    $this->load->view('templates/close_view');
?>
