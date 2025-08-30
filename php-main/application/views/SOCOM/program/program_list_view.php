<?php $this->load->view('templates/essential_javascripts'); ?>

<?php
	$js_files = array();
	$CI = &get_instance();
	$js_files['datatables'] = ["datatables.min.js", 'global'];
	$js_files['select2'] = ["select2.full.js", 'global'];

	$js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['notification'] = ["actions/SOCOM/notification.js",'custom'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
	$js_files['score'] = ["actions/SOCOM/score/score.js",'custom'];

    $js_files['weight_view'] = ['actions/SOCOM/weights/weight_view.js','custom'];
	$js_files['program'] = ['actions/SOCOM/optimizer/program.js','custom'];
	$js_files['coa_helper'] = ['actions/SOCOM/optimizer/coa_helper.js','custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

	$this->load->view('SOCOM/toast_notifications');
?>
<style>
	/* styles for multi-select to look like carbon select */
	#uploaded_afplan_select_container .select2-selection--multiple {
		background-color: #f4f4f4;
		border: 0;
		border-bottom: 1px solid #8d8d8d;
		border-radius: 0;
		min-height: 40px;
		cursor: pointer;
	}
	ul.select2-results__options {
		background-color: #f4f4f4;
		padding: 4px
	}
	.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
		border-radius: 4px;
	}
	#uploaded_afplan_select_container .select2-search__field {
		cursor: pointer;
	}

    .hot-display-license-info{
        display: none;
    }
	.active-cycle-position{
    padding-top: 2rem;
    padding-left: 1.0rem;
	padding-bottom: 0.5rem;
  	}
	.button-gap {
		gap: 4px;
	}
	.heading-line-position{
        padding-left: 1.0rem;
    }
	#heading-radio-program{
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		height: 30px;
	}
	#dataset-radio-buttons-program{
		margin-right: 30px;
	}
</style>

<h4 class="font-weight-bold active-cycle-position" cycleId=<?= !empty($get_active_cycle_with_criteria['CYCLE_ID']) ? $get_active_cycle_with_criteria['CYCLE_ID']: '' ?>>Active Cycle: <span id="active-cycle-name"><?= !empty($get_active_cycle_with_criteria['CYCLE_NAME']) ? $get_active_cycle_with_criteria['CYCLE_NAME'] : 'No Active Cycle' ?></span></h4>
<div id="heading-radio-program" class="heading-line-position">
    <p id="resource_coa_header" class="d-none">COA Optimization is Connected with Position Year FY<?php echo $subapp_pom_year ?> for Resource Constraining</p>
    <p id="issue_coa_header">COA Optimization is Connected with Position Year FY<?php echo $subapp_pom_year_issue ?> for Issue Optimization</p>
</div>
<div data-modal id="delete-confirmation-modal" class="bx--modal " role="dialog"
  aria-modal="true" aria-labelledby="delete-confirmation-modal-label" aria-describedby="delete-confirmation-modal-heading" tabindex="-1">
  <div class="bx--modal-container">
    <div class="bx--modal-header">
      <p class="bx--modal-header__heading bx--type-beta" id="delete-confirmation-modal-heading">Delete Option</p>
      <button class="bx--modal-close" type="button" data-modal-close aria-label="close modal" >
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--modal-close__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M12 4.7L11.3 4 8 7.3 4.7 4 4 4.7 7.3 8 4 11.3 4.7 12 8 8.7 11.3 12 12 11.3 8.7 8z"></path></svg>
      </button>
    </div>

    <!-- Note: Modals with content that scrolls, at any viewport, requires `tabindex="0"` on the `bx--modal-content` element -->

    <div class="bx--modal-content" >
	<p>Are you sure you want to delete this option? </p>
	<p id="delete-confirmation-modal-content-message"></p>

</div>
    <div class="bx--modal-content--overflow-indicator"></div>

    <div class="bx--modal-footer">
      <button id="secondary-delete-confirmation-modal-button" class="bx--btn bx--btn--secondary" type="button" data-modal-close>Close</button>
      <button id="primary-delete-confirmation-modal-button" class="bx--btn bx--btn--primary" type="button"  onclick="deleteOption()" data-modal-primary-focus>Delete</button>
    </div>
  </div>
  <!-- Note: focusable span allows for focus wrap feature within Modals -->
  <span tabindex="0"></span>
</div>


<!-- Option list DataTable -->
<div class="d-flex flex-column flex-fill align-items-center w-100 h-100">
	<div class="d-flex flex-column w-100 ml-1 pl-1 mr-3 my-3">
    <?php $this->load->view('SOCOM/weights/inline_weights_view', ['default_criteria_description' => $default_criteria_description]); ?>
	<?php $this->load->view('SOCOM/program/export_filter', ['page' => 'program_list']); ?>
	</div>
	<div class="d-flex flex-row w-100 button-gap">

		<div class="d-flex flex-column ml-3 mb-3">
			<legend class="bx--label">Optimizer Program Filter</legend>
			<button id="option_filter" class="bx--btn bx--btn--primary bx--btn--sm disabled">Filter</button>
		</div>

		<div class="d-flex flex-column mb-3">
			<legend class="bx--label">Export Program Alignment</legend>
			<button id="option_exporter" class="bx--btn bx--btn--primary bx--btn--sm">Export</button>
		</div>

	</div>
	<div class="w-100 h-100 px-3">
        <?php $this->load->view('SOCOM/program/program_list_table_view.php') ?>
    </div>

	<!-- <div class="w-100 p-3 m-3"> -->
		<!-- <button id="test_score"
		class="bx--btn bx--btn--primary"
		type="button">
		Score
		</button> -->

		<div id="score-tab-container" class="tab-4-container tab-containers"
			role="tabpanel" aria-labelledby="tab-link-4-container">
            <?php
            $this->load->view('SOCOM/score/score_tab_view', ['default_criteria' => $default_criteria]);
            ?>
  		</div>
	<!-- </div> -->
</div>

<script>
var optionFlagMap = {};
let selectWaitTimer;
let waitTimeAfterSelecting = 1000;
let fy_list =  <?=$fy_list?>;

/**
 * Initializes and populates options DataTable.
 *
 * dom options:   l = length changing input control (table page size)
 *                f = filtering input (search bar)
 *                t = the table
 *                i = table information summary (showing x of x entries)
 *                p = pagination control
 *                r = processing display element (load bar)
 * Include the letter to make it visible, you can manipulate position using these too.
 */


function handleOptionSelectChanged() {
    clearTimeout(selectWaitTimer);
    selectWaitTimer = setTimeout(function() {
		$('#option-list').DataTable().draw();
    }, waitTimeAfterSelecting);
}

function changeProgramDropdown(){
	$(`#optimizer-table`).DataTable().ajax.url('/socom/resource_constrained_coa/program/list/get/scored');
	selected_POM_weight_table.ajax.reload(() => {
        selected_Guidance_weight_table.ajax.reload($(`#optimizer-table`).DataTable().ajax.reload);
    });
}

/**
 * Listener that reselects previously changed download flag select options when changing pages in the DataTable.
 
$(function() {
	var dataTable = $('#option-list').DataTable();

	dataTable.on('draw', function() {
		for (const key in optionFlagMap) {
			$('#' + key + '-option-select').val(sanitizeHtml(optionFlagMap[key], { allowedAttributes:{'option': ['selected', 'disabled', 'value']}, allowedTags:['option']}));
			$('#' + key + '-option-select').select2().trigger('change');
		}
	});

})*/

</script>
