<?php
/**
 * intakes
 * 
 * tags = [
 * ['text'=>'text',
 * 'id'=>'id', (optional)
 * 'color'=>'colorName']
 * ]
 * 
 * accepted colors = Red,Magenta,Purple,Blue,Cyan,Teal,Green,Gray,Cool-Gray,Warm-Gray
 */
?>

<div>
    <?php foreach ($tags as $tag):?>
        <button class="bx--tag bx--tag--<?= $tag['color']?>" <?= isset($tag['id'])?'id="'.$tag['id'].'"':''?>>
            <span class="bx--tag__label"><?= $tag['text']?></span>
        </button>
    <?php endforeach;?>
</div>