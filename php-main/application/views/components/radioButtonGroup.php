<!-- inputs -> label, radio buttons [[id, label, value, checked]]-->

<fieldset class="<?=($useTile ?? false) ? 'bx--tile-group' : 'bx--fieldset' ?>">
    <legend class="bx--label"><?= $label ?? '' ?></legend>
    <div class="bx--radio-button-group <?= $radioGroupClass ?? '' ?>">
        <?php foreach($radioButtons as $key=> $button):?>
        <div class="bx--radio-button-wrapper">
          <input id="<?= $button['id'] ?>" class="<?= ($useTile ?? false) ? 'bx--tile-input' : 'bx--radio-button' ?>" type="radio" value="<?= $button['value'] ?>" name="<?= ($name ?? 'radio-button') ?>" tabindex="<?= $key ?>" <?php echo (isset($button['checked']) && $button['checked'])? 'checked': '' ?>>
          <label for="<?= $button['id'] ?>"  class="<?= ($useTile ?? false) ? 'bx--tile bx--tile--selectable' : 'bx--radio-button__label' ?> <?= ($useTile ?? false) && isset($button['checked']) &&  $button['checked'] ? 'bx--tile--is-selected' : '' ?>">
            <?php if (($useTile ?? false) === true): ?>
            <span class="bx--tile__checkmark">
              <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M8,1C4.1,1,1,4.1,1,8c0,3.9,3.1,7,7,7s7-3.1,7-7C15,4.1,11.9,1,8,1z M7,11L4.3,8.3l0.9-0.8L7,9.3l4-3.9l0.9,0.8L7,11z"></path>
                <path d="M7,11L4.3,8.3l0.9-0.8L7,9.3l4-3.9l0.9,0.8L7,11z" data-icon-path="inner-path" opacity="0"></path>
              </svg>
            </span>
            <?php else: ?>
            <span class="bx--radio-button__appearance"></span>
            <?php endif; ?>
            <span class="<?= ($useTile ?? false) ? 'bx--tile-content' : 'bx--radio-button__label-text' ?>"><?= $button['label'] ?></span>
          </label>
        </div>
        <?php endforeach;?>
    </div>
</fieldset>
