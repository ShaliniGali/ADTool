<div>
    <h3>Proposed Budget</h3>
    <div class="d-flex flex-row mt-3">
        <?php foreach($fy_years as $year):?>
            <div data-text-input
                class="bx--form-item bx--text-input-wrapper m-1" style="flex: 1;">
                <label for="text-input-budget-<?= $year; ?>" class="bx--label">
                    <?= 'FY'. $year; ?> *
                </label>
                <div class="bx--text-input__field-wrapper" data-invalid>
                    <svg id="invalid-icon-<?= $year; ?>" focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--text-input__invalid-icon hidden" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8,1C4.2,1,1,4.2,1,8s3.2,7,7,7s7-3.1,7-7S11.9,1,8,1z M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2	c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z"></path><path d="M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8	c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z" data-icon-path="inner-path" opacity="0"></path>
                    </svg>
                    <input id="text-input-budget-<?= $year; ?>" type="number"
                    class="bx--text-input bx--text-input--light"
                    placeholder=""
                    value="0">
                </div>
                <div class="bx--form-requirement hidden" id="invalid-text-<?= $year; ?>">
                    Invalid Input
                </div>
            </div>
        <?php endforeach; ?>
        <div data-text-input
            class="bx--form-item bx--text-input-wrapper m-1" style="flex: 1;">
            <label for="text-input-budget-fydp" class="bx--label">
                FYDP $K
            </label>
            <div class="bx--text-input__field-wrapper" data-invalid>
                <svg id="invalid-icon-fydp" focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--text-input__invalid-icon hidden" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8,1C4.2,1,1,4.2,1,8s3.2,7,7,7s7-3.1,7-7S11.9,1,8,1z M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2	c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z"></path><path d="M7.5,4h1v5h-1C7.5,9,7.5,4,7.5,4z M8,12.2c-0.4,0-0.8-0.4-0.8-0.8s0.3-0.8,0.8-0.8	c0.4,0,0.8,0.4,0.8,0.8S8.4,12.2,8,12.2z" data-icon-path="inner-path" opacity="0"></path>
                </svg>
                <input id="text-input-budget-fydp" type="number"
                class="bx--text-input bx--text-input--light"
                placeholder=""
                value="0" disabled>
            </div>
            <div class="bx--form-requirement hidden" id="invalid-text-<?= $year; ?>">
                Invalid Input
            </div>
        </div>
    </div>
</div>

<style>
    .select2-selection__rendered {
        margin: unset !important;
    }
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }

    .hidden {
      display: none !important;
    }
</style>