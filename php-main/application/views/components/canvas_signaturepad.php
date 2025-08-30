<div class="bx--form-item">
    <div class="bx--row">
        <div class="bx--offset-md-2 bx--col-md-5">
            <div class="wrapper">
                <canvas id="<?php echo (isset($id) ? $id : '');?>" class="signature-pad bx--text-area <?php echo (isset($class) ? $class : '');?>"  width=<?php echo (isset($width) ? $id : '400');?> height=<?php echo (isset($height) ? $height : '200');?> ></canvas>
            </div>
        </div>
        <div class="bx--col-md-1">
            <div class="bx--grid">
                <div class="bx--row">
                    <div class="bx--col">
                        <button id="<?php echo (isset($clearid) ? $clearid : '');?>" class="bx--btn bx--btn--secondary">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
