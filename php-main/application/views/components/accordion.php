<!-- 
takes parameters
sections = [
    [title -> string,
    content -> text,
    id => id]
    ]


 -->

<ul data-accordion class="bx--accordion">
    <?php foreach($sections as $section):?>
    <li data-accordion-item class="bx--accordion__item <?= (isset($section['active']) && $section['active'] ? 'bx--accordion__item--active' : '')?>">
      <button class="bx--accordion__heading" aria-expanded="true" aria-controls="<?= $section['id']?>" <?= (isset($section['action'])? $section['action'] : '')?>>
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--accordion__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M11 8L6 13 5.3 12.3 9.6 8 5.3 3.7 6 3z"></path></svg>
        <div class="bx--accordion__title"><?= $section['title']?></div>
      </button>
      <div id="<?= $section['id']?>" class="bx--accordion__content text-muted px-3">
        <?= $section['content']?>
      </div>
    </li>
    <?php endforeach;?>
</ul>