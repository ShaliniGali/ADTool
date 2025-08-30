<div class="exec-container-child-1">
    <span class="bold-labels" style="margin-left:0px;">Number of <?= $page_title?>s:</span>
    <div class="yellow-box" style="height: 6vh;line-height: 5vh;">
        <p id="executed-hours"><?= $total_events ?></p>
    </div>
    <p class="hours-label">Net change will be zero unless there are non-zero balances</p>
    <div class="hours-parent">
        <div class="hours-child-1">
            Dollars (Thousands) Moved:
            <div id="programmed-hours" style="height: 5vh;">$<?= $dollars_moved ?></div>
        </div>
        <div class="hours-child-2">
            Net Change:
            <div id="required-hours" style="height: 5vh;">$<?= $net_change ?></div>
        </div>
    </div>
</div>