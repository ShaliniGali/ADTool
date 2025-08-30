<?php
  $page_data['page_title'] = "Mass Account Registration";
  $page_data['page_tab'] = "Mass Account Registration";
  $page_data['page_navbar'] = true;
  $page_data['page_specific_css'] = array('rhombus_datatable.css');
  $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
  $this->load->view('templates/header_view', $page_data);
  
?>
<div class = "h3 mt-5 mx-5 text-muted">Mass Account Registration</div>
<div class="text-muted mx-5 my-4">
  <strong class="text-warning">Note!</strong> Users registered via this feature will NOT be notified by email. Insert below one or many emails separated by a comma, newline, or both for user registration: 
</div>

<div class = "mx-5">
  <form id="add_accounts_form" class="needs-validation">
      <div class = "form-group mb-0">
          <textarea id = "input_accounts" class = "form-control bg-white" rows = "10" placeholder = "<?php echo $placeholder;?>" required = "" value = "" oninput="check_account_input()"></textarea>

          <div class="col-12 my-3 text-center">
              <button class="btn btn-success success px-4" disabled type="button" id="send_accounts" style="cursor:default">Generate Accounts</button>
          </div>

          <div id = "submit_result"></div>
      </div>
  </form>
</div>

<div id="results_div" class = "mx-5 mb-5 d-none">
  <div id="info_tip" class="text-muted mb-3 d-none">
    <strong class="text-info">Tip!</strong> You may hover over the symbols to display more information.
  </div>
  <div class="table-responsive-sm pb-5">
  <table class="table table-hover table-dark">
    <thead class = "bg-dark">
      <tr>
        <th scope="col">Emails</th>
        <th scope="col" class="text-center">Non-SSO <em class="fas fa-info-circle ml-1" data-toggle='tooltip' data-placement='top' title="Accounts related to the Login and Registration modules"></em></th>
        <th scope="col" class="text-center">SSO <em class="fas fa-info-circle ml-1" data-toggle='tooltip' data-placement='top' title="Accounts related to the Single Sign-On (SSO) module"></em></th>
        <th scope="col" class="text-center">SSO Keycloak <em class="fas fa-info-circle ml-1" data-toggle='tooltip' data-placement='top' title="Accounts related to the Single Sign-On (SSO) Keycloak module"></em></th>
      </tr>
    </thead>
    <tbody id ="results_table">
    </tbody>
  </table>
  </div>
</div>
<?php
  $modal_heading = "Users registered via this feature will NOT be notified by email.";
  $result_html = "
  <div id='confirm_generate_emails_header'class='pb-2'>The following emails <span class='text-success'>will be processed</span> for registration.</div>
  <div id='confirm_generate_emails' style='max-height:5em;' class='overflow-auto bg-white p-3 rounded'></div>

  <div id='confirm_error_emails_header' class='py-2 d-none'>The following email domains are <span class='text-danger'>not allowed</span>.</div>
  <div id='confirm_error_emails' style='max-height:5em;' class='overflow-auto bg-white p-3 rounded d-none'></div>

  <div id='confirm_duplicate_emails_header' class='py-2 d-none'>The following emails had one or more identical emails. Only one of each is kept for processing.</div>
  <div id='confirm_duplicate_emails' style='max-height:5em;' class='overflow-auto bg-white p-3 rounded d-none'></div>
  ";
  
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "confirm_generate"; 
  $data['modal_header_id'] = "confirm_generate_heading";  
  $data['modal_body_id'] = "confirm_generate_message";  
  $data['modal_footer_id'] = "confirm_generate_footer";  
  $data['cancel_button_id'] = "confirm_generate_cancel"; 
  $data['continue_button_id'] = "confirm_generate_confirm"; 
  $data['button_string'] = "Confirm";
  $data['modal_header_value'] = $modal_heading;
  $data['modal_body_value'] = $result_html;
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);

?>

<?php
  $this->load->view('templates/essential_javascripts');
?>

<?php

$js_files = array();
$js_files["generic_form"] = ["global/generic_form.js",'custom'];
$js_files["auto_generate"] = ["NIPR/sso_users_registration.js",'custom'];

$CI =& get_instance();
$CI->load->library('RB_js_css');
$CI->rb_js_css->compress($js_files);

?>

<?php

$this->load->view('templates/close_view');

?>
