<div id="coa-save-container-<?=$n?>" class="d-flex flex-column justify-content-between">
    <div class="d-flex">
        <h5 id="coa-save-<?=$n?>" class="coa-save"></h5>
    </div>
    <div id="coa-manual-override-modal-button-container-<?=$n?>"
        class="d-flex justify-content-between pr-2"
    ></div>
</div>
<table id="coa-table-<?=$n?>" class="display" style="width:100%">
    <thead>
        <tr>
            <th></th>
            <th>PROPOSED BUDGET $K</th>
            <th>REMAINING $K</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($year_list as $yli => $year): ?>
            <tr>
                <td class="labelYearOptimizer label-y<?= $yli+1; ?>"><?= $year; ?></td>
                <td><input type="text" year="<?= $year; ?>" class="delta-y<?= $yli+1; ?> deltaOptimizer bx--text-input" value="0" /></td>
                <td><span class="remaining">0</span></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td>FYDP</td>
            <td><span class="delta-fydp">0</span></td>
            <td><span class="remaining">0</span></td>
        </tr>
    </tbody>
</table>

