<?php if(!isset($formItem) || $formItem == true):?>
    <div class="bx--form-item <?php echo (isset($class) ? $class : '');?>">
<?php endif;?>
    <button class="bx--btn bx--btn--<?php echo (isset($type) ? $type : 'primary');?> <?php echo (isset($buttonClass) ? $buttonClass : '');?>" type="button" id="<?php echo (isset($id) ? $id : '');?>" <?php echo (isset($action) ? $action : '');?>><?php echo (isset($label) ? $label : '');?></button>

<?php if(!isset($formItem) || $formItem == true):?>
    </div>
<?php endif;?>