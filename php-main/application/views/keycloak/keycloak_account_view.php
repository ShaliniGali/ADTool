<?php
// Ian Zablan
// June 3 2020
// Sai
// Last updated July 2 2020
$page_data['page_title'] = "Account";
$page_data['page_tab'] = "Account";
$page_data['page_navbar'] = true;
$page_data['page_specific_css'] = array('rhombus_datatable.css', 'datatables.css', 'select2.css');
$page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
$this->load->view('templates/header_view', $page_data);
?>


<div>
  <?php $this->load->view('generate_account_view', DBnames_rbUC); ?>
</div>
<div>
  <h4 class="heading pt-3 mt-0">REGISTERED ACCOUNTS</h4>
</div>
<div id="selectedDatabase" hidden><?php echo DBnames_rbUC[0];?></div>
<div class="px-3">
  <table id="accountsTable" class="table table-dark table-borderless table-striped table-hover w-100">
    <thead>
      <tr>
        <th scope="col" class="th-hover" style="width:auto;">Id</th>
        <th scope="col" class="th-hover" style="width:auto;">Name</th>
        <th scope="col" class="th-hover" style="width:auto;">Email</th>
        <th scope="col" class="th-hover" style="width:auto;">Status</th>
        <th scope="col" class="th-hover" style="width:auto;">Account Type</th>
        <th scope="col" class="th-hover" style="width:auto;">Time</th>
      </tr>
    </thead>
  </table>
  <div>





    <?php
    //  Adding Caching module
    //  All JS files must be addressed inside js folders
    $js_files = array();
    // Adding JS files with unique key. A key is defined by the purpose of adding that file
    $js_files["datatables_features"] = ["global/datatables_features.js",'custom'];
    $js_files["datatables"] = ["datatables.min.js",'global'];
    $js_files["accounts"] = ["accounts_datatable.js",'custom'];
    $CI =& get_instance();
    $CI->load->library('RB_js_css');
    $CI->rb_js_css->compress($js_files);
    ?>


    <?php
    $this->load->view('templates/close_view');
    ?>