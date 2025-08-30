<?php
    $this->load->view('templates/essential_javascripts');

    $js_files = array();
	$CI = &get_instance();
	$js_files['dashboard_user'] = ['actions/SOCOM/dashboard/users.js', 'custom'];
    $js_files['dashboard_admin'] = ['actions/SOCOM/dashboard/admin.js', 'custom'];
    $js_files['datatables'] = ["datatables.min.js", 'global'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
    $js_files['notifications'] = ['actions/SOCOM/notification.js', 'custom'];
    $js_files['toast_notification'] = ["actions/toast_notifications.js", 'custom'];
    $js_files['cycle_view'] = ['actions/SOCOM/dashboard/cycle.js', 'custom'];
    $js_files['pom_center'] = ['actions/SOCOM/pom/admin.js', 'custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

    $this->load->library('form_validation');
    $this->load->view('SOCOM/toast_notifications');

?>
<div class="mt-3 ml-3">
    <nav aria-label="Breadcrumb">
        <ol class="bx--breadcrumb">
            <li class="bx--breadcrumb-item"><a class="bx--link" href="/dashboard">Dashboard</a></li>
            <li class="bx--breadcrumb-item bx--breadcrumb-item--current">Cycle Management</li>
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
        <?php if ($is_cycle_admin_user): ?>
            <li class="bx--tabs__nav-item <?php echo $is_cycle_admin_user ? 'bx--tabs__nav-item--selected' : ''; ?>" data-target=".tab-1" role="presentation" >
                <a tabindex="0" id="cycle-admin" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-1" aria-selected="<?php echo $is_cycle_admin_user ? 'true' : 'false'; ?>">Cycle Admin</a>
            </li>
        <?php endif; ?>
        <?php if ($is_cycle_admin_user || $is_weight_criteria_admin_user): ?>
            <li class="bx--tabs__nav-item <?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? 'bx--tabs__nav-item--selected' : ''; ?>" data-target=".tab-2" role="presentation" >
                <a tabindex="0" id="weight-criteria-admin" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-2" aria-selected="<?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? 'true' : 'false'; ?>">Weight Criteria Admin</a>
            </li>
        <?php endif; ?>
        <?php if ($is_cycle_admin_user): ?>
            <li class="bx--tabs__nav-item" data-target=".tab-3" role="presentation" >
                <a tabindex="0" id="pom-center-admin" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab" aria-controls="tab-panel-3">POM Center Admin</a>
            </li>
        <?php endif; ?>
    </ul>
    </nav>

    <div style="padding: 1rem;">
        <div id="cycle-admin" class="tab-1" role="tabpanel" aria-labelledby="tab-link-1" aria-hidden="<?php echo $is_cycle_admin_user ? 'false' : 'true'; ?>">
        <?php
            if ($is_cycle_admin_user) {
                $this->load->view('SOCOM/dashboard/cycle_admin/cycle_admin_view');
            }
        ?>
        </div>
        <div id="weight-criteria-admin" class="tab-2" role="tabpanel" aria-labelledby="tab-link-2" aria-hidden="<?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? 'true' : 'false'; ?>" <?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? '' : 'hidden'; ?>>
        <?php
            if ($is_cycle_admin_user || $is_weight_criteria_admin_user) {
                $this->load->view('SOCOM/dashboard/weight_criteria_admin/weight_criteria_admin_view', ['active_cycle_with_criteria' => $active_cycle_with_criteria]);
            }      
        ?>
        </div>
        <div id="pom-center-admin" class="tab-3" role="tabpanel" aria-labelledby="tab-link-3" aria-hidden="<?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? 'true' : 'false'; ?>" <?php echo !$is_cycle_admin_user && $is_weight_criteria_admin_user ? '' : 'hidden'; ?>>
        <?php
            if ($is_cycle_admin_user) {
                $this->load->view('SOCOM/dashboard/pom_center_admin/pom_center_admin_view', $data);
            }
        ?>
        </div>
    </div>
</div>

<style>
    table.dataTable thead > tr > th.sorting {
        padding-left: 18px;
    }

    table.dataTable thead .sorting {
        background-image: none;
    }

    .dataTables_filter {
        margin: 8px 0;
    }
</style>

<script>

let cycleTable;

let criteriaTermsTable;

const cycleColumnDefinition = [
    {
        title: 'Cycle Name',
        data: 'CYCLE_NAME',
    },
    {
        title: 'Description',
        data: 'DESCRIPTION',
    },
    {
        title: 'Created Date',
        data: 'CREATED_DATETIME',
    },
    {
        title: 'Active',
        data: 'IS_ACTIVE',
        render: function(data) {
            return data === 1 ? "Yes" : "No"
        }
    },
    {
        title: 'Actions',
        searchable: false,
        orderable: false,
        lengthChange: false,
        render: data => `<?php $this->load->view('SOCOM/dashboard/cycle_admin/list_overflow_menu', ['id' => '']); ?>`
    }
];

const criteriaTermsColumnDefinition = [
    {
        title: 'Term Name',
        data: 'CRITERIA_TERM',
    },
    {
        title: 'Description',
        data: 'CRITERIA_DESCRIPTION',
    },
    {
        title: 'Created Date',
        data: 'CREATED_DATETIME',
    },
    {
       title: 'Actions',
       searchable: false,
       orderable: false,
       render: function(data, type, row) {
            return `<div data-overflow-menu class="bx--overflow-menu">
                        <button class="bx--overflow-menu__trigger" aria-haspopup="true" aria-expanded="false">
                            <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" class="bx--overflow-menu__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                                <circle cx="8" cy="3" r="1"></circle>
                                <circle cx="8" cy="8" r="1"></circle>
                                <circle cx="8" cy="13" r="1"></circle>
                            </svg>
                        </button>
                        <div class="bx--overflow-menu-options">
                            <ul class="bx--overflow-menu-options__content">
                                <li class="bx--overflow-menu-options__option">
                                    <button class="bx--overflow-menu-options__btn edit-description-btn"
                                        data-id="${row.ID}"
                                        data-description="${row.CRITERIA_DESCRIPTION}"
                                        data-criteria-name-id="${row.CRITERIA_NAME_ID}">
                                        <span class="bx--overflow-menu-options__option-content">Edit Description</span>
                                    </button>
                                </li>
                                <li class="bx--overflow-menu-options__option">
                                    <button class="bx--overflow-menu-options__btn delete-description-btn"
                                        data-id="${row.ID}"
                                        data-criteria-name-id="${row.CRITERIA_NAME_ID}">
                                        <span class="bx--overflow-menu-options__option-content">Delete Description</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>`;
       }
    }   
];

</script>