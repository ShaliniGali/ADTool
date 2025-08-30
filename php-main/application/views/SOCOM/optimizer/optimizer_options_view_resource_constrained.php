<style>
    .checkbox-spacing{
        margin-bottom: 16px;
    }

    #kept_tranches, #cut_perc_aloc {
        width: 200px;
        height: 75px;
    }
    .htCenter .handsontable td{
        text-align: center !important;
    }

</style>

<div class="d-flex flex-row">
    <div class="d-flex flex-column mr-5">
        <div class="d-flex flex-row">
            <?php $this->load->view('components/radioButtonGroup', [
                'name' => 'per_resource_optimizer-rc',
                'label' => 'Score Type',
                'useTile' => false,
                'radioButtons' => [
                    'Score Per Dollar' => [
                        'id' => 'r-score-per-dollar-rc',
                        'value' => true,
                        'key' => 1,
                        'checked' => false,
                        'label' => 'Score Per $'
                    ],
                    'Score' => [
                        'id' => 'r-score-rc',
                        'value' => false,
                        'key' => 2,
                        'checked' => true,
                        'label' => 'Score'
                    ]
                ]
                ]); ?>
        </div>
    </div>
</div>

<div class="d-flex flex-column mt-3">
    <label for="tranche-select" class="bx--label">Number of Tranches</label>
    <select id="tranche-select" class="tranche-select" name="number_of_tranches" style="width: 200px;">
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
    </select>
</div>

<div class="d-flex flex-column mt-3 mb-3">
    <button class="bx--btn bx--btn--primary bx--btn--sm" type="button" id="advanced-btn" style="width: 200px;">
        Advanced
    </button>
</div>

<div id="advanced-form" class="d-none flex-column mt-3">
    <div class="d-flex flex-row">
        <?php $this->load->view('components/radioButtonGroup', [
            'name' => 'cut_resource_optimizer-rc',
            'label' => 'Cut Type',
            'useTile' => false,
            'radioButtons' => [
                'Full Cut' => [
                    'id' => 'r-full-cut-rc',
                    'value' => true,
                    'key' => 1,
                    'checked' => false,
                    'label' => 'Full Cut'
                ],
                'Only Required Cuts' => [
                    'id' => 'r-required-cuts-rc',
                    'value' => false,
                    'key' => 2,
                    'checked' => true,
                    'label' => 'Only Required Cuts'
                ]
            ]
        ]); ?>
    </div>
    <div class="d-flex flex-column ">
    % Cut per Tranche
        <div id="kept_tranches"></div>
    </div>
    <div class="d-flex flex-column">
    % of $K Cuts per Tranche
        <div id="cuts_perc_alloc"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tranche-select').select2({
            minimumResultsForSearch: Infinity
        });

        $('#advanced-btn').on('click', function(){
            const $form = $('#advanced-form');
            if ($form.hasClass('d-none')) {
                $form.removeClass('d-none').addClass('d-flex');
                setTimeout(() => {
                    const kept = $('#kept_tranches').handsontable('getInstance');
                    const cuts = $('#cuts_perc_alloc').handsontable('getInstance');
                    if (kept && cuts) {
                        kept.refreshDimensions();
                        cuts.refreshDimensions();
                    }
                }, 100);
            } else {
                $form.removeClass('d-flex').addClass('d-none');
            }
        });
    });
</script>