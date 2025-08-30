<?php
  $page_data['page_title'] = "Home";
  $page_data['page_tab'] = "Home";
  $page_data['page_navbar'] = true;
  $page_data['page_specific_css'] = array();
  $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
  $this->load->view('templates/header_view', $page_data);
  
?>


<div class="px-3 h-100" >



</div>








<?php

  $this->load->view('templates/essential_javascripts');

?>

<?php

// $js_files['turf.min.js'] = ['turf.min.js','custom'];
// $js_files['boost'] = ['boost.js','custom'];

// $CI =& get_instance();
// $CI->load->library('RB_js_css');
// $CI->rb_js_css->compress($js_files);


?>



<?php

$this->load->view('templates/close_view');

?>