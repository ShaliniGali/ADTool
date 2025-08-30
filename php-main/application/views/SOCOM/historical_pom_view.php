<style>
    .green-cell{
        background-color: #04d704;
    }

    .red-cell{
        background-color: red;
    }

    .yellow-cell{
        background-color: yellow;
    }

    .red-text{
        color: red;
    }

    .table-container {
        justify-content: center;
    }

    #program-description{
        text-align: center;
    }

    #details-eoc-code-dropdown-container .select2-selection__rendered {
        margin: unset !important
    }

    .historical-pom-switch {
        width: 25%;
    }

    #details-eoc-code-dropdown {
        width: 16vw;
    }

</style>

<div id="details-eoc-code-dropdown-container" class="w-100 d-flex justify-content-start my-2">
    <div id="details-eoc-code-dropdown" class="d-flex flex-column mr-4 mt-2">
        <div class="d-flex align-items-center justify-content-between">
            <div class="mb-1 bx--label medium-label">EOC Code</div>
            <div>
                <button id="details-eoc-code-selection" class="bx--btn bx--btn--primary bx--btn--icon-only bx--btn--sm mr-auto button-height" 
                data-select-all="true"
                type="button" onclick="dropdown_selection('#details-eoc-code')"> 
                    Select All
                </button>
                </div>
        </div>
        <select
                id="details-eoc-code"
                type="eoc-code"
                combination-id=""
                class="selection-dropdown"
                onchange="dropdown_onchange('details','eoc-code')"
                multiple="multiple"
                >
            <option option="ALL">ALL</option>
            <?php foreach($eoc_code as $value): ?>
                <option value="<?= $value; ?>"><?= $value; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div id="historical-pom-data-view-container">
    <?php $this->load->view('SOCOM/historical_pom_data_view'); ?>
</div>


<script>
    $(function() {
        // eoc filter
        $(`#details-eoc-code`).select2({
            placeholder: "Select an option",
            width: '16vw'
        })
        .on('change.select2', function() {
                var dropdown = $(this).siblings('span.select2-container');
                if (dropdown.height() > 100) {
                    dropdown.css('max-height', '100px');
                    dropdown.css('overflow-y', 'auto');
                }
        })

        $('#details-eoc-code-selection').trigger('click');
        $('#details-eoc-code').val('ALL').trigger('change');
    })
</script>