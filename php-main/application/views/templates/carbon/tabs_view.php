<div data-tabs class="bx--tabs bx--tabs--container ">
  <div class="bx--tabs-trigger" tabindex="0">
    <a href="javascript:void(0)" class="bx--tabs-trigger-text" tabindex="-1"></a>
    <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 11L3 6 3.7 5.3 8 9.6 12.3 5.3 13 6z"></path></svg>
  </div>
  <ul class="bx--tabs__nav bx--tabs__nav--hidden database-upload-tabs" role="tablist">
    <?php foreach ($tabs as $index => $tab) : ?>
      <?php if($show_tab[$index]) : ?>
        <li
        class="bx--tabs__nav-item <?= $selected_tab == $tab ? 'bx--tabs__nav-item--selected' : ''?>"
        tab-name="<?= $tab_name[$index]; ?>"
        data-target=".<?= $tab_classes[$index]; ?>" role="tab"  aria-selected="<?= $selected_tab == $tab ? 'true' : 'false'?>"  >
        <a tabindex="0" id="<?= $tab_link_container_id[$index]; ?>" class="bx--tabs__nav-link" href="javascript:void(0)" role="tab"
            aria-controls="<?= $tab_aria_control[$index]; ?>"><?= $tab; ?></a>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
</div>