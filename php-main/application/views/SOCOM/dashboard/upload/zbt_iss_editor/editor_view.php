<style>
    .editor-container {
        height: 70vh;
        padding: 10px;
        width: 100%;
        overflow: auto;
    }
    .search-bar-wrapper {
        padding: 10px;
        justify-content: end;
    }
    .editor-pagination-wrapper {
        padding: 10px;
        flex-grow: 1;
        display: flex;
        justify-content: center;
    }
    .sv-cl-button-wrapper {
        padding: 10px;
        display: flex;
        justify-content: flex-end;
    }
    #handsontable-editor {
        width: 100%;
        height:100%;
        overflow: auto; 
    }
    #cancel-edits-btn.bx--btn--sm,
    #save-edits-btn.bx--btn--sm {
        height: 50px !important;
        width: 175px !important;
    }
    #search-btn.bx--btn--sm {
        height: 50px !important;
    }
    #refresh-btn.bx--btn--sm {
        height: 50px !important;
    }
    #search-input.bx--search-input {
        height: 50px !important;
    }
    .col-headings-dropdown-wrapper {
        width: 200px;
    }

    .edited-cell {
        background-color: #FFFF00 !important; /* Light orange */
    }
    
</style>

<?php 

    $this->load->view('templates/essential_javascripts');
	$js_files = array();
	$CI = &get_instance();
	$js_files['select2'] = ["select2.full.js", 'global'];
    $js_files['handson'] = ["handsontable.full.min.js", 'global'];
	$js_files['toast_notifications'] = ["actions/toast_notifications.js", "custom"];
    $js_files['notifications'] = ['actions/SOCOM/notification.js', 'custom'];
    $js_files['editor_view'] = ['actions/SOCOM/dashboard/upload/editor.js', 'custom'];

	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);

	$this->load->view('SOCOM/toast_notifications');

    $mode = $this->input->get('mode');
    $is_view_mode = ($mode === 'view');
?>

<div class="d-flex flex-column" >

<div class="d-flex flex-row justify-content-between align-items-start search-bar-wrapper" style="width: 100%;">
    <div class = "mr-3 mt-3">
        <button class="bx--btn bx--btn--primary bx--btn--sm" type="button" id="refresh-btn">
            Refresh
        </button>
    </div>
    <div class="d-flex flex-row align-items-start">
    <div class="col-headings-dropdown-wrapper mr-3 mt-3">
        <select id="column-headings-dropdown" class="form-control" name="column_name">
        <option></option>
            <?php foreach (($searchable_columns ?? []) as $col): ?>
                <option value="<?= $col ?>"><?= $col ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex-grow-1 mt-3">
        <div class="bx--form-item">
            <div class="bx--search bx--search--lg" role="search">
                <label id="search-input-label" hidden for="search-input" class="bx--label">Search</label>
                <input class="bx--search-input" type="text" id="search-input" placeholder="Search..." role="searchbox">
                <button class="bx--search-close bx--search-close--hidden" type="button">
                    <svg focusable="false" preserveAspectRatio="xMidYMid meet"
                                    style="will-change: transform;" xmlns="http://www.w3.org/2000/svg"
                                    class="bx--search-clear" width="16" height="16" viewBox="0 0 32 32"
                                    aria-hidden="true">
                        <path d="M24 9.41L22.59 8 16 14.59 9.41 8 8 9.41 14.59 16 8 22.59 9.41 24
                                            16 17.41 22.59 24 24 22.59 17.41 16 24 9.41z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <button class="bx--btn bx--btn--primary bx--btn--sm ml-3 mt-3" type="submit" id="search-btn">
        Search
    </button>
    </div>
</div>

<div class="d-flex flex-row editor-container">
    <div id="handsontable-editor"></div>
</div>

<div class="d-flex flex-row justify-content-between align-items-center flex-wrap" style="width:100%">
<div class="d-flex flex-row editor-pagination-wrapper">
    <nav>
        <div class="d-flex flex-column" style="width: 200px;">
            <label for="pagination-dropdown" class="bx--label">Go to Page</label>
            <select id="pagination-dropdown" class="pagination-select" name="pagination">
            </select>
</div>
    </nav>
</div>

<?php if(!$is_view_mode) : ?>
<div class="d-flex flex-row sv-cl-button-wrapper mt-4">
    <div class="bx--btn-set">
        <button class="bx--btn bx--btn--secondary bx--btn--sm mr-2" type="button" id="cancel-edits-btn">
                    Cancel
        </button>
        <button class="bx--btn bx--btn--primary bx--btn--sm" type="submit" id="save-edits-btn">
                    Save
        </button>
    </div>
</div>
<?php endif; ?>
</div>

</div>

<script>
    let editor_start_time = '<?php echo $edit_start_time; ?>';
    const rowHeaders = <?php echo json_encode($row_headers ?? []); ?>;
    const colHeaders = <?php echo json_encode($col_headers ?? []); ?>;
    const rowDataMap = {};
    colHeaders.forEach((name, index) => {
        rowDataMap[name] = index;
    });
    const handson_license = '<?= RHOMBUS_HANDSONTABLE_LICENSE ?>';
    const usr_dt_upload = '<?= $usr_dt_upload ?>';

    const hot_col_numeric = { type: 'numeric' };
    
    const hot_col_assessment_area_cod = '';

    const col_map = {
        'default': {},
        'ASSESSMENT_AREA_CODE': hot_col_assessment_area_cod,
    }

    const view_mode = '<?= $admin_viewer ?>';

</script>