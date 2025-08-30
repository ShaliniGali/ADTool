<div id="<?=$modal_id?>" role="<?=$role?>">
    <?php 
    if (!empty($launch_btn_txt)): 
    ?>
    <button tabindex="0" class="bx--btn bx--btn--primary" type="button"><?=$launch_btn_txt?></button>
    <?php
    endif;
    // add class is-visible to below div.bx--modal.bx--modal-tall
    ?>
    <div class="bx--modal bx--modal-tall" role="<?=$role?>"><span tabindex="0" role="link" class="bx--visually-hidden">Focus sentinel</span>
        <div role="dialog" class="bx--modal-container" aria-label="Label" aria-modal="true" tabindex="-1">
            <div class="bx--modal-header">
                <h2 class="bx--modal-header__label"><?=$title?></h2>
                <h3 class="bx--modal-header__heading"><?=$title_heading?></h3>
                <button class="bx--modal-close" type="button" aria-label="close">
                    <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" aria-hidden="true" width="20" height="20" viewBox="0 0 32 32" class="bx--modal-close__icon">
                        <path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path>
                    </svg>
                </button>
            </div>
            <div class="bx--modal-content pr-4">
                <?=$html_content?>
            </div>
            <?php
            if (!empty($buttons)):
            ?>
            <div class="bx--modal-footer bx--btn-set">
                <?php 
                // bx--btn--secondary
                // bx--btn--primary
                // use the above for button classes
                foreach($buttons as $btn):
                ?>
                <button tabindex="0" class="bx--btn <?=$btn['class']?>" aria-label="<?=$btn['aria-label']?>" type="button"><?=$btn['text']?></button>
                <?php
                endforeach;
                ?>
            </div>
            <?php
            endif;
            ?>
        </div><span tabindex="0" role="link" class="bx--visually-hidden">Focus sentinel</span>
    </div>
</div>

<script>

$(function() {
        $('#<?=$modal_id?> > div > div > div.bx--modal-header > button[aria-label="close"], #<?=$modal_id?> > div > div > div.bx--modal-footer > button[aria-label="close"]').on('click', 
        <?php
        if (!empty($close_event)):
            echo $close_event;
        else:
        ?>
        function() { 
            $('#<?=$modal_id?> > div.bx--modal.bx--modal-tall').removeClass('is-visible'); 
        }
        <?php
        endif;
        ?>);
        
        $('#<?=$modal_id?> button[aria-label="save"]').on('click', 
        <?php
        if (!empty($save_event)):
            echo $save_event;
        else:
        echo <<<EOT
        
        EOT;
        endif;
        ?>);
});
</script>
