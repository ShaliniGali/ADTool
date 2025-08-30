<style>
    #<?= $view_type ?>-dropdown {
        max-width: fit-content;
    }
    .select2-selection__rendered {
        margin: unset !important;
    }
</style>

<div id="<?= $view_type ?>-dropdown" class="d-flex flex-column mr-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center mb-1 bx--label medium-label">
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
        onchange="dropdown_onchange('<?= $view_type ?>')"
    >
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
        width: '17vw'
    });

    $(document).ready(function() {
        let type = '<?= $view_type ?>';
        let defaultSelectAll = <?= json_encode($default_select_all) ; ?>;
        if (defaultSelectAll) {
            const dropdown = $(`#${type}`);
            dropdown.val('ALL').trigger('change');
        }
    });


</script>