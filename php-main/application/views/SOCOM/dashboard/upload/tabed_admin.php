<?php
    $js_files = array();
	$CI = &get_instance();
	$js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['xlsx'] = ["xlsx.full.min.js", 'global'];
    $js_files['database_upload_tabbedUpload'] = ["actions/SOCOM/dashboard/upload/TabbedUpload.js", 'custom'];
    $js_files['database_upload_program_aligment'] = ["actions/SOCOM/dashboard/upload/ProgramAlignment.js", 'custom'];
    $js_files['database_upload_in_pom_cycle'] = ["actions/SOCOM/dashboard/upload/InPOMCycle.js", 'custom'];
    $js_files['database_upload_out_of_pom_cycle'] = ["actions/SOCOM/dashboard/upload/OutOfPOMCycle.js", 'custom'];
    $js_files['database_upload_zbt_issue'] = ["actions/SOCOM/dashboard/upload/ZBTIssue.js", 'custom'];
    $js_files['database_upload'] = ["actions/SOCOM/dashboard/database_upload.js", 'custom'];

    $this->load->model('SOCOM_Dynamic_Year_model');
    $this->load->view('SOCOM/toast_notifications');
    $this->load->model('SOCOM_Site_User_model');

    $CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

    $data = [];

    $all_pom_years = $this->SOCOM_Dynamic_Year_model->getAllPomYears();
    $latest_pom_year = $this->SOCOM_Dynamic_Year_model->getLatestPomYear();
    $data['all_pom_years'] = $all_pom_years;
    $data['latest_pom_year'] = $latest_pom_year;
    $data['filtered_pom_years'] = array_filter($all_pom_years, function($v) use ($latest_pom_year) {
        return $v >= $latest_pom_year;
    });

    $is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
    $is_guest = $this->rbac_users->is_guest();
	$is_restricted = $this->rbac_users->is_restricted();

?>
<div class="mt-3 ml-3">
    <nav aria-label="Breadcrumb">
        <ol class="bx--breadcrumb">
            <li class="bx--breadcrumb-item"><a class="bx--link" href="/dashboard">Dashboard</a></li>
            <li class="bx--breadcrumb-item bx--breadcrumb-item--current">Import and Upload</li>
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
        <li id="list-tab-myuser-1" class="bx--tabs__nav-item bx--tabs__nav-item--selected" data-target=".tab-1" role="presentation" >
            <a tabindex="0" id="tab-myuser-1" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-1" aria-selected="true">Program Alignment</a>
        </li>
        <?php if($is_pom_admin): ?>
        <li id="list-tab-myuser-2" class="bx--tabs__nav-item bx--tabs__nav-item" data-target=".tab-2" role="presentation">
            <a tabindex="1" id="tab-myuser-2" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-2" aria-selected="false">In-POM Cycle Data Upload</a>
        </li>
        <li id="list-tab-myuser-3" class="bx--tabs__nav-item bx--tabs__nav-item" data-target=".tab-3" role="presentation">
            <a tabindex="2" id="tab-myuser-3" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-3" aria-selected="false">Out-of-POM Cycle Data Upload</a>
        </li>
        <?php endif; ?>
        <?php if($is_pom_admin || $is_guest): ?>
        <li id="list-tab-myuser-4" class="bx--tabs__nav-item bx--tabs__nav-item" data-target=".tab-4" role="presentation">
            <a tabindex="3" id="tab-myuser-4" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-4" aria-selected="false">ZBT Events and Issue Events Data Upload</a>
        </li>
        <?php endif; ?>
        <?php if($is_pom_admin): ?>
        <li id="list-tab-myuser-5" class="bx--tabs__nav-item bx--tabs__nav-item" data-target=".tab-5" role="presentation">
            <a tabindex="5" id="tab-myuser-5" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-5" aria-selected="false">Upload Notifications</a>
        </li>
        <?php endif; ?>
    </ul>
    </nav>

    <div style="padding: 1rem;">
        <div id="tab-5" class="tab-5" role="tabpanel" aria-labelledby="tab-link-5" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/upload/upload_notification_view');
        ?>
        </div>

        <div id="tab-1" class="tab-1" role="tabpanel" aria-labelledby="tab-link-1" aria-hidden="false">
        <?php
            $this->load->view('SOCOM/dashboard/upload/upload_view', ['form_data' => ['cycle_name' => $cycle_name]]);
        ?>
        </div>
        
        <div id="tab-2" class="tab-2" role="tabpanel" aria-labelledby="tab-link-2" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/upload/In-POM_Cycle_Data_Upload_view', $data);
        ?>
        </div>
        <?php if ($is_pom_admin): ?>
        <div id="tab-3" class="tab-3" role="tabpanel" aria-labelledby="tab-link-3" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/upload/Out-of-POM_Cycle_Data_Upload_view');
        ?>
        </div>
        <?php endif; ?>
        <div id="tab-4" class="tab-4" role="tabpanel" aria-labelledby="tab-link-4" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/upload/ZBT_Issue_Data_Upload_view', $data);
        ?>
        </div>
        
    </div>
</div>

<script>
    const isPomAdmin = <?= json_encode($is_pom_admin); ?>;
    const isRestricted = <?= json_encode(auth_import_upload_role_restricted()); ?>;
    const isUser = <?= json_encode(auth_import_upload_role_user()); ?>;
    const isAdmin = <?= json_encode(auth_import_upload_role_admin()); ?>;
    const isGuest = <?= json_encode(auth_import_upload_role_guest()); ?>;

</script>
<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'warning_modal',
    'role' => 'warning-viewer-role',
    'title' => 'Warnings Details',
    'title_heading' => 'Warnings',
    'html_content' => $this->load->view('SOCOM/dashboard/upload/warning_modal', [], true),
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
]);
?>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'status_modal',
    'role' => 'status-viewer-role',
    'title' => 'Status Details',
    'title_heading' => 'File Status',
    'html_content' => $this->load->view('SOCOM/dashboard/upload/status_modal', 
    [], true),
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
]);
?>
<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'status_modal_cap_sponsor',
    'role' => 'status-viewer-role',
    'title' => 'Status Details',
    'title_heading' => 'File Status',
    'html_content' => $this->load->view('SOCOM/dashboard/upload/status_modal_zbt_iss_upload_cap_sponsor', 
    [], true),
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
]);
?>
<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'status_modal_admin',
    'role' => 'status-viewer-role',
    'title' => 'Status Details',
    'title_heading' => 'File Status',
    'html_content' => $this->load->view('SOCOM/dashboard/upload/status_modal_zbt_iss_upload_admin', 
    [], true),
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
]);
?>
<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'error_modal',
    'role' => 'error-viewer-role',
    'title' => 'Errors Details',
    'title_heading' => 'Errors',
    'html_content' => $this->load->view('SOCOM/dashboard/upload/error_modal', [], true),
    'buttons' => [
        [
            'class' => 'bx--btn--secondary flex-fill',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
    ],
]);
?>
