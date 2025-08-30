<label for="<?=$fpWeightId?>" class="bx--label mr-1">Available Weights<br />
                        <select id="<?=$fpWeightId?>" onchange="<?=$onchange;?>">
                        <?php foreach($optimizer_weights as $ow): ?>
                            <option value="<?=$ow['WEIGHT_ID']; ?>"><?=$ow['TITLE']; ?></option>
                        <?php endforeach; ?>
                    </select>
            </label>
                    <button class="bx--btn bx--btn--sm bx--btn--primary" 
                        type="button" onclick="window.location.href='/socom/resource_constrained_coa/weights/create'">Weights Builder</button>

<script>
    $(function() {
            $('#<?=$fpWeightId?>').select2({
                placeholder: "Select A User Weight",
            });

            $('#weight_chooser').hide();
        } 
    )

</script>