<?php
    $page_data['page_title'] = "FACS Manager";
    $page_data['page_tab'] = "FACS Manager";
    $page_data['page_navbar'] = true;
    $page_data['page_specific_css'] = array('rhombus_datatable.css', 'datatables.css', 'select2.css', 'nav_tabs.css', 'facs.css');
    $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
    $this->load->view('templates/header_view', $page_data);
    $is_SuperAdmin = $this->useraccounttype->checkSuperAdmin();
?>
<div class="mt-3 px-3 pb-5">
  <?php
    $tileAppName = PROJECT_TILE_APP_NAME;
    if(HAS_SUBAPPS){
        $tileAccountSession = $this->session->userdata('tile_account_session');
        $tileAppName = $tileAccountSession['tile_account_name'];
    }
    $app_label = $this->Keycloak_tiles_model->get_tiles(['title'=>$tileAppName])[0]['label'];
    echo '<div id="app_name" class="d-flex ml-3"><span class="badge-success badge badge-pill badge-secondary mr-2">'.$app_label.'</span></div>';
  ?>

  <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link active" id="roles_tab" data-toggle="tab" href="#roles_content" role="tab" aria-controls="roles_content" aria-selected="true">Access Roles</a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Application Manager</a>
      <div class="dropdown-menu">
        <a class="dropdown-item" data-toggle="tab" id="apps_tab" href="#apps_content">Tiles</a>
        <a class="dropdown-item" data-toggle="tab" id="subapps_alias_tab" href="#subapps_alias_content">Application-Tags</a>
      </div>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Mapping</a>
      <div class="dropdown-menu">
      <a class="dropdown-item" data-toggle="tab" id="subapps_tab" href="#subapps_content">Modules : Applications</a>
        <a class="dropdown-item" data-toggle="tab" id="role_mappings_tab" href="#role_mappings_content">Applications : Access Roles</a>
      </div>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="features_tab" data-toggle="tab" href="#features_content" role="tab" aria-controls="features_content" aria-selected="false">Features</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="am_tab" href="/account_manager_controller/index">Account Manager</a>
    </li>
  </ul>
  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="roles_content" role="tabpanel" aria-labelledby="roles_tab">
        <?php
            echo datatable_card(array('ID', 'Name'), 'roles_table', 1, ['roles_title', 'Current Roles']);
        ?>
    </div>
    <div class="tab-pane fade" id="apps_content" role="tabpanel" aria-labelledby="apps_tab">
        <?php
            echo datatable_card(array('ID', 'Name', 'Description'), 'apps_table', 1, ['apps_title', 'Applications']);
        ?>
    </div>
    <div class="tab-pane fade" id="subapps_alias_content" role="tabpanel" aria-labelledby="subapps_alias_tab">
        <?php
            echo datatable_card(array('ID', 'Name'), 'subapps_alias_table', 1, ['subapps_alias_title', 'Application-Tags']);
        ?>
    </div>
    <div class="tab-pane fade" id="features_content" role="tabpanel" aria-labelledby="features_tab">
        <?php
            echo datatable_card(array('ID', 'Name'), 'features_table', 0, ['features_title', 'Features']);
        ?>
    </div>
    <div class="tab-pane fade" id="subapps_content" role="tabpanel" aria-labelledby="subapps_tab">
        <?php
            echo datatable_card(array('ID', 'Module', 'Application'), 'subapps_table', 1, ['subapps_title', 'Modules : Applications']);
        ?>
    </div>
    <div class="tab-pane fade" id="role_mappings_content" role="tabpanel" aria-labelledby="role_mappings_tab">
        <?php
            echo datatable_card(array('ID', 'Application', 'Module', 'Feature', 'Access Role'), 'role_mappings_table', 1, ['role_mappings_title', 'Applications : Access Roles']);
        ?>
    </div>
  </div>
</div>

<!-- ROLES ADD FORM MODAL -->
<?php
    $formHtml = '<form id="rolesForm" class="needs-validation pb-4">
    <input type="text" id="roles_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Role*</div>
        <input type = "text" id = "roles_input_name" class = "form-control" placeholder = "Name" value = "" required="">
        <div id="roles_error_name" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="rolesSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "rolesFormModal"; 
    $data['basic_modal_title_id'] = "rolesFormModalTitle";  
    $data['basic_modal_body_id'] = "rolesFormModalBody";   
    $data['cancel_button_id'] = "rolesCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- APPS ADD FORM MODAL -->
<?php
    $formHtml = '<form id="appsForm" class="needs-validation pb-4">
    <input type="text" id="apps_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Application*</div>
        <input type = "text" id = "apps_input_label" class = "form-control" placeholder = "e.g. This App" value = "" required="">
        <div id="apps_error_label" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="appsSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "appsFormModal"; 
    $data['basic_modal_title_id'] = "appsFormModalTitle";  
    $data['basic_modal_body_id'] = "appsFormModalBody";   
    $data['cancel_button_id'] = "appsCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- SUBAPPS ALIAS ADD FORM MODAL -->
<?php
    $formHtml = '<form id="subapps_aliasForm" class="needs-validation pb-4">
    <input type="text" id="subapps_alias_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Application-Tag*</div>
        <input type = "text" id = "subapps_alias_input_alias_name" class = "form-control" placeholder = "e.g. This Subapp" value = "" required="">
        <div id="subapps_alias_error_label" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="subapps_aliasSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "subapps_aliasFormModal"; 
    $data['basic_modal_title_id'] = "subapps_aliasFormModalTitle";  
    $data['basic_modal_body_id'] = "subapps_aliasFormModalBody";   
    $data['cancel_button_id'] = "subapps_aliasCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- SUBAPPS ADD FORM MODAL -->
<?php
    $formHtml = '<form id="subappsForm" class="needs-validation pb-4">
    <input type="text" id="subapps_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Module*</div>
        <div id = "subapps_input_name"></div>
        <div id="subapps_error_name" style="color: red;"></div>
        <div class="h6 text-capitalize pr-4 ">Application*</div>
        <div id = "subapps_input_subapps_alias"></div>
        <div id="subapps_error_subapps_alias" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="subappsSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "subappsFormModal"; 
    $data['basic_modal_title_id'] = "subappsFormModalTitle";  
    $data['basic_modal_body_id'] = "subappsFormModalBody";   
    $data['cancel_button_id'] = "subappsCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- FEATURES ADD FORM MODAL -->
<?php
    $formHtml = '<form id="featuresForm" class="needs-validation pb-4">
    <input type="text" id="features_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Feature*</div>
        <div id = "features_input_name"></div>
        <div id="features_error_name" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="featuresSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "featuresFormModal"; 
    $data['basic_modal_title_id'] = "featuresFormModalTitle";  
    $data['basic_modal_body_id'] = "featuresFormModalBody";   
    $data['cancel_button_id'] = "featuresCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- ROLE MAPPINGS ADD FORM MODAL -->
<?php
    $formHtml = '<form id="role_mappingsForm" class="needs-validation pb-4">
    <input type="text" id="role_mappings_input_id" class = "d-none" readonly>

    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 d-none">App*</div>
        <div id = "role_mappings_input_app" class="d-none"></div>
        <div id="role_mappings_error_app" style="color: red;"></div>
      </div>
    </div>
    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Modules*</div>
        <div id = "role_mappings_input_subapp"></div>
        <div id="role_mappings_error_subapp" style="color: red;"></div>
      </div>
    </div>
    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Feature*</div>
        <div id = "role_mappings_input_feature"></div>
        <div id="role_mappings_error_feature" style="color: red;"></div>
      </div>
    </div>
    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Access Role*</div>
        <div id = "role_mappings_input_user_roles"></div>
        <div id="role_mappings_error_user_roles" style="color: red;"></div>
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="role_mappingsSubmitRecord">. . .</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "role_mappingsFormModal"; 
    $data['basic_modal_title_id'] = "role_mappingsFormModalTitle";  
    $data['basic_modal_body_id'] = "role_mappingsFormModalBody";   
    $data['cancel_button_id'] = "role_mappingsCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<!-- AUTOPOP FORM MODAL -->
<?php
    $formHtml = '<form id="autopopForm" class="needs-validation pb-4">

    <div class = "col-12">
      <div class = "form-group">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="mapping_pattern" id="mapping_pattern_1" value="mapping_pattern_1" checked>
          <label class="form-check-label" for="mapping_pattern_1">
            Single Access Role to multiple Applications
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="mapping_pattern" id="mapping_pattern_2" value="mapping_pattern_2">
          <label class="form-check-label" for="mapping_pattern_2">
            Single Subapp to multiple Access Level
          </label>
        </div>
        <div class="form-check mb-5">
          <input class="form-check-input" type="radio" name="mapping_pattern" id="mapping_pattern_3" value="mapping_pattern_3">
          <label class="form-check-label" for="mapping_pattern_3">
            Multiple Applications to multiple Access Roles
          </label>
        </div>
        <div id = "autopop_input_user_roles_container">
          <div class="h6 text-capitalize pr-4">Access Roles*</div>
          <div id = "autopop_input_user_roles"></div>
        </div>
        <div id = "autopop_input_subapps_container">
          <div class="h6 text-capitalize pr-4 ">Applications*</div>
          <div id = "autopop_input_subapps"></div>
        </div>

        <div id="autopop_error_user_roles" style="color: red;"></div>
      </div>
      <div id="autopop_mapping_alert" class="alert alert-danger d-none" role="alert">
        Warning: You are on the brink of overwriting all current records with new data. This action cannot be undone once completed. Please exercise caution and consider the irreversible consequences before proceeding.
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="autopopSubmitRecord">MAP</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "autopopFormModal"; 
    $data['basic_modal_title_id'] = "autopopFormModalTitle";  
    $data['basic_modal_body_id'] = "autopopFormModalBody";   
    $data['cancel_button_id'] = "autopopCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-lg";
    $this->load->view('templates/modals', $data);
?>

<!-- MAPPING SUBAPPS FORM MODAL -->
<?php
    $formHtml = '<form id="subapps_mappingForm" class="needs-validation pb-4">

    <div class = "col-12">
      <div class = "form-group">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="subapps_mapping_pattern" id="subapps_mapping_pattern_1" value="subapps_mapping_pattern_1" checked>
          <label class="form-check-label" for="subapps_mapping_pattern_1">
            Single Application to multiple Modules
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="subapps_mapping_pattern" id="subapps_mapping_pattern_2" value="subapps_mapping_pattern_2">
          <label class="form-check-label" for="subapps_mapping_pattern_2">
            Single Module to multiple Applications
          </label>
        </div>
        <div class="form-check mb-5">
          <input class="form-check-input" type="radio" name="subapps_mapping_pattern" id="subapps_mapping_pattern_3" value="subapps_mapping_pattern_3">
          <label class="form-check-label" for="subapps_mapping_pattern_3">
            Multiple Modules to multiple Applications
          </label>
        </div>
        <div id = "subapps_mapping_input_subapps_container">
          <div class="h6 text-capitalize pr-4 ">Applications*</div>
          <div id = "subapps_mapping_input_subapps"></div>
        </div>
        <div id = "subapps_mapping_input_controllers_container">
          <div class="h6 text-capitalize pr-4 ">Modules*</div>
          <div id = "subapps_mapping_input_controllers"></div>
        </div>
      </div>
      <div id="subapp_mapping_alert" class="alert alert-danger d-none" role="alert">
        Warning: You are on the brink of overwriting all current records with new data. This action cannot be undone once completed. Please exercise caution and consider the irreversible consequences before proceeding.
      </div>
    </div>
    <div class="col-12 pt-5 pb-4 text-center">
      <button class="btn btn-success formBtn success" type="submit" id="subapps_mappingSubmitRecord">MAP</button>
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "subapps_mappingFormModal"; 
    $data['basic_modal_title_id'] = "subapps_mappingFormModalTitle";  
    $data['basic_modal_body_id'] = "subapps_mappingFormModalBody";   
    $data['cancel_button_id'] = "subapps_mappingCloseFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-lg";
    $this->load->view('templates/modals', $data);
?>

<!-- DELETE ROLES MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "roles_confirmDelete"; 
  $data['modal_header_id'] = "roles_confirmDeleteHeading";  
  $data['modal_body_id'] = "roles_confirmDeleteMessage";  
  $data['modal_footer_id'] = "roles_deleteModalFooter";  
  $data['cancel_button_id'] = "roles_cancelDeleteBtn"; 
  $data['continue_button_id'] = "roles_confirmDeleteBtn"; 
  $data['button_string'] = "Delete";
  $data['modal_header_value'] = "";
  $data['modal_body_value'] = "";
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);
?>

<!-- DELETE SUBAPP MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "subapps_confirmDelete"; 
  $data['modal_header_id'] = "subapps_confirmDeleteHeading";  
  $data['modal_body_id'] = "subapps_confirmDeleteMessage";  
  $data['modal_footer_id'] = "subapps_deleteModalFooter";  
  $data['cancel_button_id'] = "subapps_cancelDeleteBtn"; 
  $data['continue_button_id'] = "subapps_confirmDeleteBtn"; 
  $data['button_string'] = "Delete";
  $data['modal_header_value'] = "";
  $data['modal_body_value'] = "";
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);
?>

<!-- DELETE SUBAPP ALIAS MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "subapps_alias_confirmDelete"; 
  $data['modal_header_id'] = "subapps_alias_confirmDeleteHeading";  
  $data['modal_body_id'] = "subapps_alias_confirmDeleteMessage";  
  $data['modal_footer_id'] = "subapps_alias_deleteModalFooter";  
  $data['cancel_button_id'] = "subapps_alias_cancelDeleteBtn"; 
  $data['continue_button_id'] = "subapps_alias_confirmDeleteBtn"; 
  $data['button_string'] = "Delete";
  $data['modal_header_value'] = "";
  $data['modal_body_value'] = "";
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);
?>

<!-- DELETE FEATURES MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "features_confirmDelete"; 
  $data['modal_header_id'] = "features_confirmDeleteHeading";  
  $data['modal_body_id'] = "features_confirmDeleteMessage";  
  $data['modal_footer_id'] = "features_deleteModalFooter";  
  $data['cancel_button_id'] = "features_cancelDeleteBtn"; 
  $data['continue_button_id'] = "features_confirmDeleteBtn"; 
  $data['button_string'] = "Delete";
  $data['modal_header_value'] = "";
  $data['modal_body_value'] = "";
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);
?>

<!-- DELETE ROLE MAPPINGS MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "role_mappings_confirmDelete"; 
  $data['modal_header_id'] = "role_mappings_confirmDeleteHeading";  
  $data['modal_body_id'] = "role_mappings_confirmDeleteMessage";  
  $data['modal_footer_id'] = "role_mappings_deleteModalFooter";  
  $data['cancel_button_id'] = "role_mappings_cancelDeleteBtn"; 
  $data['continue_button_id'] = "role_mappings_confirmDeleteBtn"; 
  $data['button_string'] = "Delete";
  $data['modal_header_value'] = "";
  $data['modal_body_value'] = "";
  $data['hasCancel'] = true;
  $this->load->view('templates/modals', $data);
?>

<!-- DUPLICATE RECORD MODAL -->
<?php

  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "noticeModal";
  $data['basic_modal_id'] = "duplicateRecord"; 
  $data['modal_header_id'] = "duplicateRecordHeading";  
  $data['modal_body_id'] = "duplicateRecordMessage";  
  $data['modal_footer_id'] = "duplicateRecordFooter";  
  $data['cancel_button_id'] = "cancelDuplicateRecordBtn"; 
  $data['continue_button_id'] = "confirmDuplicateRecordBtn"; 
  $data['button_string'] = "Continue";
  $data['modal_header_value'] = "This Record already exists";
  $data['modal_body_value'] = "Please try again.";
  $data['hasCancel'] = false;
  $this->load->view('templates/modals', $data);

?>

<?php
  $this->load->view('templates/essential_javascripts');
?>

<script>
  var roles = <?php echo json_encode($roles); ?>;
</script>

<?php
  $js_files = array();
  $CI = &get_instance();
  $js_files["datatables"] = ["datatables.min.js", 'global'];
  $js_files["datatables_features2"] = ["global/datatables_features2.js", 'custom'];
  $js_files["select"] = ["select2.full.js",'global'];
  $js_files["roles_table"] = ["facs/roles_table.js", 'custom'];
  $js_files["apps_table"] = ["facs/apps_table.js", 'custom'];
  $js_files["subapps_alias_table"] = ["facs/subapps_alias_table.js", 'custom'];
  $js_files["subapps_table"] = ["facs/subapps_table.js", 'custom'];
  $js_files["features_table"] = ["facs/features_table.js", 'custom'];
  $js_files["role_mappings_table"] = ["facs/role_mappings_table.js", 'custom'];

  $CI->load->library('RB_js_css');
  $CI->rb_js_css->compress($js_files);
?>


<?php
    $this->load->view('templates/close_view');
?>