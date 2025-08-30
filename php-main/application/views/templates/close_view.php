	

    </div>  <!-- for page-container in header_view -->
    <?php
  $js_files = array();
  $CI = &get_instance();
  $js_files['socom_p1'] = ['actions/p1_socom.js','custom'];

  $CI->load->library('RB_js_css');
  $CI->rb_js_css->compress($js_files);
    ?>
	</body>
</html>