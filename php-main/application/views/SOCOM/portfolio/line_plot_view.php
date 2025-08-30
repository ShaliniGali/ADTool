<div id="<?= $type; ?>-line-plot-view" class="d-flex flex-row align-items-center w-100 h-100">
    <div id="<?= $type; ?>-container" class="line-plot-container w-100">
        <div id="<?= $type; ?>-line-plot" class="line-plot"></div>
        <div id="<?= $type; ?>-line-plot-disclaimer" class="chart-disclaimer"></div>
    </div>

    <?php if ($type == 'budget-trend-overview'): ?>
        <div class="d-flex flex-column m-2">
            <div class="bx--form-item mb-3">
                <input class="bx--toggle-input" id="toggle-pb-lines" type="checkbox" checked>
                <label class="bx--toggle-input__label" for="toggle-pb-lines"> PB Lines
                    <span class="bx--toggle__switch">
                        <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                        <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                    </span>
                </label>
            </div>

            <?php $this->load->view('components/radioButtonGroup', [
                'name' => 'dollar',
                'label' => 'Dollar',
                'radioGroupClass' => 'd-flex flex-column align-items-start',
                'radioButtons' => [
                    'Constant FY25 $' => [
                        'id' => 'constant-fy',
                        'value' => 'constant-fy',
                        'key' => 1,
                        'checked' => true,
                        'label' => 'Constant FY25 $',
                    ],
                    'Then-Year $' => [
                        'id' => 'then-year',
                        'value' => 'then-year',
                        'key' => 2,
                        'checked' => false,
                        'label' => 'Then-Year $'
                    ]
                ]
                ]); ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .chart-disclaimer{
        color: #555;
        text-align: center;
        font-size: 16px;
    }
</style>
<script></script>
