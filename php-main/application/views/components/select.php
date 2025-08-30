<!-- 
lea march 22 2021    
[id=string,
label=string,
options=[[label=string,
    value=string,
    tags=string{disabled and/or selected and/or hidden}]] ]

to make a optiongroup

options = [[
    label=string,
    options=[label=string,
        value=string,
        tags=string{disabled and/or selected and/or hidden}]] 
    ]]
-->

<div class="bx--select">
    <?php if(isset($label)):?>
        <label for="<?= $id ?>" class="bx--label"><?= $label ?></label>
    <?php endif;?>
    <div class="bx--select-input__wrapper">
        <select id="<?= $id ?>" class="bx--select-input">
            <?php foreach ($options as $o) : ?>
                <?php if (isset($o['options'])) : ?>
                    <optgroup class="bx--select-optgroup" label="<?= $o['label'] ?>">
                        <?php foreach ($o['options'] as $op) : ?>
                            <option class="bx--select-option" value="<?= $op['value'] ?>" <?= isset($op['tags']) ? $op['tags'] : '' ?>>
                                <?= $op['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php else : ?>
                    <option class="bx--select-option" value="<?= $o['value'] ?>" <?= isset($o['tags']) ? $o['tags'] : '' ?>>
                        <?= $o['label'] ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--select__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 11L3 6 3.7 5.3 8 9.6 12.3 5.3 13 6z"></path>
        </svg>
    </div>
</div>