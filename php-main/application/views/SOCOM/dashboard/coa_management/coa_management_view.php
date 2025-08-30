<?php
    $this->load->view('templates/essential_javascripts');

    $js_files = array();
	$CI = &get_instance();
	$js_files['dashboard_user'] = ['actions/SOCOM/dashboard/users.js', 'custom'];
    $js_files['dashboard_admin'] = ['actions/SOCOM/dashboard/admin.js', 'custom'];
    $js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['select2'] = ["select2.full.js", 'global'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['notifications'] = ['actions/SOCOM/notification.js', 'custom'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['coa_share_view'] = ['actions/SOCOM/dashboard/coa_share.js', 'custom'];
    $js_files['coa_merge_view'] = ['actions/SOCOM/dashboard/coa_merge.js', 'custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

    $this->load->library('form_validation');
    $this->load->view('SOCOM/toast_notifications');

?>
<div id="overlay-loader"></div>
<div class="mt-3 ml-3">
    <nav aria-label="Breadcrumb">
        <ol class="bx--breadcrumb">
            <li class="bx--breadcrumb-item"><a class="bx--link" href="/dashboard">Dashboard</a></li>
            <li class="bx--breadcrumb-item bx--breadcrumb-item--current">COA Management</li>
        </ol>
    </nav>
</div>
<div class="mt-3 ml-3">
    <nav data-tabs class="bx--tabs" role="navigation">
    <div class="bx--tabs-trigger" tabindex="0">
        <a href="javascript:void(0)" class="bx--tabs-trigger-text" tabindex="-1"></a>
        <svg class="bx--dropdown__arrow" width="10" height="5" viewBox="0 0 10 5" fill-rule="evenodd">
            <path d="M10 0L5 5 0 0z"></path>
        </svg>
    </div>
    <ul class="bx--tabs__nav bx--tabs__nav--hidden" role="tablist">
        <li class="bx--tabs__nav-item bx--tabs__nav-item--selected" data-target=".tab-1" role="presentation" >
            <a tabindex="0" id="coa-share-tab" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-1" aria-selected="true">COA Share</a>
        </li>
        <li class="bx--tabs__nav-item" data-target=".tab-2" role="presentation" >
            <a tabindex="0" id="coa-merge-tab" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-2" aria-selected="false">Merge COA</a>
        </li>
    </ul>
    </nav>

    <?php
       $selected_value = filter_var($this->session->userdata('use_iss_extract') ?? true, FILTER_VALIDATE_BOOLEAN); // Default to 'true' (Issue Optimization)
    ?>
    <div id="dataset-radio-buttons-optimizer" class="d-flex justify-content-between align-items-center ml-auto mr-auto w-75">
    <div  style="width: 1rem;height: 1rem;">
        <?php $this->load->view('components/loading_small', ['hidden' => true]); ?></div>
        <div class="ml-1"></div>
        <?php $this->load->view('components/radioButtonGroup', [
                    'name' => 'use_iss_extract_share_coa',
                    'label' => 'POM Cycle Optimization Type',
                    'useTile' => true,
                    'radioButtons' => [
                        'ISS EXTRACT' => [
                            'id' => 'r-iss-extract-program',
                            'value' => 'true',
                            'key' => 2,
                            'checked' => ($selected_value === true),
                            'label' => 'Issue Optimization'
                        ],
                        'ISS' => [
                            'id' => 'r-iss-program',
                            'value' => 'false',
                            'key' => 1,
                            'checked' => ($selected_value === false),
                            'label' => 'Resource Constraining'
                        ]
                    ]
        ]); ?>
    </div>
</div>


    <div style="padding: 1rem;">
        <div id="coa-share-view" class="tab-1" role="tabpanel" aria-labelledby="tab-link-1" aria-hidden="false">
        <?php
            $this->load->view('SOCOM/dashboard/coa_management/coa_share_view', ['user_emails' => $user_emails, 'user_id' => $user_id]);
        ?>
        </div>
        <div id="coa-merge-view" class="tab-2" role="tabpanel" aria-labelledby="tab-link-2" aria-hidden="true" hidden>            
        <?php
            $this->load->view('SOCOM/dashboard/coa_management/coa_merge_view', ['user_emails' => $user_emails, 'user_id' => $user_id]);
        ?>
        </div>
    </div>
</div>

<script>
    fy_years = <?= json_encode($fy_years, true); ?>;
    $(document).ready(function () {
        $('input[name="use_iss_extract_share_coa"]').on('change', function () {
            let selectedValue = $(this).val();
            $.ajax({
                url: "/socom/resource_constrained_coa/program/list/update",
                type: "POST",
                data: { use_iss_extract: selectedValue, rhombus_token: rhombuscookie() },
                success: function (response) {
                    console.log("Selection updated successfully.");
                },
                error: function () {
                    console.log("Error updating selection.");
                },
                complete: function () {
                    //reset to cut button
                    $('#to_cut').val('').trigger('change')
                }
            });
        });
    })

</script>