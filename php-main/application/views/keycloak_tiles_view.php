<?php
$page_data['page_title'] = "Home";
$page_data['page_tab'] = "Home";
$page_data['page_navbar'] = true;
$page_data['page_specific_css'] = array('keycloak_tiles.css', 'rhombus_datatable.css', 'select2.css');
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);


?>

<div class="px-3 h-100 overflow-auto" style="padding-top:70px">
  <div class="d-flex">
    <button class="btn btn-secondary ml-auto mr-3" type="button" id="openRegisterModalButton">Register</button>
  </div>
  <div class="d-flex flex-md-row flex-container">
    <?php
    $tile_icon_path = 'assets/images/keycloak_tiles/';
    $apps = [];
    foreach ($tiles as $index=>$tile) {
      if(in_array($tile['id'], Keycloak_show_tiles)){
        $disabled = in_array($tile['id'], Keycloak_disable_tiles)? 'disabled':'';
        if($disabled !== 'disabled'){
          $apps[] = [$tile['title'], $tile['title']];
        }
        echo '<a class="card bg-white m-3 btn p-0 '.$disabled.'" style="width: 18rem;" onclick="showDescription('.$index.')">
                <div class="card-body border">
                <img src="'.$tile_icon_path.$tile['icon'].'" class="float-left text-center p-3 mr-4" style="width:5em;"></img>
                <h5 class="card-title">' . $tile['title'] . '</h5>
                <p class="card-text text-muted">' . $tile['note'] . '</p>
                </div>
            </a>';
      }
    }
    ?>
  </div>
</div>

<!-- REGISTER FORM MODAL -->
<?php
    $formHtml = '<form id="registerForm" class="needs-validation pb-4">
    <div class="row px-3">
      <div class = "col-12 col-md-6">
        <div class = "form-group">
          <div class="h6 text-capitalize pr-4 ">First Name *</div>
          <input type = "text" id = "input_firstName" class = "form-control" placeholder = "e.g. John" value = "" readonly>
          <div id="error_firstName" style="color: red;"></div>
        </div>
      </div>
      <div class = "col-12 col-md-6">
        <div class = "form-group">
          <div class="h6 text-capitalize pr-4 ">Last Name *</div>
          <input type = "text" id = "input_lastName" class = "form-control" placeholder = "e.g. Smith" value = "" readonly>
          <div id="error_lastName" style="color: red;"></div>
        </div>
      </div>
    </div>
    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Email*</div>
        <input type="email" class="form-control" id="input_email" placeholder="e.g. example@email.com" value="" readonly>
        <div id="error_email" style="color: red;"></div>
      </div>
    </div>
    <div class = "col-12">
      <div class = "form-group">
        <div class="h6 text-capitalize pr-4 ">Application *</div>
        <div id = "input_app"></div>
        <div id="error_app" style="color: red;"></div>
      </div>
    </div>

    <div class="col-12 pt-5 pb-4 text-center">
      <button type="button" class="btn btn-dark formBtn cancel" data-dismiss="modal">CANCEL</button>
      <button class="btn btn-success formBtn success" type="submit" id="tiles_register_submit">Register</button>
    </div>
    </form>';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "registerModal"; 
    $data['basic_modal_title_id'] = "registerModalTitle";  
    $data['basic_modal_body_id'] = "registerModalBody";   
    $data['cancel_button_id'] = "closeRegisterFormBtn"; 
    $data['modal_form_html'] = $formHtml;
    $data['modal_size'] = "modal-l";
    $this->load->view('templates/modals', $data);
?>

<!-- RESULTS MODAL -->
<?php
  $data = array(); // Reset the data array.... Just to be safe..
  $data['modal_id'] = "basic_modal";
  $data['basic_modal_id'] = "result_modal";
  $data['basic_modal_title_id'] = "result_modal_title";
  $data['basic_modal_body_id'] = "result_modal_body";
  $data['basic_modal_button_1_id'] = "result_modal_button1";
  $data['basic_modal_button_2_id'] = "result_modal_button2";
  $this->load->view('templates/modals', $data);
?>

<!-- TILE EXPAND MODAL -->
<?php
    $html = '
      <div class = "row">
          <div class = "col-12">
              <div class = "form-group">
              <div class="h6 text-uppercase pt-4 ">Application</div>
              <input type = "text" id = "input_application" class = "form-control" readonly>

              <div class="h6 text-uppercase pt-4 ">Description</div>
              <textarea id = "input_description" class = "form-control" rows = "10" readonly></textarea>
              <div class="col-12 pt-5 pb-4 text-center">
                <button class="btn btn-success formBtn success" id="redirectApp">Login</button>
              </div>
          </div>
      </div>
    ';

    $data = array(); // Reset the data array.... Just to be safe..
    $data['modal_id'] = "form_modal";
    $data['basic_modal_id'] = "msgModal"; 
    $data['basic_modal_title_id'] = "msgModalTitle";  
    $data['basic_modal_body_id'] = "msgModalBody";   
    $data['cancel_button_id'] = "closeMsgBtn"; 
    $data['modal_form_html'] = $html;
    $data['modal_size'] = "modal-xl";
    $this->load->view('templates/modals', $data);
?>

<?php

$this->load->view('templates/essential_javascripts');

?>

<script>
  var tiles = <?php echo json_encode($tiles); ?>;
  var apps = <?php echo json_encode($apps); ?>;
  var user_data = <?php echo json_encode($user_data); ?>;
</script>

<?php

$js_files = array();

$js_files["select"] = ["select2.full.js",'global'];
$js_files["datatables_features"] = ["global/datatables_features.js", 'custom'];
$js_files["keycloak_tiles"] = ["keycloak_tiles.js",'custom'];

$CI =& get_instance();
$CI->load->library('RB_js_css');
$CI->rb_js_css->compress($js_files);

?>

<?php

$this->load->view('templates/close_view');

?>