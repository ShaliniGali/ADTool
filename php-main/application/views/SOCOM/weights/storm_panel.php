<div
class="d-flex flex-column justify-content-evenly <?= $tab;?>-tab"
id="<?= $tab;?>-panel-container"
role="tabpanel"
aria-labelledby="<?= $tab;?>-link-container"
aria-hidden="true"
<?= $hidden ? 'hidden' : ''; ?>
>
    <table id="storm-score-display" class="bx--data-table w-100 table-border">
        <thead>
            <tr><th>StoRM ID</th><th>StoRM</th></tr>
        </thead>
        
    </table>
    <div class="bx--form-item ml-auto mr-auto mt-5 mb-5">
        <button id="create-<?= $tab ?>-weight" class="bx--btn bx--btn--primary rhombus-form-submit" type="submit">Save Weights</button>
    </div>
</div>