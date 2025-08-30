<?php

	$js_files = array();
    $js_files['ion_range'] = ["ion.rangeSlider.js", 'global'];
?>

<div
class="d-flex flex-column <?= $tab;?>-tab"
id="<?= $tab;?>-panel-container"
role="tabpanel"
aria-labelledby="<?= $tab;?>-link-container"
aria-hidden="true"
<?= $hidden ? 'hidden' : ''; ?>
>
    <div class="d-flex flex-column align-content-center w-100">
        <?php $sum = 0; ?>
        <div class="d-flex flex-row justify-content-center w-100 mb-3">
            <?php $this->load->view('templates/carbon/tag_view', [
                'id' => $tab.'-criteria-validation-sum-tag',
                'bx_label' => 'Sum of weight criteria must equal 1. Total Weight: <span id="'.$tab.'-criteria-validation-sum-tag-num">' . $sum . '</span>',
                'color' => 'red'
            ]); ?>
        </div>
        
            <?php for($i = 0 ; $i < count($criteria);  $i++): ?>
                <?php $crit = $criteria[$i]['CRITERIA']; ?>
                <?php $wt = $criteria[$i]['WEIGHT']; ?>
            <?php if ( $i % 3 === 0): ?>
            <div class="d-flex flex-row justify-content-around w-100 mb-3 pb-3 border-bottom border-dark">
            <?php endif; ?>
                <div class="d-flex flex-column pr-5 w-25">
                    <label for="<?= $tab;?>-criteria-<?= str_replace(' ', '-', $crit); ?>" tabindex="-1" class="bx--label">Input Weight for <strong><br /><?=  htmlspecialchars($crit, ENT_QUOTES); ?></strong></label>
                    <input type="text" id="<?= $tab;?>-criteria-<?= str_replace(' ', '-', $crit); ?>" class="js-range-slider <?= $tab;?>-crit-sliders" name="weight_<?=str_replace(' ', '-', $crit);?>" crit="<?= str_replace(' ', '-', $crit); ?>" wt="<?=$wt; ?>" 
                        value="<?=$wt; ?>" />
                    <div class="bx--form-item mt-3">
                        <label for="<?= $tab;?>-criteria-weight_<?=str_replace(' ', '-', $crit);?>_value" class="bx--label">Manual Entry <strong><br /><?=  htmlspecialchars($crit, ENT_QUOTES); ?></strong>:</label>
                        <div class="bx--text-input__field-wrapper w-50">
                            <input id="<?= $tab;?>-criteria-weight_<?=str_replace(' ', '-', $crit);?>_value" type="text" class="bx--text-input" name="<?= $tab;?>-criteria-weight_<?=str_replace(' ', '-', $crit);?>_value" value="<?=$wt;?>" placeholder="Manual Entry" tabindex="0">
                        </div>
                        <div class="bx--form-requirement"></div>
                    </div>
                    <?php $sum += floatval($wt); ?>
                </div>
            <?php if ( $i % 3 === 2 || $i === (count($criteria) - 1)): ?>
            </div>
            <?php endif; ?>
            <?php endfor; ?>
    </div>
    <div class="d-flex flex-column justify-content-around w-100">
        <div class="bx--form-item">
            <label for="<?=$tab?>-text-area-description" class="bx--label">Description</label>
            <div class="bx--form__helper-text">
            </div>
            <textarea id="<?=$tab?>-text-area-description" class="bx--text-area " name="description"
            rows="4" cols="50" placeholder="Weights Description"><?= set_value('description'); ?></textarea>
        </div>

        <div class="bx--form-item ml-auto mr-auto mt-5 mb-5">
            <button id="create-<?= $tab ?>-weight" class="bx--btn bx--btn--primary rhombus-form-submit" type="submit">Create <?=ucfirst($tab) ?> Weight</button>
        </div>
    </div>
</div>

<?php 

    $CI =& get_instance();
	$CI->load->library('RB_js_css');
	$CI->rb_js_css->compress($js_files);
    
?>
