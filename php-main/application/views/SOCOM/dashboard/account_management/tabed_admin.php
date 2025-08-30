<?php
    $this->load->view('templates/essential_javascripts');

    $js_files = array();
	$CI = &get_instance();
	$js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];

	$js_files['dashboard_user'] = ['actions/SOCOM/dashboard/users.js', 'custom'];
    $js_files['dashboard_admin'] = ['actions/SOCOM/dashboard/admin.js', 'custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

    $this->load->view('SOCOM/toast_notifications');

?>
<div class="mt-3 ml-3">
    <nav aria-label="Breadcrumb">
        <ol class="bx--breadcrumb">
            <li class="bx--breadcrumb-item"><a class="bx--link" href="/dashboard">Dashboard</a></li>
            <li class="bx--breadcrumb-item bx--breadcrumb-item--current">Account Management</li>
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
        <li class="bx--tabs__nav-item bx--tabs__nav-item--selected " data-target=".tab-1" role="presentation" >
            <a tabindex="0" id="tab-myuser-1" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-1" aria-selected="true">My User Admin</a>
        </li>
        <?php if($is_user): ?>
        <?php if ($is_super_admin === true): ?>
        <li id="list-tab-2" class="bx--tabs__nav-item" data-target=".tab-2" role="presentation" >
            <a tabindex="0" id="tab-admin-2" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-2">User Admin List</a>
        </li>
        <?php endif; ?>
        <?php if ($is_super_admin === true || $is_group_admin === true): ?>
        <li id="list-tab-3" class="bx--tabs__nav-item" data-target=".tab-5" role="presentation" >
            <a tabindex="0" id="tab-pom-user-5" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-5">Pom List</a>
        </li>
        <?php endif; ?>
        <?php if ($is_super_admin === true || $is_group_admin === true): ?>
        <li id="list-tab-4" class="bx--tabs__nav-item" data-target=".tab-3" role="presentation" >
            <a tabindex="0" id="tab-aoad-user-3" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-3">AO and AD List</a>
        </li>
        <li id="list-tab-5" class="bx--tabs__nav-item" data-target=".tab-4" role="presentation" >
            <a tabindex="0" id="tab-cycle-user-4" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-4">Cycle and Weight List</a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
        <li id="list-tab-6" class="bx--tabs__nav-item" data-target=".tab-6" role="presentation" >
            <a tabindex="0" id="tab-cap-user-6" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-6">Cap Sponsor List</a>
        </li>
    </ul>
    </nav>

    <div style="padding: 1rem;">
        <div id="tab-myuser-1" class="tab-1" role="tabpanel" aria-labelledby="tab-link-1" aria-hidden="false">
        <?php
            $this->load->view('SOCOM/dashboard/account_management/myuser');
        ?>
        </div>
        <?php if ($is_user): ?>
        <?php if ($is_super_admin === true): ?>
        <div id="tab-admin-2" class="tab-2" role="tabpanel" aria-labelledby="tab-link-2" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/account_management/admins');
        ?>
        </div>
        <?php endif; ?>
        <div id="tab-pom-user-5" class="tab-5" role="tabpanel" aria-labelledby="tab-link-5" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/account_management/pom_users');
        ?>
        </div>
        <?php if ($is_super_admin === true || $is_group_admin === true): ?>
        <div id="tab-aoad-user-3" class="tab-3" role="tabpanel" aria-labelledby="tab-link-3" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/account_management/ao_ad_users');
        ?>
        </div>
        <?php endif; ?>
        <?php if ($is_super_admin === true || $is_group_admin === true): ?>
        <div id="tab-cycle-user-4" class="tab-4" role="tabpanel" aria-labelledby="tab-link-4" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/account_management/cycle_users');
        ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div id="tab-cap-user-6" class="tab-6" role="tabpanel" aria-labelledby="tab-link-6" aria-hidden="true" hidden>
        <?php
            $this->load->view('SOCOM/dashboard/account_management/cap_users');
        ?>
        </div>
    </div>
</div>