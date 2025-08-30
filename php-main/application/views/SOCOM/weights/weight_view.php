<?php 

    $this->load->view('templates/essential_javascripts');
	$js_files = array();
	$CI = &get_instance();
	$js_files['select2'] = ["select2.full.js", 'global'];
	$js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
	$js_files['toast_notifications'] = ["actions/toast_notifications.js", "custom"];
    $js_files['notifications'] = ['actions/SOCOM/notification.js', 'custom'];

	$js_files['storm'] = ["actions/SOCOM/weights/storm.js", 'custom'];
	$js_files['create_weights'] = ["actions/SOCOM/weights/create_weights.js", 'custom'];
    $js_files['weights_list_view'] = ['actions/SOCOM/weights/weights_list_view.js', 'custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

	$this->load->view('SOCOM/toast_notifications');
?>

<style>
  .active-cycle-position{
    padding-top: 2rem;
    padding-left: 1.5rem;
  }
</style>

<h4 class="font-weight-bold mb-2 active-cycle-position" cycleId=<?= !empty($get_active_cycle_with_criteria['CYCLE_ID']) ? $get_active_cycle_with_criteria['CYCLE_ID']: '' ?>>Active Cycle: <span id="active-cycle-name"><?= !empty($get_active_cycle_with_criteria['CYCLE_NAME']) ? $get_active_cycle_with_criteria['CYCLE_NAME'] : 'No Active Cycle' ?></span></h4>

<div data-content-switcher class="bx--content-switcher w-50 ml-3 my-5">
    <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".weight--panel--opt-1">Creation</button>
    <button class="bx--content-switcher-btn" data-target=".weight--panel--opt-2">Listing</button>
</div>

<div class="weight--panel--opt-1">
    <?php $this->load->view('SOCOM/weights/create_weights_view', $view_data);?>
</div>
<div class="weight--panel--opt-2" hidden>
    <?php $this->load->view('SOCOM/weights/weights_list_view');?>
</div>