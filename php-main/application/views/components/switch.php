<!-- 
inputs:
inputClass
labelClass
switchClass
id
label
 -->


 <input class="bx--toggle-input <?= isset($inputClass) ? $inputClass : '' ?>" id="<?= $id ?>" type="checkbox" onclick=<?php echo preg_replace('/\s+/', '', $label) . 'Onclick(this)'; echo (isset($enabled) && $enabled) ? '' : ' disabled'?>>
<label class="bx--toggle-input__label <?= isset($labelClass) ? $labelClass : '' ?>" for="<?= $id ?>">
    <?= $label ?>
    <span class="bx--toggle__switch <?= isset($switchClass) ? $switchClass : '' ?>">
    </span>
    <div data-loading class="bx--loading bx--loading--small d-none">
        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
            <title>Loading</title>
            <circle class="bx--loading__background" cx="0" cy="0" r="45%" />
            <circle class="bx--loading__stroke" cx="0" cy="0" r="45%" />
        </svg>
    </div>
</label>