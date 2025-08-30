<a class="home-blocks w-100 <?php if(isset($class)) echo $class; ?>"
    <?php if(isset($link) && $link != ''):?> href="<?=$link?>" <?php endif;?>
    <?php if(isset($id)):?> id="<?=$id?>" <?php endif;?>
    <?php if(isset($data_modal_target)):?> data-modal-target="<?=$data_modal_target?>" <?php endif;?>
    <?php if(isset($onclick)):?> onclick="<?=$onclick?>" <?php endif;?>

    <?php if(isset($state) && $state): ?>
        style="opacity:0.5; pointer-events:none; cursor:not-allowed;"
    <?php endif; ?>
>



    <div class="content d-flex flex-column mt-auto mb-auto <?=$class?>
    <?php if(isset($hide_block)):?> <?=$hide_block?> <?php endif;?>" >
    <div class="mb-2 mt-3 ml-auto mr-auto ">
    <div class="mt-2 icon-image <?=$class?>">
    <?php if(isset($icon)):?>
           <?php echo $icon;?>
        <?php endif;?>
        </div>
    </div>
    <div class="text">
        <h3 class="title g-type-display-4"><?=$label;?></h3>
        <div class="description g-type-body">
            <p><?=$description;?></p>
        </div>
    </div>
    </div>
    <div class="faux-link g-type-buttons-and-standalone-links <?=$class?>">
     <!-- <?php if(isset($hide_block)):?> <?=$label?> <?php else:?> Learn more <?php endif;?> -->
    <?php if(isset($hide_block)):?> 
        <p style="color:green;">In Development</p>
    <?php else:?> Learn more 
    <?php endif;?>
    <?php if(!isset($hide_block)):?>
        <svg width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M13.675 5.7C13.6 5.625 13.6 5.55 13.525 5.475L9.025
            0.975C8.725 0.675 8.275 0.675 7.975 0.975C7.675 1.275 7.675
            1.725 7.975 2.025L11.2 5.25H1C0.55 5.25 0.25 5.55 0.25 6C0.25
            6.45 0.55 6.75 1 6.75H11.2L7.975 9.975C7.675 10.275 7.675 10.725
            7.975 11.025C8.125 11.175 8.35 11.25 8.5 11.25C8.65 11.25 8.875
            11.175 9.025 11.025L13.525 6.525C13.6 6.45 13.675 6.375 13.675
            6.3C13.75 6.075 13.75 5.925 13.675 5.7Z" fill="#000"></path>
        </svg>
        <?php endif;?>
    </div>
</a>
