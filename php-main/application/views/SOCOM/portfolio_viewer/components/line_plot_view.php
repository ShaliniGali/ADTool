<div id="<?= $type; ?>-line-plot-view" class="d-flex flex-column w-100 h-100">
    <div id="<?= $type; ?>-container" class="line-plot-container w-100 ">

        <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-3 flex-wrap" id="<?= $type; ?>-header">
            <h4 class="chart-title ml-3" id="<?= $type; ?>-chart-title"></h4>

            <?php if ($type == 'budget-trend-overview'): ?>
                <div class="d-flex align-items-center flex-wrap m-3 gap-3">

                    <div class="d-flex align-items-center toggle-group mb-4 mr-5">
                        <label for="toggle-pb-lines" class="form-label me-2 mb-0 toggle-label">PB Lines</label>
                        <input class="bx--toggle-input" id="toggle-pb-lines" type="checkbox" checked>
                        <label class="bx--toggle-input__label d-flex align-items-center mb-0" for="toggle-pb-lines">
                            <span class="bx--toggle__switch">
                                <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                                <span class="bx--toggle__text--on" aria-hidden="true">On</span>
                            </span>
                        </label>
                    </div>

                    <?php $this->load->view('components/radioButtonGroup', [
                        'name' => 'dollar',
                        'label' => '',
                        'radioGroupClass' => 'd-flex flex-row align-items-center gap-3 mb-0',
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

        <div id="<?= $type; ?>-line-plot" class="line-plot"></div>
        <div id="<?= $type; ?>-line-plot-disclaimer" class="chart-disclaimer"></div>
    </div>
</div>

<style>
    .chart-title {
        font-size: 1.25rem;
        font-weight: 500;
        color: #161616;
        white-space: nowrap;
        margin-right: 1rem;
    }

    .chart-disclaimer {
        color: #555;
        text-align: center;
        font-size: 16px;
        margin-top: 1rem;
    }

    #<?= $type; ?>-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        width: 100%;
    }

    .line-plot-container {
        background: #fff;
        border-radius: 12px;
    }

    #<?= $type; ?>-header>h4,
    #<?= $type; ?>-header>div {
        flex-shrink: 0;
    }

    #<?= $type; ?>-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        width: 100%;
        background: linear-gradient(to right, rgb(246, 246, 246), #ffffff);
       
        padding: 1rem;
       
        border-radius: 12px 12px 0 0;
       
    }


    .bx--toggle-input__label {
        margin-bottom: 0;
    }

    .toggle-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dollar-radio-group {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        #<?= $type; ?>-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .toggle-group,
        .dollar-radio-group {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }

    #<?= $type; ?>-line-plot {
        padding: 1rem;
       
    }

    #<?= $type; ?>-line-plot-disclaimer {
        padding: 0 1rem 1rem 1rem;
       
    }
</style>

