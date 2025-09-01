<style>

    .select2-selection__rendered {
        margin: unset !important;
    }

    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 12px;
        min-height: 38px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border-radius: 4px;
        background-color: #e9f5ff;
        border: 1px solid #b6dfff;
        margin-top: 4px;
        margin-right: 4px;
        font-size: 0.875rem;
        color: #004080;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        margin-right: 4px;
        color: #004080;
    }


    #<?= $view_type ?>-selection {
        padding: 6px 12px;
        margin-left: 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        line-height: 1.2;
        cursor: pointer;
        box-shadow: none;
        transition: background-color 0.3s ease, color 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #0078d4;
        background-color: #0078d4;
        color: white;
    }

    #<?= $view_type ?>-selection:hover {
        background-color: #005a9e;
        border-color: #005a9e;
        color: white;
        box-shadow: 0 0 6px rgba(0, 120, 212, 0.6);
    }


    #<?= $view_type ?>-selection.button-height {
        height: 32px;
        padding-top: 0;
        padding-bottom: 0;
    }

    #<?= $view_type ?>-dropdown {
        width: <?= $width ?> !important;
        border: 0;
    }

   
</style>



<div id="<?= $view_type ?>-dropdown" class="d-flex flex-column <?= $class ?> mr-2">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <div class="d-flex align-items-center bx--label medium-label">
            <?= $title ?>
        </div>
        <?php if ($select_all_button): ?>
            <div>
                <button id="<?= $view_type ?>-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height"
                    data-select-all="<?= $default_select_all ? 'false' : 'true' ?>"
                    type="button" onclick="dropdown_selection('#<?= $view_type ?>')">
                    <?= $default_select_all ? 'Deselect All' : 'Select All' ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <select
        id="<?= $view_type ?>"
        type="<?= $view_type ?>"
        combination-id=""
        class="selection-dropdown"
        multiple="multiple"
        onchange="dropdown_onchange('<?= $view_type ?>')">
        <?php if ($select_all_button): ?>
            <option value="ALL" <?= $default_select_all ? 'selected' : '' ?>>ALL</option>
        <?php endif; ?>
        <?php foreach ($options as $option): ?>
            <option value="<?= $option ?>"><?= $option ?></option>
        <?php endforeach; ?>
    </select>
</div>

<script>
    var type = '<?= $view_type ?>';
    lastSelectedItemsMap[type] = [];

    $(`#${type}`).select2({
        placeholder: "Select an option",
        width: '<?= $width ?>'
    });

    $(document).ready(function() {
        let type = '<?= $view_type ?>';
        let defaultSelectAll = <?= json_encode($default_select_all); ?>;
        if (defaultSelectAll) {
            const dropdown = $(`#${type}`);
            dropdown.val('ALL').trigger('change');
        }
    });
</script>